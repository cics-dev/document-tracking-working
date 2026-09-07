<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            /* Always reserve space for the scrollbar to prevent horizontal layout shift */
            html {
                overflow-y: scroll;
                scrollbar-gutter: stable;
            }

            /* Prevent flash of unstyled content during page transitions */
            [wire\:loading], [wire\:loading\.delay] {
                display: none;
            }

            /* Make sidebar sticky - stays fixed while main content scrolls */
            [data-flux-sidebar] {
                position: sticky !important;
                top: 0 !important;
                align-self: start !important;
                max-height: 100vh;
                overflow-y: auto;
            }

            /* Prevent Flux SVG icons from expanding full-screen before/during CSS load */
            svg[data-flux-icon], [data-flux-icon] {
                max-width: 1.5rem !important;
                max-height: 1.5rem !important;
            }
        </style>
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        {{-- Sidebar: sticky, full height, scrollable if content overflows --}}
        <flux:sidebar sticky stashable data-flux-allow-scroll class="no-print border-r border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-800 h-screen overflow-y-auto">

            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('landing') }}" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Navigation')" class="grid">
                    <flux:navlist.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>

                    @if (auth()->user()?->position === 'Administrator')
                        <flux:navlist.item icon="building-office" :href="route('offices.list-offices')" :current="request()->routeIs('offices.*')" wire:navigate>{{ __('Offices') }}</flux:navlist.item>
                        <flux:navlist.item icon="user" :href="route('users.list-users')" :current="request()->routeIs('users.*')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                    @else
                        @if (auth()->user()?->position === 'Records Officer')
                            <flux:navlist.item 
                                icon="inbox-arrow-down" 
                                :href="route('documents.list-documents', 'all')" 
                                :current="request()->is('documents/all')" 
                                :badge="$unreadAllCount > 0 ? $unreadAllCount : null"
                                :badge-color="$unreadAllCount > 0 ? 'red' : null" 
                                wire:navigate
                            >
                                {{ __('All Documents') }}
                            </flux:navlist.item>
                            <flux:navlist.item icon="inbox-stack" :href="route('documents.list-documents', 'sent')" :current="request()->is('documents/sent')" wire:navigate>
                                {{ __('Sent Documents') }}
                            </flux:navlist.item>
                        @elseif (auth()->user()?->position != 'Staff')
                            <flux:navlist.item 
                                icon="inbox-arrow-down" 
                                :href="route('documents.list-documents', 'received')" 
                                :current="request()->is('documents/received')" 
                                :badge="$unreadReceivedCount > 0 ? $unreadReceivedCount : null"
                                :badge-color="$unreadReceivedCount > 0 ? 'red' : null" 
                                wire:navigate
                            >
                                {{ __('Received Documents') }}
                            </flux:navlist.item>

                            <flux:navlist.item icon="inbox-stack" :href="route('documents.list-documents', 'sent')" :current="request()->is('documents/sent')" wire:navigate>
                                {{ __('Sent Documents') }}
                            </flux:navlist.item>
                        @endif
                        <flux:navlist.item 
                            icon="inbox-arrow-down" 
                            :href="route('documents.list-external-documents')" 
                            :current="request()->is('documents/list-external-documents')" 
                            :badge="$unreadExternalCount > 0 ? $unreadExternalCount : null"
                            :badge-color="$unreadExternalCount > 0 ? 'red' : null" 
                            wire:navigate
                        >
                            {{ __('External Documents') }}
                        </flux:navlist.item>
                    @endif
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="top" align="start" data-sidebar-profile-dropdown>
                @if(auth()->user()->avatar_url)
                    <button type="button" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-zinc-700 transition-colors w-full">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover ring-2 ring-indigo-400 bg-gray-200 dark:bg-zinc-600" width="32" height="32" loading="eager">
                        <span class="text-sm font-medium text-gray-700 dark:text-zinc-200 truncate">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 ml-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                    </button>
                @else
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevrons-up-down"
                    />
                @endif

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover rounded-lg bg-gray-200 dark:bg-zinc-600" width="32" height="32" loading="eager">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    @endif
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

                    {{-- Fixed: prevent double submission, button type="button" with explicit submit --}}
                    <form id="logout-form-desktop" method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="button" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors" onclick="event.stopPropagation(); document.getElementById('logout-form-desktop').submit();">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="no-print lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                @if(auth()->user()->avatar_url)
                    <button type="button" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-zinc-700 transition-colors">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-7 h-7 rounded-full object-cover ring-2 ring-indigo-400 bg-gray-200 dark:bg-zinc-600" width="28" height="28" loading="eager">
                        <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                @else
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />
                @endif

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover rounded-lg bg-gray-200 dark:bg-zinc-600" width="32" height="32" loading="eager">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    @endif
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

                    {{-- Fixed: prevent double submission, button type="button" with explicit submit --}}
                    <form id="logout-form-mobile" method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="button" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors" onclick="event.stopPropagation(); document.getElementById('logout-form-mobile').submit();">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
