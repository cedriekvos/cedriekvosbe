<?php

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

// login.feature — Scenario: The login screen is available
it('shows the login form', function () {
    $this->get('/login')->assertStatus(200);
});

// login.feature — Scenario: Signing in with valid credentials
it('signs in with valid credentials and redirects to the admin area', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

// login.feature — Scenario: An incorrect password is rejected
it('rejects an incorrect password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

// login.feature — Scenario: Email and password are required
it('requires an email and a password', function () {
    $this->post('/login', ['email' => '', 'password' => 'something'])
        ->assertSessionHasErrors(['email']);

    $this->post('/login', ['email' => 'foo@example.com', 'password' => ''])
        ->assertSessionHasErrors(['password']);
});

// login.feature — Scenario: Signing out
it('signs out and redirects to the homepage', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

// login.feature — Scenario: Repeated failed attempts are rate limited
it('rate limits the sixth failed attempt with a wait message', function () {
    Event::fake([Lockout::class]);

    $user = User::factory()->create();

    foreach (range(1, 5) as $_) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

    $response->assertSessionHasErrors(['email']);
    expect(session('errors')->get('email')[0])->toContain('seconds');

    Event::assertDispatched(Lockout::class);

    RateLimiter::clear(Str::transliterate(strtolower($user->email).'|127.0.0.1'));
});

// login.feature — Scenario: The admin area cannot be reached while signed out
it('redirects guests away from the admin area to the login page', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

// login.feature — Scenario: There is no self-service registration or password reset
it('has no self-service registration route', function () {
    $this->get('/register')->assertNotFound();
});

// Supplementary coverage (no matching scenario) ----------------------------------

it('rejects an unparseable email address', function () {
    $this->post('/login', ['email' => 'not-an-email', 'password' => 'something'])
        ->assertSessionHasErrors(['email']);
});

it('regenerates the session id on login', function () {
    $user = User::factory()->create();

    $this->startSession();
    $oldId = session()->getId();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    expect(session()->getId())->not->toBe($oldId);
});

it('clears existing session data on logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->withSession(['canary' => 'present'])->post('/logout');

    expect(session()->has('canary'))->toBeFalse();
});

it('allows the fifth failed login without triggering a lockout', function () {
    Event::fake([Lockout::class]);

    $user = User::factory()->create();

    foreach (range(1, 5) as $_) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    Event::assertNotDispatched(Lockout::class);

    RateLimiter::clear(Str::transliterate(strtolower($user->email).'|127.0.0.1'));
});

it('builds the throttle key from the lowercased email and the IP address', function () {
    $request = LoginRequest::create('/login', 'POST', [
        'email' => 'Mixed.Case@Example.COM',
    ]);
    $request->server->set('REMOTE_ADDR', '203.0.113.42');

    expect($request->throttleKey())->toBe('mixed.case@example.com|203.0.113.42');
});
