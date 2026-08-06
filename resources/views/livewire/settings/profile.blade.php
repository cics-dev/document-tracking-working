<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your personal and professional details')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full">

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- PROFILE PICTURE / AVATAR SECTION                   --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="mb-8">
                <flux:heading size="lg" class="mb-4">Profile Picture</flux:heading>
                <flux:separator variant="subtle" class="mb-3" />

                <p class="text-sm text-gray-500 dark:text-zinc-400 mb-3 text-center sm:text-left">
                    Choose an avatar, upload a custom image, or take a photo with your webcam.
                </p>

                <div class="flex flex-col sm:grid sm:grid-cols-4 gap-4 mb-3 items-center">
                    {{-- 1. Current Avatar Box --}}
                    <div class="w-full max-w-xs border border-gray-200 dark:border-zinc-700 rounded-lg p-4 flex flex-col items-center justify-center min-h-[200px] bg-white dark:bg-zinc-800">
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Current Avatar</label>
                        <div class="relative group">
                            @if($custom_avatar)
                                <img src="{{ $custom_avatar->temporaryUrl() }}" class="w-28 h-28 rounded-full object-cover border-4 border-indigo-400 shadow-lg">
                            @elseif(!empty($captured_avatar))
                                <img src="{{ $captured_avatar }}" class="w-28 h-28 rounded-full object-cover border-4 border-green-400 shadow-lg">
                            @elseif(!empty($selected_preset_avatar))
                                <img src="{{ asset($selected_preset_avatar) }}" class="w-28 h-28 rounded-full object-cover border-4 border-amber-400 shadow-lg">
                            @elseif($current_avatar)
                                <img src="{{ auth()->user()->avatar_url }}" class="w-28 h-28 rounded-full object-cover border-4 border-blue-400 shadow-lg">
                            @else
                                <div class="w-28 h-28 rounded-full bg-gray-100 dark:bg-zinc-700 border-4 border-dashed border-gray-300 dark:border-zinc-600 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-gray-400 dark:text-zinc-500">{{ auth()->user()->initials() }}</span>
                                </div>
                            @endif

                            @if($current_avatar || $custom_avatar || !empty($captured_avatar) || !empty($selected_preset_avatar))
                                <button type="button" wire:click="removeAvatar"
                                    class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs shadow-lg transition-colors">
                                    ✕
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Choose an Avatar Box --}}
                    <div class="w-full max-w-xs border border-gray-200 dark:border-zinc-700 rounded-lg p-4 flex flex-col items-center justify-center min-h-[200px] bg-white dark:bg-zinc-800">
                        <div class="text-center">
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-1">Choose an Avatar</label>
                            <p class="text-xs text-gray-400 dark:text-zinc-500 mb-3">Select from preset avatars</p>
                        </div>
                        <button type="button" onclick="openAvatarModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Browse Avatars
                        </button>
                    </div>

                    {{-- 3. Capture from Webcam Box --}}
                    <div class="w-full max-w-xs border border-gray-200 dark:border-zinc-700 rounded-lg p-4 flex flex-col items-center justify-center min-h-[200px] bg-white dark:bg-zinc-800">
                        <div class="text-center">
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-1">Capture from Webcam</label>
                            <p class="text-xs text-gray-400 dark:text-zinc-500 mb-3">Take a photo using your device camera</p>
                        </div>
                        <button type="button" onclick="openWebcamModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Open Camera
                        </button>
                    </div>

                    {{-- 4. Upload Custom Image Box --}}
                    <div class="w-full max-w-xs border border-gray-200 dark:border-zinc-700 rounded-lg p-4 flex flex-col items-center justify-center min-h-[200px] bg-white dark:bg-zinc-800">
                        <div class="w-full flex flex-col items-center">
                            <flux:input type="file" wire:model="custom_avatar" label="Upload Custom Image" description="PNG, JPG (Max: 3MB)" accept="image/png, image/jpeg, image/webp" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- PERSONAL INFORMATION                               --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="mb-8">
                <flux:heading size="lg" class="mb-4">Personal Information</flux:heading>
                <flux:separator variant="subtle" class="mb-6" />
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-3">
                        <flux:field>
                            <flux:label>Family Name <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="family_name" type="text" required autofocus autocomplete="family-name" />
                            <flux:error name="family_name" />
                        </flux:field>
                    </div>
                    <div class="md:col-span-3">
                        <flux:field>
                            <flux:label>Given Name <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="given_name" type="text" required autocomplete="given-name" />
                            <flux:error name="given_name" />
                        </flux:field>
                    </div>
                    <div class="md:col-span-3">
                        <flux:input wire:model="middle_name" label="Middle Name" type="text" autocomplete="additional-name" />
                    </div>
                    <div class="md:col-span-1">
                        <flux:input wire:model="middle_initial" label="MI" type="text" maxlength="1" />
                    </div>
                    <div class="md:col-span-2">
                        <flux:input wire:model="suffix" label="Suffix" type="text" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-2">
                        <flux:input wire:model="honorifics" label="Honorifics" placeholder="(Mr./Ms.)" type="text" />
                    </div>
                    <div class="md:col-span-2">
                        <flux:input wire:model="titles" label="Title" placeholder="(PhD, etc.)" type="text" />
                    </div>
                    <div class="md:col-span-2">
                        <flux:select wire:model="gender" label="Gender" placeholder="Select...">
                            <flux:select.option value="male">Male</flux:select.option>
                            <flux:select.option value="female">Female</flux:select.option>
                            <flux:select.option value="other">Other</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="md:col-span-6">
                        <flux:field>
                            <flux:label>Email <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="email" type="email" required autocomplete="email" />
                            <flux:error name="email" />
                        </flux:field>

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                            <div class="mt-2">
                                <flux:text>
                                    {{ __('Your email address is unverified.') }}
                                    <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </flux:link>
                                </flux:text>

                                @if (session('status') === 'verification-link-sent')
                                    <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </flux:text>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- PROFILE SIGNATURE                                  --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="mb-8">
                <flux:heading size="lg" class="mb-4">Profile Signature</flux:heading>
                <flux:separator variant="subtle" class="mb-6" />
                
                <div class="flex flex-col sm:flex-row items-start gap-6">
                    <div class="flex-shrink-0 relative">
                        <label class="block text-sm font-medium text-white-700 mb-2">Current Signature</label>
                        <div class="relative group">
                            
                            @if($signature) 
                                <img src="{{ $signature->temporaryUrl() }}" class="w-40 h-20 object-contain border-2 border-gray-200 rounded bg-gray-50">
                                
                                <div class="absolute -top-2 -right-2">
                                    <flux:button wire:click="$set('signature', null)" icon="x-mark" size="xs" variant="filled" class="rounded-full bg-red-500 hover:bg-red-600 text-white border-none" />
                                </div>

                            @elseif($current_signature)
                                <img src="{{ asset('storage/' . $current_signature) }}" class="w-40 h-20 object-contain border-2 border-gray-200 rounded bg-gray-50">
                                
                            @else
                                <div class="w-40 h-20 bg-gray-50 border-2 border-dashed border-gray-300 rounded flex items-center justify-center text-gray-400">
                                    <span class="text-xs text-gray-400">No Signature</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex-grow">
                        <flux:input type="file" wire:model="signature" label="Upload Signature" description="PNG, JPG (Max: 2MB)" accept="image/png, image/jpeg" />
                        @error('signature') 
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Save button --}}
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center sm:justify-end w-full">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        {{-- <livewire:settings.delete-user-form /> --}}
    </x-settings.layout>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- AVATAR SELECTION MODAL                             --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="avatarModal" class="fixed inset-0 z-[9998] hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Choose an Avatar
                </h3>
                <button type="button" onclick="closeAvatarModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto">
                <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-3">
                    @foreach($preset_avatars as $avatar)
                        <button type="button"
                            wire:click="selectPresetAvatar('{{ $avatar }}')"
                            onclick="closeAvatarModal()"
                            class="relative group rounded-full overflow-hidden transition-all duration-200 focus:outline-none
                                {{ $selected_preset_avatar === 'assets/img/avatar/' . $avatar
                                    ? 'ring-4 ring-indigo-500 ring-offset-2 dark:ring-offset-zinc-800 scale-110 shadow-xl'
                                    : 'ring-2 ring-transparent hover:ring-indigo-300 hover:scale-105 hover:shadow-md' }}">
                            <img src="{{ asset('assets/img/avatar/' . $avatar) }}"
                                 alt="{{ pathinfo($avatar, PATHINFO_FILENAME) }}"
                                 class="w-14 h-14 rounded-full object-cover">
                            @if($selected_preset_avatar === 'assets/img/avatar/' . $avatar)
                                <div class="absolute inset-0 bg-indigo-500/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- WEBCAM CAPTURE MODAL                               --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div id="webcamModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Capture Photo
                </h3>
                <button type="button" onclick="closeWebcamModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6">
                <div class="relative rounded-xl overflow-hidden bg-black aspect-video mb-4">
                    <video id="webcamVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="webcamCanvas" class="hidden"></canvas>
                    <div id="webcamPreview" class="hidden absolute inset-0">
                        <img id="webcamPreviewImg" class="w-full h-full object-cover" alt="Captured">
                    </div>
                    <div id="webcamFlash" class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-150"></div>
                </div>

                <div id="webcamControls" class="flex items-center justify-center gap-3">
                    <button type="button" id="btnCapture" onclick="capturePhoto()"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="currentColor"/></svg>
                        Capture
                    </button>
                </div>

                <div id="webcamRetakeControls" class="hidden flex items-center justify-center gap-3">
                    <button type="button" onclick="retakePhoto()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-800 dark:text-white rounded-lg font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Retake
                    </button>
                    <button type="button" onclick="usePhoto()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Use This Photo
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- JAVASCRIPT                                         --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <script>
        // ─── Avatar Modal ────────────────────────────────
        function openAvatarModal() {
            // No automatic refresh – the list is loaded only on mount.
            document.getElementById('avatarModal').classList.remove('hidden');
            document.getElementById('avatarModal').classList.add('flex');
        }
        function closeAvatarModal() {
            document.getElementById('avatarModal').classList.add('hidden');
            document.getElementById('avatarModal').classList.remove('flex');
        }

        // ─── Webcam Modal ────────────────────────────────
        let webcamStream = null;
        let capturedDataUrl = null;

        function openWebcamModal() {
            const modal = document.getElementById('webcamModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            startWebcam();
        }

        function closeWebcamModal() {
            const modal = document.getElementById('webcamModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            stopWebcam();
            resetWebcamUI();
        }

        function startWebcam() {
            const video = document.getElementById('webcamVideo');
            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'user' } })
                .then(stream => {
                    webcamStream = stream;
                    video.srcObject = stream;
                })
                .catch(err => {
                    alert('Could not access camera. Please grant camera permission and try again.');
                    closeWebcamModal();
                });
        }

        function stopWebcam() {
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
                webcamStream = null;
            }
            const video = document.getElementById('webcamVideo');
            if (video) video.srcObject = null;
        }

        function capturePhoto() {
            const video = document.getElementById('webcamVideo');
            const canvas = document.getElementById('webcamCanvas');
            const ctx = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Mirror the capture (selfie mode)
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            capturedDataUrl = canvas.toDataURL('image/png');

            // Flash effect
            const flash = document.getElementById('webcamFlash');
            flash.style.opacity = '1';
            setTimeout(() => { flash.style.opacity = '0'; }, 150);

            // Show preview
            document.getElementById('webcamPreviewImg').src = capturedDataUrl;
            document.getElementById('webcamPreview').classList.remove('hidden');
            document.getElementById('webcamVideo').classList.add('hidden');
            document.getElementById('webcamControls').classList.add('hidden');
            document.getElementById('webcamRetakeControls').classList.remove('hidden');
            document.getElementById('webcamRetakeControls').classList.add('flex');
        }

        function retakePhoto() {
            capturedDataUrl = null;
            resetWebcamUI();
        }

        function resetWebcamUI() {
            document.getElementById('webcamPreview').classList.add('hidden');
            document.getElementById('webcamVideo').classList.remove('hidden');
            document.getElementById('webcamControls').classList.remove('hidden');
            document.getElementById('webcamRetakeControls').classList.add('hidden');
            document.getElementById('webcamRetakeControls').classList.remove('flex');
        }

        function usePhoto() {
            if (capturedDataUrl) {
                @this.call('setCapturedAvatar', capturedDataUrl);
                closeWebcamModal();
            }
        }
    </script>

    <style>
        #webcamVideo {
            transform: scaleX(-1);
        }
    </style>
</section>