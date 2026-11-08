<style>
    :is(html:not([data-forms-ready]), html[data-offline])
        :is(form[wire\:submit] [type="submit"], form[wire\:submit\.prevent] [type="submit"], [data-form-action]),
    [data-upload-pending] [data-form-action],
    form[data-document-editor]:not(:has([data-editor-ready])) [data-form-action] {
        opacity: .55;
        cursor: wait;
    }
</style>
<script>
(() => {
    if (window.dtsFormReadinessInstalled) return;
    window.dtsFormReadinessInstalled = true;

    const livewireForm = 'form[wire\\:submit], form[wire\\:submit\\.prevent]';
    const componentRoot = '[wire\\:id]';
    const formActions = livewireForm + ', [data-form-action]';
    const pending = new WeakMap();
    const uploads = new WeakMap();
    let ready = false;
    let noticeTimer;
    let noticeVersion = 0;

    function notify(message, persistent = false) {
        if (!document.body) return;
        let notice = document.getElementById('connection-status');
        if (!notice) {
            notice = document.createElement('div');
            notice.id = 'connection-status';
            notice.setAttribute('role', 'status');
            notice.className = 'fixed inset-x-4 bottom-4 z-[100] rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 shadow-lg sm:left-auto sm:max-w-sm';
            document.body.append(notice);
        }

        clearTimeout(noticeTimer);
        const version = ++noticeVersion;
        notice.textContent = message;
        notice.hidden = false;
        const dismiss = () => {
            if (noticeVersion === version) notice.hidden = true;
        };
        if (!persistent) noticeTimer = setTimeout(dismiss, 6500);

        return dismiss;
    }

    function offline() {
        document.documentElement.setAttribute('data-offline', '');
        notify('You seem to be offline. Reconnect to continue.', true);
    }

    function failed() {
        if (!navigator.onLine) {
            offline();
            return;
        }
        notify('The request could not be completed. Check your connection. If you were saving, check whether it went through before trying again.');
    }

    function blockIfUnready(event, element) {
        const root = element.closest(componentRoot);
        const form = element.closest(livewireForm);
        const type = form?.querySelector('[data-document-type]');
        let message;

        if (!navigator.onLine) {
            offline();
        } else if (!ready || (form?.hasAttribute('data-document-editor') && !form.querySelector('[data-editor-ready]'))) {
            message = 'This form is still loading. Please wait a moment.';
        } else if (uploads.get(root)?.size) {
            message = 'Please wait for the file upload to finish.';
        } else if (pending.get(root)) {
            message = 'Please wait for the current request to finish.';
        } else if (type && type.value !== form.dataset.configuredDocumentType) {
            message = 'The document settings are still loading. Please wait before continuing.';
            type.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        if (message) notify(message);
    }

    document.addEventListener('submit', event => {
        if (event.target.matches(livewireForm)) blockIfUnready(event, event.target);
    }, true);

    document.addEventListener('click', event => {
        const action = event.target.closest?.('[data-form-action]');
        if (action) blockIfUnready(event, action);
    }, true);

    document.addEventListener('change', event => {
        if (!event.target.matches('input[type="file"][wire\\:model]')) return;
        if (ready && navigator.onLine) return;

        event.target.value = '';
        blockIfUnready(event, event.target);
    }, true);

    document.addEventListener('livewire:init', () => {
        Livewire.interceptMessage(({ message, onFailure, onError, onFinish }) => {
            const root = message.component.el;
            if (!root.matches(formActions) && !root.querySelector(formActions)) return;

            pending.set(root, (pending.get(root) ?? 0) + 1);
            let dismissSlowNotice;
            const slowTimer = setTimeout(() => {
                if (root.isConnected && navigator.onLine) {
                    dismissSlowNotice = notify('Still working. Your connection may be slow.', true);
                }
            }, 5000);

            const cancelInterruptedUploads = () => {
                for (const call of message.payload.calls) {
                    if (['_startUpload', '_finishUpload', '_uploadErrored'].includes(call.method)) {
                        message.component.$wire.$cancelUpload(call.params[0]);
                    }
                }
            };
            onFailure(cancelInterruptedUploads);
            onError(({ response }) => {
                if ([408, 502, 503, 504].includes(response.status)) cancelInterruptedUploads();
            });

            onFinish(() => {
                clearTimeout(slowTimer);
                dismissSlowNotice?.();
                const remaining = pending.get(root) - 1;
                if (remaining > 0) pending.set(root, remaining);
                else pending.delete(root);
            });
        });

        Livewire.interceptRequest(({ onFailure, onError }) => {
            onFailure(failed);
            onError(({ response, preventDefault }) => {
                if ([408, 502, 503, 504].includes(response.status)) {
                    preventDefault();
                    failed();
                }
            });
        });
    });

    function setReady(value) {
        ready = value;
        document.documentElement.toggleAttribute('data-forms-ready', ready);
    }

    document.addEventListener('livewire:initialized', () => setReady(true));
    document.addEventListener('livewire:navigate', event => {
        if (!navigator.onLine && !event.detail.history && !event.detail.cached) {
            event.preventDefault();
            offline();
        }
    });
    document.addEventListener('livewire:navigating', () => setReady(false));
    document.addEventListener('livewire:navigated', () => {
        setReady(true);
        if (!navigator.onLine) offline();
    });

    document.addEventListener('livewire-upload-start', event => {
        const root = event.target.closest(componentRoot);
        if (!root) return;
        if (!uploads.has(root)) uploads.set(root, new Set());
        uploads.get(root).add(event.detail.property);
        root.setAttribute('data-upload-pending', '');
    });

    for (const eventName of ['livewire-upload-finish', 'livewire-upload-error', 'livewire-upload-cancel']) {
        document.addEventListener(eventName, event => {
            const root = event.target.closest(componentRoot);
            const properties = uploads.get(root);
            properties?.delete(event.detail.property);
            if (!properties?.size) root?.removeAttribute('data-upload-pending');
        });
    }

    window.addEventListener('offline', offline);
    window.addEventListener('online', () => {
        document.documentElement.removeAttribute('data-offline');
        notify('Connection restored.');
    });
    if (!navigator.onLine) document.addEventListener('DOMContentLoaded', offline, { once: true });
})();
</script>
