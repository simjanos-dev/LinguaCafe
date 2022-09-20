<template>
    <div id="reader-box">
        <div v-if="!finished" id="toolbar">
            <button :class="{'toolbar-button': true}" @click="backToChapters()" title="Back to chapters">
                <i class="fa fa-arrow-left"></i>
            </button>
            <button :class="{'toolbar-button': true, 'selected': sidebar == 'chapters'}" @click="toggleSidebar('chapters')" title="Chapter list">
                <i class="fas fa-book"></i>
            </button>
            <button :class="{'toolbar-button': true}" @click="settings.fontSize ++; unselectWord(); saveSettings();" title="Increase font size">
                <i class="fa fa-search-plus"></i>
            </button>
            <button :class="{'toolbar-button': true}" @click="settings.fontSize --; unselectWord(); saveSettings();" title="Decrease font size">
                <i class="fa fa-search-minus"></i>
            </button>
            <button :class="{'toolbar-button': true, 'selected': settings.plainTextMode}" @click="settings.plainTextMode = !settings.plainTextMode; unselectWord(); saveSettings();" title="Plain text mode">
                <i class="fa fa-highlighter"></i>
            </button>
            <button :class="{'toolbar-button': true, 'selected': settings.japaneseText}" @click="settings.japaneseText = !settings.japaneseText; unselectWord(); saveSettings();" title="Japanese text direction">
                <i class="fa fa-language"></i>
            </button>
            <button :class="{'toolbar-button': true, 'selected': !settings.highlightWords}"  @click="settings.highlightWords = !settings.highlightWords; saveSettings();" title="Highlight words">
                <i class="fa fa-underline"></i>
            </button>
            <button :class="{'toolbar-button': true, 'selected': sidebar == 'settings'}"  @click="toggleSidebar('settings')" title="Settings">
                <i class="fa fa-cog"></i>
            </button>
        </div>
        <div v-if="!finished" id="settings" :class="{'visible': sidebar == 'settings'}">
            <select v-model="settings.vocabBoxStyle" @change="saveSettings">
                <option value="sidebar">Sidebar</option>
                <option value="floating">Floating</option>
            </select>
        </div>
        <div v-if="!finished" id="chapters" :class="{'visible': sidebar == 'chapters'}">
            <div id="course-name">{{ _courseName }}</div>
            <div :class="{'chapter': true, 'selected': lesson.id == _lessonId}" 
                v-for="(lesson, index) in _lessons" :key="index">
                <a :href="'/lesson/' + lesson.id"><span :id="lesson.id == _lessonId ? 'selected-chapter' : ''">{{ index + '.) ' + lesson.name }}</span></a>
            </div>
        </div>
        <div v-if="!finished" id="reader" :class="{'plain-text-mode': settings.plainTextMode, 'japanese-text': settings.japaneseText, 'sidebar-opened': sidebar !== '', 'sidebar-vocab-box': settings.vocabBoxStyle == 'sidebar'}">
            <template v-for="(word, wordIndex) in words">
                <template v-if="word.word.indexOf('NEWLINE') == -1  && _language !== 'japanese'">
                    <template v-if="spaceFreeWords.includes(word.word)">
                        <div :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': settings.highlightWords, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                            @mousedown.stop="startSelection($event, wordIndex)" @mouseup.stop="finishSelection" @mousemove="updateSelection($event, wordIndex)" @mouseenter="hoverPhraseSelection(wordIndex)" @mouseleave="removePhraseHover()">{{ word.word }}</div>
                    </template><!--
                    --><template v-if="!spaceFreeWords.includes(word.word)"><!--
                        --><div :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': settings.highlightWords, 'plain-text-': true, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                            @mousedown.stop="startSelection($event, wordIndex)" @mouseup.stop="finishSelection" @mousemove="updateSelection($event, wordIndex)" @mouseenter="hoverPhraseSelection(wordIndex)" @mouseleave="removePhraseHover()">{{ word.word }}</div><!--
                    --></template>

                </template><!--
                --><div v-if="word.word.indexOf('NEWLINE') == -1 && _language == 'japanese'" :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': settings.highlightWords, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                    @mousedown.stop="startSelection($event, wordIndex)" @mouseup.stop="finishSelection" @mousemove="updateSelection($event, wordIndex)" @mouseenter="hoverPhraseSelection(wordIndex)" @mouseleave="removePhraseHover()">{{ word.word }}</div><!--
                    
                --><br v-if="word.word == 'NEWLINE'"><!--
            --></template>
            <br><br><br><br><br><br>
            <button id="finish-reading-button" class="btn btn-green" @click="finish()"><i class="fa fa-check"></i>&nbsp;&nbsp;Finish reading</button>
            <br><br><br><br>
        </div>
        <div id="floating-vocab-box" :class="{'bottom-arrow': vocabBoxPosition.arrow == 'bottom'}" :style="{'top': vocabBoxPosition.top + 'px', 'left': vocabBoxPosition.left + 'px', 'height': vocabBoxSize.height + 'px'}" v-if="selection.length && settings.vocabBoxStyle == 'floating' && !finished && !temporarySidebarVocabBox">
            <button id="show-sidebar-vocab-box-button" class="btn btn-primary edit-stage-button" @click="temporarySidebarVocabBox = true"><i class="fa fa-edit"></i></button>
            <div class="vocab-box-line" v-if="selection.length == 1">
                <div>{{uniqueWords[selection[0].uniqueWordIndex].word}}</div>
                <div>{{uniqueWords[selection[0].uniqueWordIndex].reading}}</div>
            </div>
            <div class="vocab-box-line" v-if="selection.length == 1 && uniqueWords[selection[0].uniqueWordIndex].base_word">
                <div>{{uniqueWords[selection[0].uniqueWordIndex].base_word}}</div>
                <div>{{uniqueWords[selection[0].uniqueWordIndex].base_word_reading}}</div>
            </div>
            <div class="vocab-box-translation" v-if="selection.length == 1">
                {{uniqueWords[selection[0].uniqueWordIndex].translation}}
            </div>
            <div class="vocab-box-line phrase" v-if="selection.length > 1">
                <template v-for="(word, index) in selection">{{ word.word }}</template>
            </div>
            <div class="vocab-box-translation" v-if="selectedPhrase !== -1">
                {{ phrases[selectedPhrase].translation }}
            </div>
        </div>
        <div id="phrase-box"  v-if="selection.length > 1 && (settings.vocabBoxStyle == 'sidebar' || temporarySidebarVocabBox) && !finished">
            <button id="close-vocab-box-button" class="btn edit-stage-button" @click="unselectWord();">x</button>
            <button id="save-phrase-button" class="btn btn-green" @click="saveNewPhrase" v-if="selectedPhrase == -1">Save new phrase</button>
            <button id="delete-phrase-button" class="btn btn-red" @click="deletePhrase" v-if="selectedPhrase !== -1">Delete phrase</button><br>

            <div class="phrase-box-title first">Phrase</div>
            <div class="phrase-box-line phrase">
                <template v-for="(word, index) in selection">
                    <span v-if="word.word !== 'NEWLINE'">{{ word.word }}</span>
                </template>
            </div>

            <div class="phrase-box-line">
                <div class="phrase-box-title">Reading</div>
                <textarea type="text" class="phrase-input" placeholder="Reading" v-model="phraseReading" @change="updatePhrase"></textarea>
            </div>
            
            <div class="phrase-box-line">
                <div class="phrase-box-title">Translation</div>
                <textarea type="text" class="phrase-input" placeholder="Translation" v-model="phraseTranslation" @change="updatePhrase"></textarea>
            </div>

            <div class="phrase-box-line" v-if="selectedPhrase !== -1">
                <button 
                @click="setStage(-7)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -7}"
                >7</button>
                <button 
                @click="setStage(-6)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -6}"
                >6</button>
                <button 
                @click="setStage(-5)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -5}"
                >5</button>
                <button 
                @click="setStage(-4)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -4}"
                >4</button>
                <button 
                @click="setStage(-3)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -3}"
                >3</button>
                <button 
                @click="setStage(-2)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -2}"
                >2</button>
                <button 
                @click="setStage(-1)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -1}"
                >1</button>
                <button 
                @click="setStage(0)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == 0}"><i class="fa fa-book"></i></button>
                <button 
                @click="setStage(1)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == 1}"><b>X</b></button>
            </div>

            <div id="phrase-search-box">
                <b>Search dictionary</b>
                <input type="text" v-model="vocabSearch" @change="makeJishoRequest">
                <div id="translations" :class="{'show-all': !allSearchResultsVisible && showAllSearchResults}">
                    <div class="vocab-translation" @click="addDefinitionToInput(definition.english.split('||').join(', '))"
                        v-for="(definition, definitionIndex) in searchResults" :key="definitionIndex">
                        <div class="target-language">{{ definition.japanese }}</div>
                        <div class="own-language" :title="definition.english.split('||').join(', ')">{{ definition.english.split('||').join(', ') }}</div>
                    </div>
                </div>
                <span id="show-all-translations" v-if="!allSearchResultsVisible && !showAllSearchResults" @click="showAllSearchResults = true">Show all</span>
            </div>
        </div>
        <div id="vocab-box" v-if="selection.length == 1 && (settings.vocabBoxStyle == 'sidebar' || temporarySidebarVocabBox) && !finished">
            <button id="close-vocab-box-button" class="btn edit-stage-button" @click="unselectWord();">x</button>
            <div class="vocab-box-line">
                Word:
                <div class="vocab-box-line-value">{{selection[0].wordIndex}}, {{uniqueWords[selection[0].uniqueWordIndex].word}} {{ words[selection[0].wordIndex].phraseIndexes}}</div>
                
            </div>
            <div id="vocab-read-count">
                {{ uniqueWords[selection[0].uniqueWordIndex].read_count }} <i class="fa fa-book"></i>
            </div>
            <div id="vocab-lookup-count">
                {{ uniqueWords[selection[0].uniqueWordIndex].lookup_count }} <i class="fa fa-eye"></i>
            </div>
            <div id="vocab-last-level-up" v-if="uniqueWords[selection[0].uniqueWordIndex].last_level_up">
                {{ uniqueWords[selection[0].uniqueWordIndex].last_level_up }} <i class="fa fa-eye"></i>
            </div>
            <div class="vocab-box-line">
                Reading:
                <input type="text" v-model="uniqueWords[selection[0].uniqueWordIndex].reading" @change="updateNewWord">
            </div>
            <div class="vocab-box-line">
                Base word:
                <input type="text" v-model="uniqueWords[selection[0].uniqueWordIndex].base_word" @change="updateNewWord">
            </div>
            <div class="vocab-box-line">
                Base reading:
                <input type="text" v-model="uniqueWords[selection[0].uniqueWordIndex].base_word_reading" @change="updateNewWord">
            </div>
            <div class="vocab-box-line translation">
                Translation:
                <textarea id="translation" type="text" placeholder="Translation" v-model="uniqueWords[selection[0].uniqueWordIndex].translation" @change="updateNewWord"></textarea>
            </div>

            <div class="vocab-box-line">
                <button 
                @click="setStage(-7)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -7}"
                >7</button>
                <button 
                @click="setStage(-6)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -6}"
                >6</button>
                <button 
                @click="setStage(-5)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -5}"
                >5</button>
                <button 
                @click="setStage(-4)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -4}"
                >4</button>
                <button 
                @click="setStage(-3)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -3}"
                >3</button>
                <button 
                @click="setStage(-2)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -2}"
                >2</button>
                <button 
                @click="setStage(-1)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == -1}"
                >1</button>
                <button 
                @click="setStage(0)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == 0}"><i class="fa fa-book"></i></button>
                <button 
                @click="setStage(1)"
                :class="{'btn': true, 'btn-primary': true, 'edit-stage-button': true, 
                    'selected': selectionStage == 1}"><b>X</b></button>
            </div>

            <div id="search-box">
                <b>Search dictionary</b>
                <input type="text" v-model="vocabSearch" @change="makeJishoRequest">
                <div id="translations" :class="{'show-all': !allSearchResultsVisible && showAllSearchResults}">
                    <div class="vocab-translation" @click="addDefinitionToInput(definition.english.split('||').join(', '))"
                        v-for="(definition, definitionIndex) in searchResults" :key="definitionIndex">
                        <div class="target-language">{{ definition.japanese }}</div>
                        <div class="own-language" :title="definition.english.split('||').join(', ')">{{ definition.english.split('||').join(', ') }}</div>
                    </div>
                </div>
                <span id="show-all-translations" v-if="!allSearchResultsVisible && !showAllSearchResults" @click="showAllSearchResults = true">Show all</span>
            </div>
        </div>
        <div v-if="finished" id="finished-box">
            <div id="lesson-finished-text">Congratulations! You have finished {{ _lessonName }}!</div>

            <table id="finished-stats" class="table-sm table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Word type</th>
                        <th scope="col">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Read words:</td>
                        <td> {{ _wordCount }} </td>
                    </tr>
                    <tr>
                        <td>Newly saved words:</td>
                        <td> {{ newlySavedWords }} </td>
                    </tr>
                    <tr>
                        <td>Learned words:</td>
                        <td> {{ learnedWords }} </td>
                    </tr>
                    <tr>
                        <td>Progressed words:</td>
                        <td> {{ progressedWords }} </td>
                    </tr>
                </tbody>
            </table>
            <a :href="'/lessons/' + _courseId + ''"><button id="go-to-lessons-button" class="btn btn-primary">Go to lessons</button></a>
            <a :href="'/lesson/' + nextLesson + ''"><button id="go-to-lessons-button" class="btn btn-primary" v-if="nextLesson !== -1">Go to next lesson</button></a>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                settings: {
                    vocabBoxStyle: 'sidebar',
                    highlightWords: true,
                    plainTextMode: false,
                    japaneseText: false,
                    fontSize: 20,
                },
                temporarySidebarVocabBox: false,
                sidebar: '',
                spaceFreeWords: ['.', ',', ':', '?', '!', '-', '*', ' ', '\r\n', '\r\n '],
                finished: false,
                newlySavedWords: 0,
                learnedWords: 0,
                progressedWords: 0,
                words: JSON.parse(this._text),
                uniqueWords: JSON.parse(this._uniqueWords),
                phrases: JSON.parse(this._phrases),
                deletedPhrases: [],
                selection: [],
                selectedPhrase: -1,
                selectionStage: null,
                ongoingSelection: [],
                ongoingSelectionStartingWord: {
                    wordIndex: -1,
                },
                vocabBoxPosition: {
                    left: 0,
                    top: 0,
                    arrow: 'top',
                },
                vocabBoxSize: {
                    width: 320,
                    height: 130
                },
                searchResults: [],
                allSearchResultsVisible: false,
                showAllSearchResults: false,
                vocabSearch: '',
                phraseTranslation: '',
                phraseReading: '',
                nextLesson: -1,
            }
        },
        props: {
            _courseName: String,
            _lessonId: String,
            _wordCount: Number,
            _lessonName: String,
            _text: String,
            _uniqueWords: String,
            _courseId: String,
            _language: String,
            _lessons: Array,
            _phrases: String,
        },
        mounted() {
            for (let i = 0; i < this.$props._lessons.length; i++) {
                if (this.$props._lessons[i].id == this.$props._lessonId && i < this.$props._lessons.length - 1 && this.$props._lessons[i + 1].read_count) {
                    this.nextLesson = this.$props._lessons[i + 1].id;
                    break;
                }
            }

            this.settings.vocabBoxStyle = this.$cookie.get('vocab-box-style');
            this.settings.highlightWords =  this.$cookie.get('highlight-words') == 'true';
            this.settings.plainTextMode =  this.$cookie.get('plain-text-mode') == 'true';
            this.settings.japaneseText =  this.$cookie.get('japanese-text') == 'true';
            this.settings.fontSize =  parseInt(this.$cookie.get('font-size'));

            if (this.settings.vocabBoxStyle == null) {
                this.settings.vocabBoxStyle = 'sidebar';
                this.settings.highlightWords =  true;
                this.settings.plainTextMode =  false;
                this.settings.japaneseText =  false;
                this.settings.fontSize =  20;
            }

            this.saveSettings();
            document.getElementById('reader').addEventListener('scroll', this.scrollEvent);
            document.getElementById('reader').addEventListener('mouseup', this.finishSelection);
            this.$forceUpdate();
            this.updatePhraseBorders();
        },
        methods: {
            backToChapters: function() {
                if (window.confirm('Are you sure you want to go back to chapters?')) {
                    window.location.href = '/lessons/' + this._courseId;
                }
            },
            saveSettings: function() {
                this.$cookie.set('vocab-box-style', this.settings.vocabBoxStyle, 3650);
                this.$cookie.set('highlight-words', this.settings.highlightWords, 3650);
                this.$cookie.set('plain-text-mode', this.settings.plainTextMode, 3650);
                this.$cookie.set('japanese-text', this.settings.japaneseText, 3650);
                this.$cookie.set('font-size', this.settings.fontSize, 3650);
            },
            scrollEvent: function() {
                this.unselectWord();
            },
            toggleSidebar: function(newSidebar) {
                if (this.sidebar == newSidebar) {
                    this.sidebar = '';
                    return;
                }
                
                this.sidebar = newSidebar;
                if (newSidebar == 'chapters') {
                    setTimeout(() => {
                        document.getElementById('selected-chapter').scrollIntoView();
                    }, 305);
                }
            },
            getUniqueWordIndex: function(word) {
                for (var i = 0; i < this.uniqueWords.length; i++) {
                    if (this.uniqueWords[i].word == word) {
                        return i;
                    }
                }

                return -1;
            },
            updateNewWord: function() {
                if (this.selection.length == 1 && this.uniqueWords[this.selection[0].uniqueWordIndex].stage == 2) {
                    this.setStage(-5);
                }
            },
            startSelection: function(event, wordIndex) {
                if (this.ongoingSelection.length == 1 && this.ongoingSelection[0].wordIndex == wordIndex) {
                    this.unselectWord();
                    return;
                }

                if (this.settings.plainTextMode) {
                    this.unselectWord();
                    return;
                }

                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                }
                
                // set selected word          
                var selectedWord = {
                    word: event.srcElement.outerText,
                    wordIndex: wordIndex,
                    uniqueWordIndex: this.getUniqueWordIndex(event.srcElement.outerText.toLowerCase()),
                    reading: this.uniqueWords[this.getUniqueWordIndex(this.words[wordIndex].word.toLowerCase())].reading,
                    sentenceIndex: this.words[wordIndex].sentenceIndex,
                };
                
                this.ongoingSelection = [selectedWord];
                this.words[wordIndex].selected = true;
                this.ongoingSelectionStartingWord.wordIndex = wordIndex;
                this.updateSelectedWordLookupCount(selectedWord.word, selectedWord.uniqueWordIndex);
                this.updateVocabBoxPosition(event.target.getBoundingClientRect());
            },
            updateSelection: function(event, wordIndex) {
                if (!this.ongoingSelection.length || event.buttons !== 1) {
                    return;
                }

                var firstWordIndex = this.ongoingSelectionStartingWord.wordIndex;;
                var lastWordIndex = wordIndex;
                
                if (firstWordIndex > lastWordIndex) {
                    var firstWordIndex = wordIndex;
                    var lastWordIndex = this.ongoingSelectionStartingWord.wordIndex;
                }

                this.ongoingSelection = [];
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;

                    if (i < firstWordIndex || i > lastWordIndex) {
                        continue;
                    }

                    this.words[i].selected = true;
                    var selectedWord = {
                        word: this.words[i].word,
                        wordIndex: i,
                        uniqueWordIndex: this.getUniqueWordIndex(this.words[i].word.toLowerCase()),
                        reading: this.uniqueWords[this.getUniqueWordIndex(this.words[i].word.toLowerCase())].reading,
                        sentenceIndex: this.words[i].sentenceIndex,
                    };

                    this.ongoingSelection.push(selectedWord);
                }
            },
            finishSelection: function() {
                if (this.ongoingSelection.length == 1) {
                    // if the selected word is in an expression, select the expression instead
                    var selectedPhrase = this.getSelectedPhraseIndex();
                    var newWordSelected = this.selection.find(o => o.wordIndex == this.ongoingSelection[0].wordIndex) !== undefined;
                    var phraseIndexes = this.words[this.ongoingSelection[0].wordIndex].phraseIndexes;
                    if (phraseIndexes.length && selectedPhrase !== phraseIndexes[phraseIndexes.length - 1]) {
                        if (selectedPhrase == -1 || !newWordSelected) {
                            this.selectPhraseInstanceByWord(this.ongoingSelection[0].wordIndex, phraseIndexes[0]);
                        } else {
                            for (let i = 0; i < phraseIndexes.length; i++) {
                                if (phraseIndexes[i] == selectedPhrase && i < phraseIndexes.length - 1) {
                                    this.selectPhraseInstanceByWord(this.ongoingSelection[0].wordIndex, phraseIndexes[i + 1]);
                                    break;
                                }
                            }
                        }
                    }

                    if (this.ongoingSelection.length == 1) {
                        this.vocabSearch = this.ongoingSelection[0].word;
                    }
                }

                if (this.ongoingSelection.length > 1) {
                    this.phraseReading = '';
                    this.vocabSearch = '';
                    for (let i = 0; i < this.ongoingSelection.length; i++) {
                        if (this.ongoingSelection.[i].word.toLowerCase() == 'newline') {
                            continue;
                        }

                        this.vocabSearch += this.ongoingSelection.[i].word;
                        this.phraseReading += this.ongoingSelection.[i].reading;
                    }
                }

                // update selected word classes after automatic phrase selection
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                }

                for (let i = 0; i < this.ongoingSelection.length; i++) {
                    this.words[this.ongoingSelection[i].wordIndex].selected = true;
                }

                this.selection = this.ongoingSelection;
                this.selectedPhrase = this.getSelectedPhraseIndex();

                this.phraseTranslation = this.selectedPhrase !== -1 ? this.phrases[this.selectedPhrase].translation : '';
                this.phraseReading = this.selectedPhrase !== -1 ? this.phrases[this.selectedPhrase].reading : this.phraseReading;
                this.ongoingSelection = [];
                this.updateSelectedWordStage();
                this.updateExampleSentence();
                
                // if the user checks the meaning of a word or phrase, it must not level up
                if (this.getSelectedPhraseIndex() !== -1) {
                    this.phrases[this.getSelectedPhraseIndex()].checked = true;
                } else if (this.selection.length == 1) {
                    this.uniqueWords[this.selection[0].uniqueWordIndex].checked = true;
                }

                this.makeJishoRequest();
            },
            removePhraseHover: function() {
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].hover = false;
                }
            },
            updateSelectedWordStage: function() {
                if (this.selectedPhrase == -1 && this.selection.length) {
                    this.selectionStage = this.uniqueWords[this.selection[0].uniqueWordIndex].stage;
                } else if (this.selectedPhrase !== -1){
                    this.selectionStage = this.phrases[this.selectedPhrase].stage;
                }
            },
            updateExampleSentence: function() {
                if (this.selection.length == 1) {
                    var exampleSentence = [];

                    for (var i = 0; i < this.words.length; i++) {
                        if (this.words[i].word == 'NEWLINE' || this.words[i].sentenceIndex !== this.selection[0].sentenceIndex) {
                            continue;
                        }

                        exampleSentence.push(this.words[i].word);
                    }

                    this.uniqueWords[this.selection[0].uniqueWordIndex].example_sentence = JSON.stringify(exampleSentence);
                }

            },
            updateVocabBoxPosition: function(positions) {
                var readerDom = document.getElementById('reader');
                this.vocabBoxPosition.left = positions.right - this.vocabBoxSize.width / 2 - (positions.right - positions.left) / 2;

                if (this.vocabBoxPosition.left < 30) {
                    this.vocabBoxPosition.left = 30;
                } else if (this.vocabBoxPosition.left > window.innerWidth - this.vocabBoxSize.width - 30) {
                    this.vocabBoxPosition.left = window.innerWidth - this.vocabBoxSize.width - 30;
                }

                if (positions.top > this.vocabBoxSize.height + 12) {
                    this.vocabBoxPosition.top = positions.top - this.vocabBoxSize.height - 12;
                    this.vocabBoxPosition.arrow = 'bottom';
                } else {
                    this.vocabBoxPosition.top = positions.bottom + 12;
                    this.vocabBoxPosition.arrow = 'top';
                }
            },
            updateSelectedWordLookupCount: function(word, uniqueWordIndex) {
                this.uniqueWords[uniqueWordIndex].lookup_count ++;

                for (var i  = 0; i < this.words.length; i++) {
                    if (this.words[i].word.toLowerCase() == word) {
                        this.words[i].lookup_count = this.uniqueWords[uniqueWordIndex].lookup_count;
                    }
                }
            },
            makeJishoRequest: function() {
                this.searchResults = [];
                axios.get('/jisho-request/' + this.vocabSearch)
                .then(function (response) {
                    this.processJishoRequest(response.data.data);
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                })
                .then(function () {
                });
            },
            processJishoRequest: function(data) {
                for (var i = 0; i < data.length; i++) {
                    for (var j = 0; j < data[i].senses.length; j++) {
                        this.searchResults.push({
                            english: data[i].senses[j].english_definitions.join('||'),
                            japanese: data[i].slug
                        });
                    }
                }

                this.$nextTick(() => {
                    var element = document.getElementById('translations');
                    if (element == null) {
                        return;
                    }

                    this.allSearchResultsVisible = element.scrollHeight <= element.clientHeight;
                    this.showAllSearchResults = false;
                });
            },
            unselectWord() {
                this.selectedPhrase = -1;
                this.temporarySidebarVocabBox = false;
                this.selection = [];
                
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                    this.words[i].hover = false;
                }
            },
            addDefinitionToInput: function(definition) {
                if (this.selection.length == 1) {
                    if (this.uniqueWords[this.selection[0].uniqueWordIndex].translation.length) {
                        this.uniqueWords[this.selection[0].uniqueWordIndex].translation += ', ';
                    }

                    this.uniqueWords[this.selection[0].uniqueWordIndex].translation += definition;
                } else {
                    if (this.phraseTranslation.length) {
                        this.phraseTranslation += ', ';
                    }

                    this.phraseTranslation += definition;
                    this.updatePhrase();
                }
            },
            setStage: function(stage) {
                if (this.selectedPhrase == -1 && this.selection.length == 1) {
                    this.uniqueWords[this.selection[0].uniqueWordIndex].stage = stage;
                    if (stage == 0) {
                        this.learnedWords ++;
                    }

                    // set all the required words' stages in the text
                    for (var i  = 0; i < this.words.length; i++) {
                        if (this.words[i].word.toLowerCase() == this.selection[0].word.toLowerCase()) {
                            this.words[i].stage = stage;
                        }
                    }
                } else if (this.selectedPhrase !== -1) {
                    this.phrases[this.selectedPhrase].stage = stage;
                    this.updatePhraseBorders();
                }

                this.updateSelectedWordStage();
            },
            deletePhrase: function() {
                if (this.selectedPhrase == -1) {
                    return;
                }

                var phraseText = this.phrases[this.selectedPhrase].words.join('');
                for (var i  = 0; i < this.words.length; i++) {
                    var index = this.words[i].phraseIndexes.indexOf(this.selectedPhrase);
                    if (index !== -1) {
                        this.words[i].phraseIndexes.splice(index, 1);
                    }

                    for (let p = 0; p < this.words[i].phraseIndexes.length; p++) {
                        if (this.words[i].phraseIndexes[p] > this.selectedPhrase) {
                            this.words[i].phraseIndexes[p] -= 1;
                        }
                    }

                    this.words[i].selected = false;
                    this.words[i].hover = false;
                }

                var deletedPhrase = this.phrases.splice(this.selectedPhrase, 1)[0];
                if (deletedPhrase.id !== -1) {
                    this.deletedPhrases.push(deletedPhrase);
                }

                this.selectedPhrase = -1;
                this.selection = [];
                this.updatePhraseBorders();
            },
            updatePhrase: function() {
                // save phrase if already exists
                var selectedPhrase = this.getSelectedPhraseIndex();
                if (selectedPhrase !== -1) {
                    this.phrases[selectedPhrase].reading = this.phraseReading;
                    this.phrases[selectedPhrase].translation = this.phraseTranslation;
                }
            },
            saveNewPhrase: function() {
                // create phrase object
                var phrase = {
                    id: -1,
                    stage: -5,
                    words: [],
                    checked: true,
                    last_level_up: '',
                    reading: this.phraseReading,
                    translation: this.phraseTranslation,
                };

                for (var i = 0; i < this.selection.length; i++) {
                    if (this.selection[i].word.toLowerCase() == 'newline') {
                        continue;
                    }
                    
                    phrase.words.push(this.selection[i].word.toLowerCase());
                }

                // find all instance of the new phrase in the text
                var phraseOccurences = [];
                for (var i = 0; i < this.words.length; i++) {
                    // check if the current word is the start of the phrase
                    if (this.words[i].word.toLowerCase() == phrase.words[0]) {
                        phraseOccurences.push([
                            {
                                word: this.words[i].word.toLowerCase(),
                                wordIndex: i,
                                newLineCount: 0
                            }
                        ]);
                    }

                    // check if the current word is the continuation of a phrase
                    for (let p = 0 ; p < phraseOccurences.length; p++) {
                        if (phraseOccurences[p].length == phrase.words.length) {
                            continue;
                        }

                        if (phrase.words[phraseOccurences[p].length] == this.words[i].word.toLowerCase() &&
                            (i - 1) == phraseOccurences[p][phraseOccurences[p].length - 1].wordIndex + phraseOccurences[p][phraseOccurences[p].length - 1].newLineCount) {
                            phraseOccurences[p].push({
                                word: this.words[i].word.toLowerCase(),
                                wordIndex: i,
                                newLineCount: 0
                            });
                        }

                        // count 'NEWLINE' words for comparison
                        if (this.words[i].word.toLowerCase() == 'newline') {
                            phraseOccurences[p][phraseOccurences[p].length - 1].newLineCount ++;
                        }
                    }

                }

                // mark all instance of the new phrase in the text
                for (let p = 0 ; p < phraseOccurences.length; p++) {
                    if (phraseOccurences[p].length < phrase.words.length) {
                        continue;
                    }

                    for (let i = 0; i < phraseOccurences[p].length; i++) {
                        this.words[phraseOccurences[p][i].wordIndex].phraseIndexes.push(this.phrases.length);
                    }
                }

                // remove the new phrase from the deleted phrases
                for (let i = 0; i < this.deletedPhrases.length; i++) {
                    var currentPhrase = this.deletedPhrases[i].words.join();
                    if (this.deletedPhrases[i].words.join() == phrase.words.join()) {
                        phrase.id = this.deletedPhrases[i].id;
                        this.deletedPhrases.splice(i, 1);
                        break;
                    }
                }

                this.phrases.push(phrase);
                this.updatePhraseBorders();
                this.selectedPhrase = this.getSelectedPhraseIndex();

                this.updateSelectedWordStage();
            },
            updatePhraseBorders: function() {
                for (var i = 0; i < this.words.length; i++) {
                    if (this.words[i].phraseIndexes.length) {
                        var lowestPhraseStage = 1000;
                        for (let p = 0; p < this.words[i].phraseIndexes.length; p++) {
                            if (parseInt(this.phrases[this.words[i].phraseIndexes[p]].stage) < lowestPhraseStage) {
                                lowestPhraseStage = parseInt(this.phrases[this.words[i].phraseIndexes[p]].stage);
                            }
                        }

                        this.words[i].phraseStage = lowestPhraseStage;
                    }
                    if (this.words[i].phraseIndexes.length && (i == 0 || !this.words[i - 1].phraseIndexes.length)) {
                        this.words[i].phraseStart = true;
                    } else {
                        this.words[i].phraseStart = false;
                    }

                    if (this.words[i].phraseIndexes.length && (i + 1 == this.words.length || !this.words[i + 1].phraseIndexes.length)) {
                        this.words[i].phraseEnd = true;
                    } else {
                        this.words[i].phraseEnd = false;
                    }
                }
            },
            selectPhraseInstanceByWord: function(wordIndex, phraseIndex) {
                var currentWordIndex = wordIndex;
                var newSelection = [];

                // find the first word of the phrase
                while (currentWordIndex > 0 && (this.words[currentWordIndex - 1].word == 'NEWLINE' || this.words[currentWordIndex - 1].phraseIndexes.includes(phraseIndex))) {
                    currentWordIndex --;
                }

                // select the phrase
                do {
                    if (this.words[currentWordIndex].word !== 'NEWLINE') {
                        newSelection.push({
                            word: this.words[currentWordIndex].word,
                            reading: this.uniqueWords[this.getUniqueWordIndex(this.words[currentWordIndex].word.toLowerCase())].reading,
                            sentenceIndex: this.words[currentWordIndex].sentenceIndex,
                            wordIndex: currentWordIndex,
                            uniqueWordIndex: this.getUniqueWordIndex(this.words[currentWordIndex].word.toLowerCase()),
                        });

                    }

                    currentWordIndex ++;
                } while(currentWordIndex < this.words.length && (this.words[currentWordIndex].word == 'NEWLINE' || this.words[currentWordIndex].phraseIndexes.includes(phraseIndex)));
                
                this.ongoingSelection = newSelection;
            },
            hoverPhraseSelection: function(wordIndex) {
                this.removePhraseHover();
                var phraseIndexes = this.words[wordIndex].phraseIndexes;

                // find the first word of the phrase
                var currentWordIndex = wordIndex;
                while (currentWordIndex > 0 && (this.words[currentWordIndex - 1].word == 'NEWLINE' || this.words[currentWordIndex - 1].phraseIndexes.some(el => phraseIndexes.includes(el)))) {
                    currentWordIndex--;
                }

                // highlight the phrase
                do {
                    this.words[currentWordIndex].hover = true;
                    currentWordIndex ++;
                } while(currentWordIndex < this.words.length && (this.words[currentWordIndex].word == 'NEWLINE' || this.words[currentWordIndex].phraseIndexes.some(el => phraseIndexes.includes(el))));
            },
            getSelectedPhraseIndex: function() {
                var phraseIndex = -1;
                var selectedText = this.selection.map(a => a.word.toLowerCase()).join('');
                
                while (selectedText.indexOf('newline') !== -1) {
                    selectedText = selectedText.replace('newline', '');
                }
                

                for (let i = 0; i < this.phrases.length; i++) {
                    if (selectedText == this.phrases[i].words.join('')) {
                        phraseIndex = i;
                        break;
                    }
                }

                return phraseIndex;
            },
            finish: function() {
                axios.post('/finish-lesson', {
                    uniqueWords: JSON.stringify(this.uniqueWords),
                    phrases: JSON.stringify(this.phrases),
                    deletedPhrases: JSON.stringify(this.deletedPhrases),
                    sentences: JSON.stringify(this.sentences),
                    language: this.$props._language,
                    lessonId: this.$props._lessonId
                })
                .then(function (response) {
                    // count progressed and learned words
                    for (var i  = 0; i < this.uniqueWords.length; i++) {
                        if (!this.uniqueWords[i].checked && this.uniqueWords[i].stage < -1) {
                            this.progressedWords ++;
                        }

                        if (!this.uniqueWords[i].checked && this.uniqueWords[i].stage == -1) {
                            this.learnedWords ++;
                        }
                    }

                    this.finished = true;
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                })
                .then(function () {
                });
            }
        }
    }
</script>
