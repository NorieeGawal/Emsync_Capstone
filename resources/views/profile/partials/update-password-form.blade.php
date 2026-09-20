<section>
    <header>
        <h2 class="text-base font-bold text-slate-800">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-xs leading-5 text-slate-500">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4 space-y-3">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <div x-data="{ show: false }" class="relative mt-1">
                <x-text-input id="update_password_current_password" name="current_password" type="password" x-bind:type="show ? 'text' : 'password'" class="block w-full pr-10" autocomplete="current-password" />
                <button type="button" @click="show = !show" :aria-label="show ? 'Hide current password' : 'Show current password'" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 transition hover:text-slate-700">
                    <svg x-show="!show" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"></path><path d="M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17.7 17.7 0 0 1-3.2 3.8"></path><path d="M6.2 6.3C3.9 8 2.5 12 2.5 12s3.5 6 9.5 6c1 0 1.9-.2 2.7-.5"></path><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <div x-data="{ show: false }" class="relative mt-1">
                <x-text-input id="update_password_password" name="password" type="password" x-bind:type="show ? 'text' : 'password'" class="block w-full pr-10" autocomplete="new-password" />
                <button type="button" @click="show = !show" :aria-label="show ? 'Hide new password' : 'Show new password'" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 transition hover:text-slate-700">
                    <svg x-show="!show" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"></path><path d="M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17.7 17.7 0 0 1-3.2 3.8"></path><path d="M6.2 6.3C3.9 8 2.5 12 2.5 12s3.5 6 9.5 6c1 0 1.9-.2 2.7-.5"></path><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <div x-data="{ show: false }" class="relative mt-1">
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" x-bind:type="show ? 'text' : 'password'" class="block w-full pr-10" autocomplete="new-password" />
                <button type="button" @click="show = !show" :aria-label="show ? 'Hide password confirmation' : 'Show password confirmation'" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400 transition hover:text-slate-700">
                    <svg x-show="!show" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"></path><path d="M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17.7 17.7 0 0 1-3.2 3.8"></path><path d="M6.2 6.3C3.9 8 2.5 12 2.5 12s3.5 6 9.5 6c1 0 1.9-.2 2.7-.5"></path><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 4000)"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700"
                    role="status"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg>
                    {{ __('Password changed successfully.') }}
                </p>
            @endif
        </div>
    </form>
</section>
