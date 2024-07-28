export default {
    namespaced: true,
    state: () => ({
        active: false,
        dictionaryTranslation: '',
        deeplTranslation: '',
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
    }),
    mutations: {
        setValue (state, payload) {
            state[payload.propertyName] = payload.value;
        },
    },
    getters: { }
}
