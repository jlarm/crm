<x-filament-panels::page>
    <form wire:submit="updateProfile">
        {{ $this->editProfileForm }}

        <x-filament::button type="submit" class="mt-6">
            Save Profile
        </x-filament::button>
    </form>

    <div style="height: 2rem;"></div>

    <form wire:submit="updatePassword">
        {{ $this->editPasswordForm }}

        <x-filament::button type="submit" class="mt-6">
            Update Password
        </x-filament::button>
    </form>
</x-filament-panels::page>
