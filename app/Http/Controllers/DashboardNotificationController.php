<?php

namespace App\Http\Controllers;

use App\Models\NotificationRead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardNotificationController extends Controller
{
    public function markRead(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notification_key' => ['required', 'string', 'max:100'],
            'redirect_to' => ['nullable', 'url'],
        ]);

        NotificationRead::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'notification_key' => $validated['notification_key'],
            ],
            ['read_at' => now()]
        );

        return redirect()->to($validated['redirect_to'] ?? url()->previous());
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notification_keys' => ['required', 'array', 'min:1'],
            'notification_keys.*' => ['required', 'string', 'max:100'],
            'redirect_to' => ['nullable', 'url'],
        ]);

        $now = now();
        $rows = collect($validated['notification_keys'])
            ->unique()
            ->map(fn (string $key): array => [
                'user_id' => $request->user()->id,
                'notification_key' => $key,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        NotificationRead::upsert(
            $rows,
            ['user_id', 'notification_key'],
            ['read_at', 'updated_at']
        );

        return redirect()->to($validated['redirect_to'] ?? url()->previous());
    }
}
