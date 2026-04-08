<nav
    x-data="{
        open: false,
        scrolled: false,
        handleScroll() {
            this.scrolled = window.scrollY > 8;
        },
    }"
    x-init="handleScroll(); window.addEventListener('scroll', () => handleScroll(), { passive: true })"
    class="sticky top-0 z-40 border-b border-transparent bg-slate-950/75 transition-all duration-200"
    :class="scrolled
        ? 'backdrop-blur-md supports-[backdrop-filter]:bg-slate-950/70 border-slate-800/80 shadow-[0_10px_30px_-18px_rgba(15,23,42,0.95)]'
        : 'backdrop-blur-0 supports-[backdrop-filter]:bg-slate-950/55'"
>
    @php
        $user = Auth::user();
        $canManageUsers = $user?->canManageUsers() ?? false;
        $canViewMembers = $user?->canViewMembers() ?? false;
        $canManageFinance = $user?->canManageFinance() ?? false;
        $canViewFinance = $user?->canViewFinance() ?? false;
        $canManageAnnouncements = $user?->canManageAnnouncements() ?? false;
        $canManageCalendar = $user?->canManageCalendar() ?? false;
        $canViewOwnMemberProfile = $user?->canViewOwnMemberProfile() ?? false;
        $isMembersNavActive = request()->routeIs('members.*') && ! request()->routeIs('members.self');
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

                <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">
                    {{ __('Calendar') }}
                </x-nav-link>

                @if ($canViewFinance)
                    <x-nav-link :href="route('contributions.index')" :active="request()->routeIs('contributions.*')">
                        {{ __('Contributions') }}
                    </x-nav-link>

                    <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                        {{ __('Expenses') }}
                    </x-nav-link>
                @endif

                @if ($canViewMembers)
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

                        @if ($canManageAnnouncements)
                            <x-dropdown-link :href="route('announcements.index')">
                                {{ __('Announcements') }}
                            </x-dropdown-link>
                        @endif

                        @if ($canManageCalendar)
                            <x-dropdown-link :href="route('calendar.create')">
                                {{ __('Add Event') }}
                            </x-dropdown-link>
                        @endif

                        @if ($canManageUsers)
                            <x-dropdown-link :href="route('users.index')">
                                {{ __('User Roles') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('activity-logs.index')">
                                {{ __('Activity Tracker') }}
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

            <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">
                {{ __('Calendar') }}
            </x-responsive-nav-link>

            @if ($canViewFinance)
                <x-responsive-nav-link :href="route('contributions.index')" :active="request()->routeIs('contributions.*')">
                    {{ __('Contributions') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                    {{ __('Expenses') }}
                </x-responsive-nav-link>
            @endif

            @if ($canViewMembers)
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

                @if ($canManageAnnouncements)
                    <x-responsive-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">
                        {{ __('Announcements') }}
                    </x-responsive-nav-link>
                @endif

                @if ($canManageCalendar)
                    <x-responsive-nav-link :href="route('calendar.create')" :active="request()->routeIs('calendar.create')">
                        {{ __('Add Event') }}
                    </x-responsive-nav-link>
                @endif

                @if ($canManageUsers)
                    <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('User Roles') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">
                        {{ __('Activity Tracker') }}
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
