<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('hr deletion archives an account and removes it from the active directory', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($hr)
        ->delete(route('hr.employees.destroy', $employee))
        ->assertRedirect(route('hr.employees.index'))
        ->assertSessionHas('success', 'Account archived. It will be permanently deleted after one month.');

    expect(User::withTrashed()->findOrFail($employee->id)->trashed())->toBeTrue();

    $this->get(route('hr.employees.index'))->assertDontSee($employee->email);
    $this->get(route('hr.employees.archived'))
        ->assertOk()
        ->assertSee($employee->email)
        ->assertSee('Restore')
        ->assertSee(now()->addMonth()->format('M j, Y'));
});

test('hr can restore an archived account', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $employee->delete();

    $this->actingAs($hr)
        ->post(route('hr.employees.restore', $employee->id))
        ->assertRedirect(route('hr.employees.archived'))
        ->assertSessionHas('success', 'Account restored successfully.');

    expect(User::findOrFail($employee->id)->trashed())->toBeFalse();
});

test('model pruning permanently deletes accounts archived for more than one month', function () {
    Storage::fake('public');
    Storage::disk('public')->put('profile-photos/old.png', 'old photo');
    Storage::disk('public')->put('profile-photos/recent.png', 'recent photo');

    $oldAccount = User::factory()->create([
        'role' => 'employee',
        'profile_photo_path' => 'profile-photos/old.png',
    ]);
    $oldAccount->delete();
    User::withTrashed()->whereKey($oldAccount->id)->update([
        'deleted_at' => now()->subMonth()->subSecond(),
    ]);

    $recentAccount = User::factory()->create([
        'role' => 'employee',
        'profile_photo_path' => 'profile-photos/recent.png',
    ]);
    $recentAccount->delete();

    $this->artisan('model:prune', ['--model' => User::class])->assertSuccessful();

    $this->assertDatabaseMissing('users', ['id' => $oldAccount->id]);
    $this->assertDatabaseHas('users', ['id' => $recentAccount->id]);
    Storage::disk('public')->assertMissing('profile-photos/old.png');
    Storage::disk('public')->assertExists('profile-photos/recent.png');
});
