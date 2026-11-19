<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create(['role' => 'employee']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('employee.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['sensitive_marker' => 'authenticated-data'])
        ->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
    $response->assertSessionMissing('sensitive_marker');
    expect($response->headers->get('Cache-Control'))
        ->toContain('no-store', 'no-cache', 'must-revalidate', 'max-age=0', 'post-check=0', 'pre-check=0');
    expect($response->headers->get('Pragma'))->toBe('no-cache');
    expect($response->headers->get('Expires'))->toBe('0');

    $this->get('/dashboard')->assertRedirect(route('login', absolute: false));
});

test('authenticated dashboard responses are not cached by the browser', function () {
    $user = User::factory()->create(['role' => 'employee']);

    $response = $this->actingAs($user)->get(route('employee.dashboard'));

    $response->assertOk();
    expect($response->headers->get('Cache-Control'))
        ->toContain('no-store', 'no-cache', 'must-revalidate', 'max-age=0', 'post-check=0', 'pre-check=0');
    expect($response->headers->get('Pragma'))->toBe('no-cache');
    expect($response->headers->get('Expires'))->toBe('0');
});

test('unauthenticated users cannot access protected routes directly', function () {
    $this->get('/dashboard')->assertRedirect(route('login', absolute: false));
    $this->get('/profile')->assertRedirect(route('login', absolute: false));
});
