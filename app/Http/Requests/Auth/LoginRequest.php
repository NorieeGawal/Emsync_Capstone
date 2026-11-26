<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_LOGIN_ATTEMPTS = 3;

    private const LOCKOUT_SECONDS = 60;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:hr,manager,employee'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->recordFailedAttempt('email', trans('auth.failed'));
        }

        // Keep role mismatches indistinguishable from bad passwords to avoid revealing account details.
        if ($this->filled('role') && Auth::user()->role !== $this->input('role')) {
            Auth::logout();
            $this->recordFailedAttempt('email', trans('auth.failed'));
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->lockoutKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->lockoutKey(), 1)) {
            return;
        }

        $this->throwRateLimited();
    }

    private function recordFailedAttempt(string $field, string $message): void
    {
        if (RateLimiter::hit($this->throttleKey(), self::LOCKOUT_SECONDS) >= self::MAX_LOGIN_ATTEMPTS) {
            RateLimiter::clear($this->throttleKey());
            // A separate key starts a fresh full lockout window on the third failed attempt.
            RateLimiter::hit($this->lockoutKey(), self::LOCKOUT_SECONDS);
            $this->throwRateLimited();
        }

        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }

    private function throwRateLimited(): void
    {
        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->lockoutKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    private function lockoutKey(): string
    {
        return $this->throttleKey().':lockout';
    }
}
