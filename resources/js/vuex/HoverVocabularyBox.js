export default {
    namespaced: true,
    state: () => ({
        dictionaryTranslation: '',
        deeplTranslation: '',
        dictionarySearchTerm: '',
        disabledWhileSelecting: false,
        active: false,
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
            console.log('hoverVocabularyBox set value', payload.propertyName, payload.value)
            state[payload.propertyName] = payload.value;
        },
    },
    getters: { }
}
