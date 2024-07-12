export default global = {
    namespaced: true,
    state: () => ({
        userUuid: '',
        userAdmin: false,
    }),
    mutations: {
        setUuid (state, userUuid) {
            state.userUuid = userUuid;
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
