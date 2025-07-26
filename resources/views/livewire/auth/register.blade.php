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
      <div class="flex flex-col gap-5 sm:gap-0">

        <!-- Logo -->
        <div class="flex justify-center">
          <img src="{{ asset('/assets/img/hd-logo.png') }}" alt="ZPPSU Logo" class="h-24 sm:h-28 w-auto" />
        </div>

        <x-auth-header 
          :title="__('Create an account')" 
          :description="__('Enter your details below to create your account')" 
          class="text-center"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form wire:submit="register" class="flex flex-col gap-4 sm:gap-5">
          <!-- Name -->
          <div class="space-y-1">
            <flux:input
              wire:model="name"
              :label="__('Name')"
              type="text"
              required
              autofocus
              autocomplete="name"
              :placeholder="__('Full name')"
            />
          </div>

          <!-- Email Address -->
          <div class="space-y-1">
            <flux:input
              wire:model="email"
              :label="__('Email address')"
              type="email"
              required
              autocomplete="email"
              placeholder="email@example.com"
            />
          </div>

          <!-- Password -->
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

          <!-- Confirm Password -->
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

          <!-- Submit Button -->
          <div>
            <flux:button 
              variant="primary" 
              type="submit" 
              class="w-full py-2.5 px-4 bg-blue-600 hover:bg-green-600 text-white font-medium rounded-md transition hover:scale-[1.02] active:scale-[0.98]"
            >
              {{ __('Create account') }}
            </flux:button>
          </div>
        </form>

        <div class="text-center text-sm text-gray-600 dark:text-gray-400 pt-2">
          {{ __('Already have an account?') }}
          <flux:link 
            :href="route('login')" 
            wire:navigate
            class="text-blue-600 dark:text-blue-400 hover:text-black dark:hover:text-black hover:underline ml-1"
          >
            {{ __('Log in') }}
          </flux:link>
        </div>
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