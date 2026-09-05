import './bootstrap';

// Extension Stream Insulation
// Protects internal SRE telemetry & console from noisy third-party content scripts (e.g. MetaMask ObjectMultiplex)
if (typeof window !== 'undefined') {
    window.addEventListener('unhandledrejection', (event) => {
        const msg = event?.reason?.message || '';
        if (msg.includes('ObjectMultiplex') || msg.includes('EventEmitter') || msg.includes('app-init-liveness')) {
            event.preventDefault();
        }
    });
}
