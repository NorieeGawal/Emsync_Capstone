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

test('generic dashboard route redirects users to their role dashboard', function () {
    foreach ([
        'hr' => 'hr.dashboard',
        'manager' => 'manager.dashboard',
        'employee' => 'employee.dashboard',
    ] as $role => $dashboardRoute) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route($dashboardRoute, absolute: false));
    }
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'role' => $user->role,
    ])->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->get('/login')->assertSee('These credentials do not match our records.');
});

test('login rejects credentials when the selected role does not match the account', function () {
    $user = User::factory()->create(['role' => 'employee']);

    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'manager',
    ])->assertRedirect('/login')
        ->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.',
        ]);

    $this->assertGuest();
    $this->get('/login')->assertSee('These credentials do not match our records.');
});

test('login locks out after three failed attempts for one minute', function () {
    $user = User::factory()->create(['role' => 'employee']);

    foreach (range(1, 2) as $attempt) {
        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'role' => $user->role,
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'role' => $user->role,
    ])->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->get('/login')->assertSee('Too many login attempts');

    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => $user->role,
    ])->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->travel(61)->seconds();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => $user->role,
    ])->assertRedirect(route('employee.dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users can logout', function () {
    foreach (['hr', 'manager', 'employee'] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->withSession(['sensitive_marker' => 'authenticated-data']);
        $sessionId = session()->getId();
        $csrfToken = session()->token();

        $response = $this->post('/logout');

        $this->assertGuest();
        expect(session()->getId())->not->toBe($sessionId);
        expect(session()->token())->not->toBe($csrfToken);
        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionMissing('sensitive_marker');
        expect($response->headers->get('Cache-Control'))
            ->toContain('private', 'no-store', 'no-cache', 'must-revalidate', 'max-age=0', 's-maxage=0');
        expect($response->headers->get('Pragma'))->toBe('no-cache');
        expect($response->headers->get('Expires'))->toBe('0');

        $this->get('/dashboard')->assertRedirect(route('login', absolute: false));
    }
});

test('authenticated dashboard responses are not cached by the browser', function () {
    foreach ([
        'hr' => 'hr.dashboard',
        'manager' => 'manager.dashboard',
        'employee' => 'employee.dashboard',
    ] as $role => $dashboardRoute) {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)->get(route($dashboardRoute));

        $response->assertOk();
        expect($response->headers->get('Cache-Control'))
            ->toContain('private', 'no-store', 'no-cache', 'must-revalidate', 'max-age=0', 's-maxage=0');
        expect($response->headers->get('Pragma'))->toBe('no-cache');
        expect($response->headers->get('Expires'))->toBe('0');
    }
});

test('unauthenticated users cannot access protected routes directly', function () {
    foreach (['/dashboard', '/hr/dashboard', '/manager/dashboard', '/employee/dashboard', '/profile'] as $path) {
        $this->get($path)->assertRedirect(route('login', absolute: false));
    }
});

test('logout only accepts post requests', function () {
    $this->get('/logout')->assertMethodNotAllowed();
});
