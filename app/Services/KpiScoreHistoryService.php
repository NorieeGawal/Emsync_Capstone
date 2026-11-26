<?php

namespace App\Services;

use App\Models\KpiIndicator;
use App\Models\KpiRecord;
use App\Models\KpiScoreAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KpiScoreHistoryService
{
    public function save(
        User $employee,
        KpiIndicator $indicator,
        string $period,
        array $values,
        User $actor,
        string $transactionId,
        ?string $reason
    ): void {
        // The full employee/criterion/period key keeps concurrent edits scoped to one saved score.
        $record = KpiRecord::where('employee_id', $employee->id)
            ->where('kpi_indicator_id', $indicator->id)
            ->where('period', $period)
            ->lockForUpdate()
            ->first();

        if ($record && blank($reason)) {
            throw ValidationException::withMessages([
                'reason' => 'Enter a reason when changing an existing KPI score.',
            ]);
        }

        $original = $this->snapshot($record);
        $record ??= new KpiRecord([
            'employee_id' => $employee->id,
            'kpi_indicator_id' => $indicator->id,
            'period' => $period,
        ]);
        $record->fill([...$values, 'encoded_by' => $actor->id])->save();
        $record->refresh();

        $this->audit(
            employee: $employee,
            indicator: $indicator,
            period: $period,
            action: $original === null ? 'created' : 'updated',
            original: $original,
            new: $this->snapshot($record),
            actor: $actor,
            reason: $reason ?: 'Initial KPI submission',
            transactionId: $transactionId,
        );
    }

    public function deleteForEmployeePeriod(User $employee, string $period, User $actor, string $reason): int
    {
        return DB::transaction(function () use ($employee, $period, $actor, $reason): int {
            // This bulk operation is limited to one employee and one review period.
            $records = KpiRecord::with('kpiIndicator')
                ->where('employee_id', $employee->id)
                ->where('period', $period)
                ->lockForUpdate()
                ->get();
            $transactionId = (string) Str::uuid();

            foreach ($records as $record) {
                $this->audit(
                    employee: $employee,
                    indicator: $record->kpiIndicator,
                    period: $period,
                    action: 'deleted',
                    original: $this->snapshot($record),
                    new: null,
                    actor: $actor,
                    reason: $reason,
                    transactionId: $transactionId,
                );
                $record->delete();
            }

            return $records->count();
        });
    }

    public function deleteSavedEntry(User $employee, string $period, User $actor, string $reason): int
    {
        return DB::transaction(function () use ($employee, $period, $actor, $reason): int {
            // Lock only this employee's rows for this period before auditing and deleting them.
            $records = KpiRecord::with('kpiIndicator')
                ->where('employee_id', $employee->id)
                ->where('period', $period)
                ->lockForUpdate()
                ->get();

            if ($records->isEmpty()) {
                throw ValidationException::withMessages([
                    'period' => 'There are no saved KPI scores for this employee and review period.',
                ]);
            }

            $transactionId = (string) Str::uuid();
            foreach ($records as $record) {
                $this->audit(
                    employee: $employee,
                    indicator: $record->kpiIndicator,
                    period: $period,
                    action: 'deleted',
                    original: $this->snapshot($record),
                    new: null,
                    actor: $actor,
                    reason: $reason,
                    transactionId: $transactionId,
                );
                $record->delete();
            }

            return $records->count();
        });
    }

    public function deleteSavedCriterion(
        User $employee,
        string $period,
        int $indicatorId,
        User $actor,
        string $reason
    ): void {
        DB::transaction(function () use ($employee, $period, $indicatorId, $actor, $reason): void {
            // Include the criterion ID as well as employee and period to avoid cross-record deletion.
            $records = KpiRecord::with('kpiIndicator')
                ->where('employee_id', $employee->id)
                ->where('period', $period)
                ->where('kpi_indicator_id', $indicatorId)
                ->lockForUpdate()
                ->get();

            if ($records->isEmpty()) {
                throw ValidationException::withMessages([
                    'kpi_indicator_id' => 'There is no saved score for this employee, criterion, and review period.',
                ]);
            }

            $transactionId = (string) Str::uuid();
            foreach ($records as $record) {
                $this->audit(
                    employee: $employee,
                    indicator: $record->kpiIndicator,
                    period: $period,
                    action: 'deleted',
                    original: $this->snapshot($record),
                    new: null,
                    actor: $actor,
                    reason: $reason,
                    transactionId: $transactionId,
                );
                $record->delete();
            }
        });
    }

    public function undoScore(User $employee, string $period, int $indicatorId, User $actor, string $reason): void
    {
        DB::transaction(function () use ($employee, $period, $indicatorId, $actor, $reason): void {
            $indicator = KpiIndicator::findOrFail($indicatorId);
            $record = $this->lockRecord($employee, $period, $indicatorId);
            $audit = $this->latestReversibleAudit($employee, $period, $indicatorId);
            $transactionId = (string) Str::uuid();

            if ($audit) {
                $this->reverseAudit($audit, $employee, $indicator, $actor, $reason, $transactionId, $record);

                return;
            }

            if (! $record) {
                throw ValidationException::withMessages([
                    'score' => 'There is no KPI score or saved history to undo for this criterion.',
                ]);
            }

            $original = $this->snapshot($record);
            $record->delete();
            $this->audit(
                employee: $employee,
                indicator: $indicator,
                period: $period,
                action: 'undo',
                original: $original,
                new: null,
                actor: $actor,
                reason: $reason,
                transactionId: $transactionId,
            );
        });
    }

    public function undoSubmission(User $employee, string $period, string $transactionId, User $actor, string $reason): int
    {
        return DB::transaction(function () use ($employee, $period, $transactionId, $actor, $reason): int {
            $entries = KpiScoreAudit::where('employee_id', $employee->id)
                ->where('period', $period)
                ->where('transaction_id', $transactionId)
                ->whereIn('action', ['created', 'updated', 'deleted'])
                ->whereNull('reversed_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($entries->isEmpty()) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'This submission has already been undone or is no longer available.',
                ]);
            }

            foreach ($entries as $entry) {
                $latest = $this->latestReversibleAudit($employee, $period, (int) $entry->kpi_indicator_id);
                if (! $latest || $latest->id !== $entry->id) {
                    throw ValidationException::withMessages([
                        'transaction_id' => 'Undo newer KPI changes before undoing this submission.',
                    ]);
                }
            }

            $undoTransactionId = (string) Str::uuid();
            foreach ($entries as $entry) {
                $indicator = KpiIndicator::find($entry->kpi_indicator_id);
                if (! $indicator && $entry->original_value !== null) {
                    throw ValidationException::withMessages([
                        'transaction_id' => 'A KPI criterion in this submission no longer exists, so it cannot be restored.',
                    ]);
                }

                $this->reverseAudit(
                    $entry,
                    $employee,
                    $indicator,
                    $actor,
                    $reason,
                    $undoTransactionId,
                    $this->lockRecord($employee, $period, (int) $entry->kpi_indicator_id),
                );
            }

            return $entries->count();
        });
    }

    public function undoAllCurrentScores(User $employee, string $period, User $actor, string $reason): int
    {
        return DB::transaction(function () use ($employee, $period, $actor, $reason): int {
            $records = KpiRecord::with('kpiIndicator')
                ->where('employee_id', $employee->id)
                ->where('period', $period)
                ->orderBy('kpi_indicator_id')
                ->lockForUpdate()
                ->get();

            if ($records->isEmpty()) {
                throw ValidationException::withMessages([
                    'period' => 'There are no current KPI scores to undo for this review period.',
                ]);
            }

            $undoTransactionId = (string) Str::uuid();
            foreach ($records as $record) {
                $audit = $this->latestReversibleAudit($employee, $period, (int) $record->kpi_indicator_id);
                if ($audit) {
                    $this->reverseAudit($audit, $employee, $record->kpiIndicator, $actor, $reason, $undoTransactionId, $record);

                    continue;
                }

                $original = $this->snapshot($record);
                $record->delete();
                $this->audit(
                    employee: $employee,
                    indicator: $record->kpiIndicator,
                    period: $period,
                    action: 'undo',
                    original: $original,
                    new: null,
                    actor: $actor,
                    reason: $reason,
                    transactionId: $undoTransactionId,
                );
            }

            return $records->count();
        });
    }

    private function latestReversibleAudit(User $employee, string $period, int $indicatorId): ?KpiScoreAudit
    {
        return KpiScoreAudit::where('employee_id', $employee->id)
            ->where('period', $period)
            ->where('kpi_indicator_id', $indicatorId)
            ->whereIn('action', ['created', 'updated', 'deleted'])
            ->whereNull('reversed_at')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    private function lockRecord(User $employee, string $period, int $indicatorId): ?KpiRecord
    {
        return KpiRecord::where('employee_id', $employee->id)
            ->where('period', $period)
            ->where('kpi_indicator_id', $indicatorId)
            ->lockForUpdate()
            ->first();
    }

    private function reverseAudit(
        KpiScoreAudit $entry,
        User $employee,
        ?KpiIndicator $indicator,
        User $actor,
        string $reason,
        string $undoTransactionId,
        ?KpiRecord $record
    ): void {
        if (! $indicator) {
            throw ValidationException::withMessages([
                'score' => 'The KPI criterion no longer exists, so this score cannot be restored.',
            ]);
        }

        $current = $this->snapshot($record);
        // Refuse to restore stale history if another edit changed the score after this audit entry.
        if ($current !== $entry->new_value) {
            throw ValidationException::withMessages([
                'score' => 'This score has changed since the selected history entry. Reload the page before undoing it.',
            ]);
        }

        $restored = $entry->original_value;
        if ($restored === null) {
            $record?->delete();
        } else {
            $record ??= new KpiRecord([
                'employee_id' => $employee->id,
                'kpi_indicator_id' => $indicator->id,
                'period' => $entry->period,
            ]);
            $record->fill($restored)->save();
        }

        $entry->update([
            'reversed_at' => now(),
            'reversed_by' => $actor->id,
        ]);

        $this->audit(
            employee: $employee,
            indicator: $indicator,
            period: $entry->period,
            action: 'undo',
            original: $current,
            new: $restored,
            actor: $actor,
            reason: $reason,
            transactionId: $undoTransactionId,
            reversesAuditId: $entry->id,
        );
    }

    private function snapshot(?KpiRecord $record): ?array
    {
        if (! $record) {
            return null;
        }

        return [
            'score' => number_format((float) $record->score, 2, '.', ''),
            'workload_target' => $record->workload_target,
            'workload_completed' => $record->workload_completed,
            'encoded_by' => (int) $record->encoded_by,
            'remarks' => $record->remarks,
        ];
    }

    private function audit(
        User $employee,
        ?KpiIndicator $indicator,
        string $period,
        string $action,
        ?array $original,
        ?array $new,
        User $actor,
        string $reason,
        string $transactionId,
        ?int $reversesAuditId = null
    ): void {
        // Store before/after snapshots with the actor and reason so this change remains traceable.
        KpiScoreAudit::create([
            'transaction_id' => $transactionId,
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'period' => $period,
            'kpi_indicator_id' => $indicator?->id,
            'kpi_indicator_name' => $indicator?->name ?? 'Unknown KPI',
            'action' => $action,
            'original_value' => $original,
            'new_value' => $new,
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'reason' => $reason,
            'reverses_audit_id' => $reversesAuditId,
        ]);
    }
}
