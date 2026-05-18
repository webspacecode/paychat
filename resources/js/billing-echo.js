import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

if (!window.Echo) {
    window.Pusher = Pusher;

    const wsHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
    const wsPort = import.meta.env.VITE_REVERB_PORT || (window.location.protocol === 'https:' ? 443 : 8080);
    const wsScheme = import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '');
    const forceTLS = wsScheme === 'https';

    // window.Echo = new Echo({
    //     broadcaster: 'reverb',
    //     key: import.meta.env.VITE_REVERB_APP_KEY || import.meta.env.VITE_REVERB_KEY || 'local',
    //     wsHost,
    //     wsPort,
    //     wssPort: wsPort,
    //     forceTLS,
    //     enabledTransports: forceTLS ? ['wss'] : ['ws'],
    // });

    window.Echo = new Echo({
        broadcaster: 'reverb',

        key: 'pc_live_8f3k29x',

        wsHost: 'paychat.shop',

        wsPort: 443,
        wssPort: 443,

        forceTLS: true,

        enabledTransports: ['ws', 'wss'],
    })

    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('Billing WebSocket connected');
    });

    window.Echo.connector.pusher.connection.bind('error', (error) => {
        console.error('Billing WebSocket error:', error);
    });
}
