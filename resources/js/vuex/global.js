import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export default global = {
    namespaced: true,
    state: () => ({
        userUuid: '',
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
        setUuid (state, userUuid) {
            state.userUuid = userUuid;
        }
    },
    getters: {
        echo (state) {
            return state.echo;
        },
        userUuid(state) {
            return state.userUuid;
        }
    }
}
