<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible class="no-print border-r border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-800">

            <flux:sidebar.header>
                <flux:sidebar.brand :href="route('dashboard')" :logo="asset('/assets/img/zppsu-logo.png')" name="DTS - ZPPSU" wire:navigate />
                <flux:sidebar.collapse class="hidden lg:flex" tooltip="Collapse or expand sidebar" />
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <div class="grid">
                    <div class="px-3 py-2 text-sm font-medium text-zinc-400 in-data-flux-sidebar-collapsed-desktop:hidden">{{ __('Navigation') }}</div>
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:sidebar.item>
                    @if(auth()->user()?->hasAccess('manage_roles'))
                            <flux:sidebar.item icon="user-group" :href="route('roles')" :current="request()->routeIs('roles')" wire:navigate>{{ __('Roles') }}</flux:sidebar.item>
                    @endif
                    @if(auth()->user()?->hasAccess('manage_users'))
                            <flux:sidebar.item icon="user" :href="route('users.list-users')" :current="request()->routeIs('users.*')" wire:navigate>{{ __('Users') }}</flux:sidebar.item>
                    @endif
                    @if(auth()->user()?->hasAccess('manage_offices'))
                            <flux:sidebar.item icon="building-office" :href="route('offices.list-offices')" :current="request()->routeIs('offices.*')" wire:navigate>{{ __('Offices') }}</flux:sidebar.item>
                    @endif
                    @if(auth()->user()?->hasAccess('manage_access_rights'))
                            <flux:sidebar.item icon="key" :href="route('access-rights')" :current="request()->routeIs('access-rights')" wire:navigate>{{ __('Access Rights') }}</flux:sidebar.item>
                    @endif
                    @if(auth()->user()?->hasAccess('manage_document_flows'))
                            <flux:sidebar.item icon="document-text" :href="route('document-types')" :current="request()->routeIs('document-types')" wire:navigate>{{ __('Document Types') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="arrow-path-rounded-square" :href="route('document-flows')" :current="request()->routeIs('document-flows')" wire:navigate>{{ __('Document Flows') }}</flux:sidebar.item>
                    @endif
                    <livewire:notifications.sidebar-notifications />
                </div>
            </flux:sidebar.nav>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:sidebar.profile
                    :name="auth()->user()->position"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="no-print lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
