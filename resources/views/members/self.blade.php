<x-app-layout>
    @if (! $member)
        <x-slot name="header">
            <div>
                <h2 class="section-heading">Profile</h2>
                <p class="section-subheading">
                    View your member profile, contribution history, and monthly dues coverage records.
                </p>
            </div>
        </x-slot>

        <div class="page-shell">
            <div class="page-content max-w-4xl">
                <div class="app-panel">
                    <div class="panel-body">
                        <h3 class="text-lg font-semibold text-slate-100">No linked member profile</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            Your login account does not have a linked member record yet. Please contact an administrator, president, or treasurer for assistance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('members.partials.profile-page')
    @endif
</x-app-layout>
