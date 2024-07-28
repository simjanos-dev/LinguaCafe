export default {
    namespaced: true,
    state: () => ({
        active: false,
        vocabularyBottomSheetVisible: false,
        key: 0,

        /*
            Keep the sidebar hidden until the first position
            update, so it won't jump around on the screen when
            a text is opened.
        */
        sidebarHidden: true,

        // data for word
        word: '',
        reading: '',
        baseWord: '',
        baseWordReading: '',
        stage: 0,

        // data for phrase
        phrase: [],
        phraseReading: '',

        // data for both
        type: 'empty',
        inflections: [],
        kanjiList: [],
        translationText: '',

        // ui data
        tab: 0,
        width: 400,
        positionLeft: 0,
        positionTop: 0,
        height: 0,
        searchField: '',
        searchResults: [],
    }),
    mutations: {
        update (state) {
            state.key ++;
        },
        reset(state) {
            state.active = false;
            state.tab = 0;
            state.searchField = '';
            state.translationText = '';
            state.word = '';
            state.phrase = [];
            state.reading = '';
            state.kanjiList = [];
            state.baseWord = '';
            state.baseWordReading = '';
            state.stage = 2;
            state.type = 'empty';
        },
        setActive (state, value) {
            state.active = value;
        },
        setWidth (state, value) {
            state.width = value;
        },
        setHeight (state, value) {
            state.height = value;
        },
        setPositionLeft (state, value) {
            state.positionLeft = value;
        },
        setPositionTop (state, value) {
            state.positionTop = value;
        },
        setType (state, value) {
            state.type = value;
        },
        setWord (state, value) {
            state.word = value;
        },
        setPhrase (state, value) {
            state.phrase = value;
        },
        setPhraseReading (state, value) {
            state.phraseReading = value;
        },
        setReading (state, value) {
            state.reading = value;
        },
        setBaseWord (state, value) {
            state.baseWord = value;
        },
        setBaseWordReading (state, value) {
            state.baseWordReading = value;
        },
        setTranslationText (state, value) {
            state.translationText = value;
        },
        setStage (state, value) {
            state.stage = value;
        },
        setSearchField (state, value) {
            state.searchField = value;
        },
        setInflections (state, value) {
            state.inflections = value;
        },
        setVocabularyBottomSheetVisible(state, value) {
            state.vocabularyBottomSheetVisible = value;
        },
        setSidebarHidden(state, value) {
            state.sidebarHidden = value;
        },
        pushWordToPhrase (state, value) {
            state.phrase.push(value);
        },
        pushKanjiToList (state, value) {
            state.kanjiList.push(value);
        },
        appendSearchField (state, value) {
            state.searchField += value;
        },
        appendReading (state, value) {
            state.appendReading += value;
        },
    },
    getters: { }
}
