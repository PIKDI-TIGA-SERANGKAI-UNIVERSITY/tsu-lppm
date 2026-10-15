import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = process.env.MIX_REVERB_APP_KEY || '';
const reverbHost = process.env.MIX_REVERB_HOST || (typeof window !== 'undefined' ? window.location.hostname : '');
const reverbPort = process.env.MIX_REVERB_PORT || 8080;
const reverbScheme = process.env.MIX_REVERB_SCHEME || 'http';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: reverbScheme === 'https',
    enabledTransports: ['ws', 'wss'],
});
