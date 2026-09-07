<div>
  <!-- Single root element wrapper (Livewire compliant) -->
  <div class="fixed inset-0 flex items-center justify-center p-4 sm:p-6 bg-[#660710]">
    
    <!-- FINAL PARTICLE BACKGROUND (Your requested smaller version) -->
    <div class="particle-bg absolute inset-0 z-0 opacity-60"></div>
    
    <!-- Background Logo - HUGE version -->
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-45">
      <img src="{{ asset('/assets/img/hd-logo.png') }}" alt="ZPPSU Logo Background" class="h-[500px] sm:h-[700px] w-auto" />
    </div>

    <!-- START: Card Wrapper -->
    <div class="w-auto max-w-md mx-auto bg-white/88 dark:bg-zinc-600/90 p-6 sm:p-8 rounded-lg shadow-md dark:shadow-black/50 border border-gray-200 dark:border-zinc-700 relative z-10 backdrop-blur-sm">
      <div class="flex flex-col gap-5 sm:gap-5">

        <!-- Logo -->
        <div class="flex justify-center">
          <img src="{{ asset('/assets/img/hd-logo.png') }}" alt="ZPPSU Logo" class="h-24 sm:h-28 w-auto" />
        </div>

        <x-auth-header 
          :title="__('Log in to your account')" 
          :description="__('Enter your email and password below to log in')" 
          class="text-center"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form wire:submit="login" class="flex flex-col gap-4 sm:gap-5">
          <!-- Email Address -->
          <div class="space-y-1">
            <flux:input
              wire:model="email"
              :label="__('Email')"
              type="email"
              required
              autofocus
              autocomplete="email"
              placeholder="email@example.com"
            />
          </div>

          <!-- Password -->
          <div class="space-y-1 relative">
            <flux:input
              wire:model="password"
              :label="__('Password')"
              type="password"
              required
              autocomplete="current-password"
              :placeholder="__('Password')"
            />

            @if (Route::has('password.request'))
              <flux:link 
                class="absolute right-0 top-0 text-sm text-blue-600 dark:text-blue-400 hover:text-black dark:hover:text-black hover:underline"
                :href="route('password.request')" 
                wire:navigate
              >
                {{ __('Forgot your password?') }}
              </flux:link>
            @endif
          </div>

          <!-- Remember Me -->
          <div class="flex items-center">
            <flux:checkbox 
              wire:model="remember" 
              :label="__('Remember me')"
              class="text-sm text-gray-600 dark:text-gray-300 hover:text-[#800000] dark:hover:text-[#a04040]"
            />
          </div>

          <!-- Submit Button -->
          <div>
            <flux:button 
              variant="primary" 
              type="submit" 
              class="w-full py-2.5 px-4 bg-blue-600 hover:bg-green-600 text-white font-medium rounded-md transition hover:scale-[1.02] active:scale-[0.98]"
            >
              {{ __('Log in') }}
            </flux:button>
          </div>
        </form>

        @if (Route::has('register'))
          <div class="text-center text-sm text-gray-600 dark:text-gray-400 pt-2">
          <!--  {{ __('Don\'t have an account?') }} -->
            <flux:link 
              :href="route('register')" 
              wire:navigate
              class="text-blue-600 dark:text-blue-400 hover:text-black dark:hover:text-black hover:underline ml-1"
            >
          <!--    {{ __('Sign up') }} -->
            </flux:link>
          </div>
        @endif
      </div>
    </div>
    <!-- END: Card Wrapper -->
  </div>

  <!-- FINAL PARTICLE STYLE (Smaller version) -->
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
</div>
