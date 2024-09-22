import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export default {
    namespaced: true,
    state: () => ({
        userUuid: '',
        userName: false,
        userEmail: false,
        userAdmin: false,
        echo: new Echo({
            broadcaster: 'pusher',
            key: 'wjp2pou6ebgibtwccqsj',
            cluster: 'mt1',
            forceTLS: false,
            wsHost: window.location.hostname,
            wsPort: 6001,
            enabledTransports: ['ws', 'wss'],
        })
    }),
    mutations: {
        setUuid (state, userUuid) {
            state.userUuid = userUuid;
        },
        setUserName (state, userName) {
            state.userName = userName;
        },
        setUserEmail (state, userEmail) {
            state.userEmail = userEmail;
        },
        setUserAdmin (state, userAdmin) {
            state.userAdmin = userAdmin;
        }
    },
    getters: {
        echo (state) {
            return state.echo;
        },
        userUuid(state) {
            return state.userUuid;
        },
        userAdmin(state) {
            return state.userAdmin;
        }
    }
}
