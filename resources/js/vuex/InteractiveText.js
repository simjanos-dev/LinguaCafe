export default global = {
    namespaced: true,
    state: () => ({
        words: [],
    }),
    mutations: {
        setWords (state, words) {
            state.words = words;
        },
    },
    getters: {
        words(state) {
            return state.words;
        }
    }
}
