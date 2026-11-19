<?php

use App\Models\NotificationRead;
use App\Models\User;

test('notification redirects cannot send users to external sites', function () {
    $hr = User::factory()->create(['role' => 'hr']);

    $this->actingAs($hr)
        ->from('/hr/dashboard')
        ->post(route('notifications.read'), [
            'notification_key' => 'example-notification',
            'redirect_to' => 'https://example.com/phishing',
        ])
        ->assertRedirect('/hr/dashboard')
        ->assertSessionHasErrors('redirect_to');

    expect(NotificationRead::where('user_id', $hr->id)->exists())->toBeFalse();
});
