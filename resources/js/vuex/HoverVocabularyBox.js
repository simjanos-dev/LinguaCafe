export default {
    namespaced: true,
    state: () => ({
        active: false,
        dictionaryTranslation: '',
        apiTranslations: [],
        dictionarySearchTerm: '',
        disabledWhileSelecting: false,
        lastHoveredWordIndex: -1,
        key: 0,
        hoveredWords: null,
        hoveredPhrase: -1,
        stage: null,
        reading: '',
        userTranslation: '',
        positionLeft: 0,
        positionTop: 0,
        arrowPosition: 'top',
        image: null,
    }),
    mutations: {
        setValue (state, payload) {
            state[payload.propertyName] = payload.value;
        },
    },
    getters: { }
}
