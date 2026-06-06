<?php

use App\Models\Invite;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $token = '';

    public function mount(): void
    {
        $this->token = (string) request()->query('invite', '');

        if ($this->invite?->email) {
            $this->email = $this->invite->email;
        }
    }

    public function getInviteProperty(): ?Invite
    {
        return once(fn () => $this->token ? Invite::where('token', $this->token)->first() : null);
    }

    public function getInviteValidProperty(): bool
    {
        return $this->invite?->isUsable() ?? false;
    }

    public function register(): void
    {
        $invite = $this->invite;

        if (! $invite?->isUsable()) {
            throw ValidationException::withMessages(['token' => __('A valid invite is required to register.')]);
        }

        $emailRules = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class];
        if ($invite->email) {
            $emailRules[] = 'in:'.$invite->email;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();

        event(new Registered($user = User::create($validated)));

        $invite->forceFill([
            'used_by' => $user->id,
            'used_at' => now(),
        ])->save();

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    @unless ($this->invite_valid)
        <div class="mb-4 text-sm text-red-600">
            {{ __('This invite link is invalid, expired, or already used. Ask whoever invited you for a fresh link.') }}
        </div>
    @else
    <form wire:submit="register">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" :readonly="(bool) $this->invite?->email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
    @endunless
</div>
