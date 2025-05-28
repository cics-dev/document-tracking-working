<div class="fixed top-15 left-1/2 transform -translate-x-1/2 w-full max-w-md">
  <!-- START: Card Wrapper (adjust size & styles here) -->
  <div class="max-w-lg mx-auto bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-[0_8px_20px_rgba(0,0,0,0.9)]">
    <div class="flex flex-col gap-6">

      <!-- Logo -->
      <div class="flex justify-center mt-0">
        <img src="{{ asset('/assets/img/zppsu-logo.png') }}" alt="Logo" class="h-28 w-auto" />
      </div>

      <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

      <!-- Session Status -->
      <x-auth-session-status class="text-center" :status="session('status')" />

      <form wire:submit="login" class="flex flex-col gap-6">
          <!-- Email Address -->
          <flux:input
              wire:model="email"
              :label="__('Email')"
              type="email"
              required
              autofocus
              autocomplete="email"
              placeholder="email@example.com"
          />

          <!-- Password -->
          <div class="relative">
              <flux:input
                  wire:model="password"
                  :label="__('Password')"
                  type="password"
                  required
                  autocomplete="current-password"
                  :placeholder="__('Password')"
              />

              @if (Route::has('password.request'))
                  <flux:link class="absolute right-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                      {{ __('Forgot your password?') }}
                  </flux:link>
              @endif
          </div>

          <!-- Remember Me -->
          <flux:checkbox wire:model="remember" :label="__('Remember me')" />

          <div class="flex items-center justify-end">
              <flux:button variant="primary" type="submit" class="w-full hover:bg-blue-600">
                {{ __('Log in') }}
              </flux:button>
          </div>
      </form>

      @if (Route::has('register'))
          <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
              {{ __('Don\'t have an account?') }}
              <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
          </div>
      @endif
    </div>
  </div>
  <!-- END: Card Wrapper -->
</div>
