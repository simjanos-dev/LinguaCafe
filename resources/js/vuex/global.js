import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export default global = {
    state: () => ({
        echo: new Echo({
            broadcaster: 'pusher',
            key: 'key1',
            cluster: 'mt1',
            forceTLS: false,
            wsHost: 'lingua.cafe',
            wsPort: 6001,
            enabledTransports: ['ws', 'wss'],
        })
    }),
    mutations: {
        increment (state) {
           state.count++
        }
    },
    getters: {
        echo (state) {
            return state.echo;
        }
    }
}
