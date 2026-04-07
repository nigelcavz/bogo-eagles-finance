<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="section-heading">
                {{ __('Account Settings') }}
            </h2>
            <p class="section-subheading">Manage your account information, email address, and password.</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('force_password_change'))
                <div class="rounded-md bg-yellow-100 p-4 text-yellow-900">
                    {{ session('force_password_change') }}
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
