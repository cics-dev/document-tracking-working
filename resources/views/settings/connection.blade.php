<x-layouts.app>
    <section class="w-full">
        @include('partials.settings-heading')

        <x-settings.layout heading="Connection test" subheading="Check how quickly your device can reach the document server.">
            <div
                data-endpoint="{{ route('settings.connection.sample', absolute: false) }}"
                data-test-token="{{ csrf_token() }}"
                data-upload-bytes="{{ \App\Http\Controllers\ConnectionTestController::UPLOAD_BYTES }}"
                x-ref="test"
                x-data="{
                    controller: null,
                    phase: '',
                    error: '',
                    latency: null,
                    download: null,
                    upload: null,

                    destroy() {
                        this.controller?.abort();
                    },

                    async run() {
                        if (this.controller) return;
                        this.error = '';
                        this.latency = this.download = this.upload = null;

                        if (!navigator.onLine) {
                            this.error = 'You seem to be offline. Reconnect and try again.';
                            return;
                        }

                        const { endpoint, testToken, uploadBytes } = this.$refs.test.dataset;
                        const controller = new AbortController();
                        const timeout = setTimeout(() => controller.abort(), 60000);
                        this.controller = controller;
                        const options = {
                            cache: 'no-store',
                            credentials: 'same-origin',
                            redirect: 'error',
                            signal: controller.signal,
                            headers: { Accept: 'application/json' },
                        };

                        try {
                            this.phase = 'Measuring latency…';
                            let started = performance.now();
                            const ping = await fetch(endpoint, options);
                            if (ping.status !== 204) throw new Error('Request failed');
                            this.latency = Math.round(performance.now() - started);

                            this.phase = 'Measuring download…';
                            started = performance.now();
                            const download = await fetch(endpoint + '?sample=1', options);
                            if (!download.ok) throw new Error('Request failed');
                            const sample = await download.arrayBuffer();
                            this.download = this.megabits(sample.byteLength, started);

                            this.phase = 'Measuring upload…';
                            const bytes = crypto.getRandomValues(new Uint8Array(Number(uploadBytes)));
                            started = performance.now();
                            const upload = await fetch(endpoint, {
                                ...options,
                                method: 'POST',
                                headers: {
                                    ...options.headers,
                                    'Content-Type': 'application/octet-stream',
                                    'X-CSRF-TOKEN': testToken,
                                },
                                body: bytes,
                            });
                            if (upload.status !== 204) throw new Error('Request failed');
                            this.upload = this.megabits(bytes.length, started);
                            this.phase = 'Test complete.';
                        } catch (error) {
                            this.phase = '';
                            if (!navigator.onLine) {
                                this.error = 'You seem to be offline. Reconnect and try again.';
                            } else if (error.name === 'AbortError') {
                                this.error = 'The connection test timed out. Try again when the connection is more stable.';
                            } else {
                                this.error = 'The connection test could not finish. Check your connection and try again shortly.';
                            }
                        } finally {
                            clearTimeout(timeout);
                            this.controller = null;
                        }
                    },

                    megabits(bytes, started) {
                        return (bytes * 8 / Math.max(performance.now() - started, 1) / 1000).toFixed(2);
                    },
                }"
                class="max-w-xl space-y-5"
            >
                <flux:text>The test uses about 320 KB of data. Results are estimates for this server.</flux:text>

                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <dt class="text-sm font-medium">Latency</dt>
                        <dd class="mt-1 tabular-nums" x-text="latency === null ? '—' : latency + ' ms'">—</dd>
                    </div>
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <dt class="text-sm font-medium">Download</dt>
                        <dd class="mt-1 tabular-nums" x-text="download === null ? '—' : download + ' Mbps'">—</dd>
                    </div>
                    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <dt class="text-sm font-medium">Upload</dt>
                        <dd class="mt-1 tabular-nums" x-text="upload === null ? '—' : upload + ' Mbps'">—</dd>
                    </div>
                </dl>

                <flux:button type="button" variant="primary" icon="signal" x-on:click="run()" x-bind:disabled="controller !== null">
                    Run connection test
                </flux:button>

                <p x-cloak x-show="phase" x-text="phase" role="status" class="text-sm text-zinc-600 dark:text-zinc-300"></p>
                <p x-cloak x-show="error" x-text="error" role="alert" class="text-sm text-amber-800 dark:text-amber-300"></p>
            </div>
        </x-settings.layout>
    </section>
</x-layouts.app>
