<section>
    <header>
        <h2 class="text-base font-bold text-slate-800">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-xs leading-5 text-slate-500">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            @if($user->profile_photo_path)
                <img src="{{ url('/storage/' . ltrim($user->profile_photo_path, '/')) }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-2xl object-cover shadow-sm">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f4c212] text-xl font-black text-[#111111]">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</div>
            @endif
            <div>
                <x-input-label for="profile_photo" :value="__('Profile photo')" />
                <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700" />
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
                @if($user->profile_photo_path)
                    <label class="mt-2 inline-flex items-center gap-2 text-xs font-medium text-red-600">
                        <input type="hidden" name="remove_profile_photo" value="0">
                        <input type="checkbox" name="remove_profile_photo" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        Remove current photo
                    </label>
                @endif
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
