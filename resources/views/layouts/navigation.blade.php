<nav x-data="{ open: false }" class="relative z-40 bg-slate-900/65 backdrop-blur">
    @php
        $user = Auth::user();
        $canManageUsers = $user?->role === 'admin';
        $canManageMembers = in_array($user?->role, ['admin', 'president', 'treasurer'], true);
        $canManageFinance = in_array($user?->role, ['admin', 'treasurer'], true);
        $canViewOwnMemberProfile = in_array($user?->role, ['member', 'officer', 'president', 'treasurer'], true);
        $isMembersNavActive = request()->routeIs('members.index', 'members.create', 'members.store', 'members.edit', 'members.update', 'members.show');
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid h-16 grid-cols-[auto_1fr_auto] items-center gap-4">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-sky-300" />
                    </a>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="hidden items-center justify-center gap-2 sm:flex">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-nav-link>

                @if ($canManageFinance)
                    <x-nav-link :href="route('contributions.index')" :active="request()->routeIs('contributions.*')">
                        {{ __('Contributions') }}
                    </x-nav-link>

                    <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                        {{ __('Expenses') }}
                    </x-nav-link>
                @endif

                @if ($canManageMembers)
                    <x-nav-link :href="route('members.index')" :active="$isMembersNavActive">
                        {{ __('Members') }}
                    </x-nav-link>
                @endif

                @if ($canViewOwnMemberProfile)
                    <x-nav-link :href="route('members.self')" :active="request()->routeIs('members.self')">
                        {{ __('Profile') }}
                    </x-nav-link>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden justify-self-end sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-md border border-slate-800 bg-slate-900/70 px-3 py-2 text-sm font-medium leading-4 text-slate-300 transition duration-150 ease-in-out hover:border-slate-700 hover:text-sky-200 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Account Settings') }}
                        </x-dropdown-link>

                        @if ($canManageUsers)
                            <x-dropdown-link :href="route('users.index')">
                                {{ __('User Roles') }}
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center justify-self-end sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-slate-200 focus:bg-slate-800 focus:text-slate-200 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-slate-800 bg-slate-900 sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if ($canManageFinance)
                <x-responsive-nav-link :href="route('contributions.index')" :active="request()->routeIs('contributions.*')">
                    {{ __('Contributions') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                    {{ __('Expenses') }}
                </x-responsive-nav-link>
            @endif

            @if ($canManageMembers)
                <x-responsive-nav-link :href="route('members.index')" :active="$isMembersNavActive">
                    {{ __('Members') }}
                </x-responsive-nav-link>
            @endif

            @if ($canViewOwnMemberProfile)
                <x-responsive-nav-link :href="route('members.self')" :active="request()->routeIs('members.self')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-slate-800 pt-4 pb-1">
            <div class="px-4">
                <div class="text-base font-medium text-slate-100">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-slate-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Account Settings') }}
                </x-responsive-nav-link>

                @if ($canManageUsers)
                    <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('User Roles') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
