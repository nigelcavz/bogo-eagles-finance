<nav
    x-data="{
        open: false,
        scrolled: false,
        handleScroll() {
            this.scrolled = window.scrollY > 8;
        },
        closeMenu() {
            this.open = false;
        },
        toggleMenu() {
            this.open = !this.open;
        },
    }"
    x-init="handleScroll(); window.addEventListener('scroll', () => handleScroll(), { passive: true })"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @click.outside="closeMenu()"
    class="sticky top-0 z-40 border-b border-transparent bg-slate-950/75 transition-all duration-200"
    :class="scrolled
        ? 'backdrop-blur-md supports-[backdrop-filter]:bg-slate-950/70 border-slate-800/80 shadow-[0_10px_30px_-18px_rgba(15,23,42,0.95)]'
        : 'backdrop-blur-0 supports-[backdrop-filter]:bg-slate-950/55'"
>
    @php
        $user = Auth::user();
        $canManageUsers = $user?->canManageUsers() ?? false;
        $canViewMembers = $user?->canViewMembers() ?? false;
        $canViewFinance = $user?->canViewFinance() ?? false;
        $canManageAnnouncements = $user?->canManageAnnouncements() ?? false;
        $canManageCalendar = $user?->canManageCalendar() ?? false;
        $canViewOwnMemberProfile = $user?->canViewOwnMemberProfile() ?? false;
        $isMembersNavActive = request()->routeIs('members.*') && ! request()->routeIs('members.self');
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-14 items-center justify-between gap-3 py-2 sm:grid sm:h-16 sm:grid-cols-[1fr_auto_1fr] sm:py-0">
            <div class="flex items-center">
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <x-application-logo class="block h-10 w-auto fill-current text-sky-300" />
                        <div class="min-w-0">
                            <p class="text-base font-semibold leading-tight text-slate-100 sm:hidden">
                                Bogo Eagles Finance
                            </p>
                            <p class="mt-0.5 text-xs leading-tight text-slate-400 sm:hidden">
                                Cebu North Bogo Eagles Club
                            </p>
                            <p class="hidden text-[0.72rem] font-semibold uppercase leading-tight tracking-[0.2em] text-slate-100 sm:block">
                                <span class="block">Cebu North Bogo</span>
                                <span class="mt-0.5 block text-slate-300">Eagles Club Finance</span>
                            </p>
                        </div>
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

            <!-- Mobile Actions -->
            <div class="flex items-center gap-2 sm:hidden">
                <button @click="toggleMenu()" class="inline-flex items-center justify-center rounded-xl border border-slate-800 bg-slate-900/80 p-2 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-slate-100 focus:bg-slate-800 focus:text-slate-100 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak class="mobile-menu-dropdown-wrap" x-transition.opacity>
        <div class="absolute inset-0 bg-slate-950/45" @click="closeMenu()"></div>
        <div class="mx-auto max-w-7xl px-4 sm:hidden">
            <div class="mobile-menu-dropdown" x-transition:enter="transition ease-out duration-180" x-transition:enter-start="-translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-120" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-2 opacity-0">
            <div class="border-b border-slate-800/80 px-5 py-4">
                <div>
                    <p class="text-sm font-semibold text-slate-100">Bogo Eagles Finance</p>
                    <p class="mt-1 text-xs uppercase tracking-[0.2em] text-sky-300/80">Signed In</p>
                    <h3 class="mt-2 text-base font-semibold text-slate-100">{{ Auth::user()->name }}</h3>
                    <p class="mt-1 text-sm text-slate-400">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <div class="space-y-5 px-5 py-5">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Navigate</p>
                    <a href="{{ route('dashboard') }}" class="mobile-menu-link" @click="closeMenu()">
                        <span>Dashboard</span>
                        <span class="text-xs text-slate-500">{{ request()->routeIs('dashboard') ? 'Current' : 'Open' }}</span>
                    </a>
                    <a href="{{ route('calendar.index') }}" class="mobile-menu-link" @click="closeMenu()">
                        <span>Calendar</span>
                        <span class="text-xs text-slate-500">{{ request()->routeIs('calendar.*') ? 'Current' : 'Open' }}</span>
                    </a>
                    @if ($canViewFinance)
                        <a href="{{ route('contributions.index') }}" class="mobile-menu-link" @click="closeMenu()">
                            <span>Contributions</span>
                            <span class="text-xs text-slate-500">{{ request()->routeIs('contributions.*') ? 'Current' : 'Open' }}</span>
                        </a>
                        <a href="{{ route('expenses.index') }}" class="mobile-menu-link" @click="closeMenu()">
                            <span>Expenses</span>
                            <span class="text-xs text-slate-500">{{ request()->routeIs('expenses.*') ? 'Current' : 'Open' }}</span>
                        </a>
                    @endif
                    @if ($canViewMembers)
                        <a href="{{ route('members.index') }}" class="mobile-menu-link" @click="closeMenu()">
                            <span>Members</span>
                            <span class="text-xs text-slate-500">{{ $isMembersNavActive ? 'Current' : 'Open' }}</span>
                        </a>
                    @endif
                    @if ($canViewOwnMemberProfile)
                        <a href="{{ route('members.self') }}" class="mobile-menu-link" @click="closeMenu()">
                            <span>My Profile</span>
                            <span class="text-xs text-slate-500">{{ request()->routeIs('members.self') ? 'Current' : 'Open' }}</span>
                        </a>
                    @endif
                </div>

                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Actions</p>
                    <a href="{{ route('profile.edit') }}" class="mobile-menu-link" @click="closeMenu()">
                        <span>Account Settings</span>
                        <span class="text-xs text-slate-500">Manage</span>
                    </a>

                    @if ($canManageAnnouncements)
                        <a href="{{ route('announcements.index') }}" class="mobile-menu-link" @click="closeMenu()">
                            <span>Announcements</span>
                            <span class="text-xs text-slate-500">{{ request()->routeIs('announcements.*') ? 'Current' : 'Open' }}</span>
                        </a>
                    @endif

                    @if ($canManageCalendar)
                        <a href="{{ route('calendar.create') }}" class="mobile-menu-link" @click="closeMenu()">
                            <span>Add Event</span>
                            <span class="text-xs text-slate-500">Create</span>
                        </a>
                    @endif

                    @if ($canManageUsers)
                        <a href="{{ route('users.index') }}" class="mobile-menu-link" @click="closeMenu()">
                            <span>User Roles</span>
                            <span class="text-xs text-slate-500">{{ request()->routeIs('users.*') ? 'Current' : 'Open' }}</span>
                        </a>

                        <a href="{{ route('activity-logs.index') }}" class="mobile-menu-link" @click="closeMenu()">
                            <span>Activity Tracker</span>
                            <span class="text-xs text-slate-500">{{ request()->routeIs('activity-logs.*') ? 'Current' : 'Open' }}</span>
                        </a>
                    @endif
                </div>

                <form method="POST" action="{{ route('logout') }}" class="pt-1">
                    @csrf
                    <button type="submit" class="mobile-menu-link w-full justify-between text-red-200">
                        <span>Log Out</span>
                        <span class="text-xs text-red-300/70">Exit</span>
                    </button>
                </form>
            </div>
            </div>
        </div>
    </div>
</nav>
