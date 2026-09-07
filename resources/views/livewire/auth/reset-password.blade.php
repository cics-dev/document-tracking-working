<<<<<<< HEAD
<div>
    <div class="fixed inset-0 flex items-center justify-center p-4 sm:p-6 bg-[#660710]">
        <div class="particle-bg absolute inset-0 z-0 opacity-60"></div>

        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-45">
            <img src="{{ asset('/assets/img/hd-logo.png') }}" alt="ZPPSU Logo Background" class="h-[500px] sm:h-[700px] w-auto" />
        </div>

        <div class="w-auto max-w-md mx-auto bg-white/88 dark:bg-zinc-600/90 p-6 sm:p-8 rounded-lg shadow-md dark:shadow-black/50 border border-gray-200 dark:border-zinc-700 relative z-10 backdrop-blur-sm">
            <div class="flex flex-col gap-5 sm:gap-5">
                <div class="flex justify-center">
                    <img src="{{ asset('/assets/img/hd-logo.png') }}" alt="ZPPSU Logo" class="h-24 sm:h-28 w-auto" />
                </div>

                <x-auth-header
                    :title="__('Reset password')"
                    :description="__('Please enter your new password below')"
                    class="text-center"
                />

                <x-auth-session-status class="text-center" :status="session('status')" />

                <form wire:submit="resetPassword" class="flex flex-col gap-4 sm:gap-5">
                    <div class="space-y-1">
                        <flux:input
                            wire:model="email"
                            :label="__('Email')"
                            type="email"
                            required
                            autocomplete="email"
                            readonly
                        />
                    </div>

                    <div class="space-y-1">
                        <flux:input
                            wire:model="password"
                            :label="__('Password')"
                            type="password"
                            required
                            autocomplete="new-password"
                            :placeholder="__('Password')"
                        />
                    </div>

                    <div class="space-y-1">
                        <flux:input
                            wire:model="password_confirmation"
                            :label="__('Confirm password')"
                            type="password"
                            required
                            autocomplete="new-password"
                            :placeholder="__('Confirm password')"
                        />
                    </div>

                    <div>
                        <flux:button
                            type="submit"
                            variant="primary"
                            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-green-600 text-white font-medium rounded-md transition hover:scale-[1.02] active:scale-[0.98]"
                        >
                            {{ __('Reset password') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .particle-bg {
            background: transparent;
            position: absolute;
            overflow: hidden;
        }

        .particle-bg::before {
            content: "";
            position: absolute;
            width: 200%;
            height: 200%;
            background-image:
                radial-gradient(circle, rgba(255,255,255,0.3) 1.5px, transparent 1.5px),
                radial-gradient(circle, rgba(255,255,255,0.4) 2px, transparent 2px),
                radial-gradient(circle, rgba(255,255,255,0.2) 1px, transparent 1px);
            background-size:
                80px 80px,
                120px 120px,
                60px 60px;
            animation: particleMove 40s linear infinite;
            filter: blur(0.4px);
        }

        @keyframes particleMove {
            0% {
                background-position: 0 0, 60px 30px, 30px 60px;
            }
            100% {
                background-position:
                    80px 80px,
                    180px 130px,
                    90px 140px;
            }
        }
    </style>
=======
<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email')"
            type="email"
            required
            autocomplete="email"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Reset password') }}
            </flux:button>
        </div>
    </form>
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
</div>
