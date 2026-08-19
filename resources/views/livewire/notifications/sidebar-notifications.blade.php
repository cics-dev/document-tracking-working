<div wire:poll.10s="refreshNotifications"
    x-data="{
        sound: localStorage.getItem('dts-notification-sound') !== 'off',
        audioContext: null,
        init() {
            const unlock = () => {
                this.ensureAudio();
                if (this.audioContext?.state === 'suspended') this.audioContext.resume();
            };
            window.addEventListener('pointerdown', unlock, { once: true });
            window.addEventListener('keydown', unlock, { once: true });
        },
        ensureAudio() {
            if (!this.audioContext) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) this.audioContext = new AudioContextClass();
            }
            return this.audioContext;
        },
        playSound() {
            if (!this.sound) return;
            const context = this.ensureAudio();
            if (!context) return;
            const play = () => {
                const oscillator = context.createOscillator();
                const gain = context.createGain();
                oscillator.type = 'sine'; oscillator.frequency.value = 880;
                gain.gain.setValueAtTime(0.001, context.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.18, context.currentTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.001, context.currentTime + 0.35);
                oscillator.connect(gain); gain.connect(context.destination);
                oscillator.start(); oscillator.stop(context.currentTime + 0.36);
            };
            context.state === 'suspended' ? context.resume().then(play).catch(() => {}) : play();
        },
        toggleSound() {
            this.sound = !this.sound;
            localStorage.setItem('dts-notification-sound', this.sound ? 'on' : 'off');
            if (this.sound) this.playSound();
        }
    }"
    @new-document-notification.window="
        playSound()
    "
    class="contents">
    <div class="mb-2 flex items-center justify-between px-2 text-xs text-zinc-500">
        <span>Documents @if($unreadTotal)<span class="ml-1 rounded-full bg-red-600 px-2 py-0.5 font-semibold text-white">{{ $unreadTotal > 99 ? '99+' : $unreadTotal }}</span>@endif</span>
        <button type="button" class="rounded p-1 hover:bg-zinc-200" @click="toggleSound()" :title="sound ? 'Sound on — click to turn off' : 'Sound off — click to turn on and test'">
            <flux:icon x-show="sound" icon="speaker-wave" class="size-4" />
            <flux:icon x-cloak x-show="!sound" icon="speaker-x-mark" class="size-4" />
        </button>
    </div>
    @if(auth()->user()->hasAccess('view_all_documents'))
        <flux:navlist.item icon="inbox-arrow-down" :href="route('documents.list-documents', 'all')" :current="request()->is('documents/all')" wire:navigate :badge="$unreadAll ?: null" :badge-color="$unreadAll ? 'red' : null">{{ __('All Documents') }}</flux:navlist.item>
    @endif
    @if(auth()->user()->hasAccess('receive_documents'))
        <flux:navlist.item icon="inbox-arrow-down" :href="route('documents.list-documents', 'received')" :current="request()->is('documents/received')" wire:navigate :badge="$unreadReceived ?: null" :badge-color="$unreadReceived ? 'red' : null">{{ __('Received Documents') }}</flux:navlist.item>
    @endif
    @if(auth()->user()->hasAccess('send_documents'))
        <flux:navlist.item icon="inbox-stack" :href="route('documents.list-documents', 'Sent')" :current="request()->is('documents/sent')" wire:navigate :badge="$unreadSent ?: null" :badge-color="$unreadSent ? 'red' : null">{{ __('Sent Documents') }}</flux:navlist.item>
        <flux:navlist.item icon="document-plus" :href="route('documents.create-document')" :current="request()->routeIs('documents.create-document')" wire:navigate>{{ __('Write Document') }}</flux:navlist.item>
    @endif
    @if(auth()->user()->hasAccess('receive_external_documents') || auth()->user()->hasAccess('send_external_documents'))
        <flux:navlist.item icon="inbox-arrow-down" :href="route('documents.list-external-documents')" :current="request()->is('documents/list-external-documents')" wire:navigate :badge="$unreadExternal ?: null" :badge-color="$unreadExternal ? 'red' : null">{{ __('External Documents') }}</flux:navlist.item>
    @endif
    @if($showToast)
        <div class="fixed right-4 top-4 z-50 flex max-w-sm items-start gap-3 rounded-lg border border-indigo-200 bg-white p-4 shadow-xl">
            <div class="rounded-full bg-indigo-100 p-2 text-indigo-600"><flux:icon icon="bell-alert" class="size-5" /></div>
            <div class="flex-1"><p class="font-semibold">New document received</p><p class="text-sm text-zinc-600">You now have {{ $unreadTotal }} unread {{ Str::plural('document', $unreadTotal) }}.</p></div>
            <button type="button" wire:click="dismissToast"><flux:icon icon="x-mark" class="size-4" /></button>
        </div>
    @endif
</div>
