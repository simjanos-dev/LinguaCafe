<template>
    <div id="reader-box" :style="{'max-width': settings.maximumTextWidth}">
        <div v-if="!finished" id="toolbar">
            <button :class="{'toolbar-button': true}" @click="backToChapters()" title="Back to chapters">
                <i class="fa fa-arrow-left"></i>
            </button>
            <button :class="{'toolbar-button': true, 'selected': toolbar == 'chapters'}" @click="toggleToolbar('chapters')" title="Chapter list">
                <i class="fas fa-book"></i>
            </button>
            <button :class="{'toolbar-button': true, 'selected': toolbar == 'glossary'}" @click="toggleToolbar('glossary')" title="Glossary">
                <i class="fas fa-list"></i>
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
            <button :class="{'toolbar-button': true, 'selected': settings.highlightWords}"  @click="settings.highlightWords = !settings.highlightWords; saveSettings();" title="Highlight words">
                <i class="fa fa-underline"></i>
            </button>
            <button :class="{'toolbar-button': true, 'selected': toolbar == 'settings'}"  @click="toggleToolbar('settings')" title="Settings">
                <i class="fa fa-cog"></i>
            </button>
        </div>

        <!-- Settings -->
        <div id="settings" :class="{'visible': toolbar == 'settings'}">
            <!-- Highlight words -->
            <div class="setting">
                <div class="setting-label">Highlight words:</div>
                <div class="setting-input switch">
                    <label class="switch">
                        <input type="checkbox" v-model="settings.highlightWords" @change="saveSettings">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- Maximum text width -->
            <div class="setting">
                <div class="setting-label">Maximum text width:</div>
                <div class="setting-input slider">
                    <vue-slider 
                        :data="{
                            '800px': '800px',
                            '900px': '900px',
                            '1000px': '1000px',
                            '1100px': '1100px',
                            '1200px': '1200px',
                            '1300px': '1300px',
                            '1400px': '1400px',
                            '1500px': '1500px',
                            '100%': '100%'
                        }" 
                        :marks="{'800px': 'Small', '100%': 'Full'}" 
                        :drag-on-click="true"
                        :lazy="true"
                        :contained="true"
                        v-model="settings.maximumTextWidth"
                        @change="saveSettings"
                    />
                </div>
            </div>

            <!-- Font size -->
            <div class="setting">
                <div class="setting-label">Font size:</div>
                <div class="setting-input slider">
                    <vue-slider 
                        :min="15"
                        :max="25"
                        :interval="1"
                        :marks="[15, 20, 25]" 
                        :drag-on-click="true"
                        :lazy="true"
                        :contained="true"
                        v-model="settings.fontSize"
                        @change="saveSettings"
                    />
                </div>
            </div>

            <!-- Japanese vertical text -->
            <div class="setting">
                <div class="setting-label">Japanese vertical text:</div>
                <div class="setting-input switch">
                    <label class="switch">
                        <input type="checkbox" v-model="settings.japaneseText" @change="saveSettings">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- Display phrase reading -->
            <div class="setting">
                <div class="setting-label">Display phrase reading:</div>
                <div class="setting-input switch">
                    <label class="switch">
                        <input type="checkbox" v-model="settings.displayPhraseReading" @change="saveSettings">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- Display glossary reading -->
            <div class="setting">
                <div class="setting-label">Display glossary reading:</div>
                <div class="setting-input switch">
                    <label class="switch">
                        <input type="checkbox" v-model="settings.displayGlossaryReadings" @change="saveSettings">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- Auto move words to known -->
            <div class="setting">
                <div class="setting-label">Auto move words to known:</div>
                <div class="setting-input switch">
                    <label class="switch">
                        <input type="checkbox" v-model="settings.autoMoveWordsToKnown" @change="saveSettings">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <div v-if="!finished" id="chapters" :class="{'visible': toolbar == 'chapters'}">
            <div id="course-name">{{ _courseName }}</div>
            <div :class="{'chapter': true, 'selected': lesson.id == _lessonId}" 
                v-for="(lesson, index) in _lessons" :key="index">
                <a :href="'/lesson/' + lesson.id"><span :id="lesson.id == _lessonId ? 'selected-chapter' : ''">{{ index + '.) ' + lesson.name }}</span></a>
            </div>
        </div>
        <div v-if="!finished" id="glossary" :class="{'visible': toolbar == 'glossary'}">
            <template v-for="(word, index) in glossary">
                <div class="glossary-entry" :key="index">
                    <div class="glossary-title">
                        <!-- Glossary entry stage -->
                            <div class="stage">
                                <div class="stage-text">{{ word.stage }}</div>
                                <vue-circle
                                    :progress="((7 - word.stage) / 7 * 100)"
                                    :size="36"
                                    :thickness="5"
                                    empty-fill="rgb(230, 230, 230)"
                                    :fill="{color: $cookie.get('ebook-reader-mode') == null ? 'rgba(52, 232, 127, 1)' : 'black'}"
                                    :ref="'circle' + index"
                                    :start-angle="4.71239"
                                    :show-percent="false">
                                </vue-circle>
                            </div>
                        <!-- Glossary entry word or phrase -->
                        <template>
                            <div :class="{'word': true, 'reading': settings.displayGlossaryReadings}" @click="settings.displayGlossaryReadings = !settings.displayGlossaryReadings" v-if="word.base_word == ''">
                                <ruby>{{ word.word }}<rt v-if="settings.displayGlossaryReadings">{{ word.reading }}</rt></ruby>
                            </div>
                            <div :class="{'word': true, 'reading': settings.displayGlossaryReadings}" @click="settings.displayGlossaryReadings = !settings.displayGlossaryReadings" v-if="word.base_word !== ''">
                                <ruby>{{ word.base_word }}<rt v-if="word.showReading">{{ word.base_word_reading }}</rt></ruby> 
                                <i class="fas fa-long-arrow-alt-right"></i> 
                                <ruby>{{ word.word }}<rt v-if="word.showReading">{{ word.reading }}</rt></ruby> 
                            </div>
                        </template>
                    </div>

                    <!-- Glossary entry translation-->
                    <div class="translation" v-if="word.translation.length">
                        <ul>
                            <li v-for="(translation, index) in word.translation.split(';')">{{ translation }}</li>
                        </ul>
                    </div>
                </div>
            </template>
        </div>

        <!-- Vocab box -->
        <div id="vocab-box" :class="{'editing': vocabEditMode == 'word' || vocabEditMode == 'phrase', 'new-phrase': selection.length > 1 && selectedPhrase == -1, 'translation-edit': vocabEditMode == 'translation'}" :style="{'top': vocabBoxPosition.top + 'px', 'left': vocabBoxPosition.left + 'px'}" v-if="selection.length && !finished && !selectionOngoing " @mouseup.stop=";">
            <!-- Word -->
            <div class="vocab-word-box" v-if="selection.length == 1 && (vocabEditMode == '' || vocabEditMode == 'word')">
                <span class="title" v-if="vocabEditMode == ''">Word</span>
                <span class="title" v-if="vocabEditMode == 'word'">Word editing</span>
                <template v-if="vocabEditMode == ''">
                    <span class="vocab-edit" @click="vocabEditMode = 'word'; updateVocabBoxPosition();"><i class="fa fa-pen"></i> Edit</span>
                </template>
                
                <!-- With base word-->
                <div class="vocab-word"  v-if="uniqueWords[selection[0].uniqueWordIndex].base_word !== ''">
                    <ruby>{{uniqueWords[selection[0].uniqueWordIndex].base_word}}<rt>{{uniqueWords[selection[0].uniqueWordIndex].base_word_reading}}</rt></ruby>
                    <i class="fas fa-long-arrow-alt-right"></i>
                    <ruby>{{uniqueWords[selection[0].uniqueWordIndex].word}}<rt>{{uniqueWords[selection[0].uniqueWordIndex].reading}}</rt></ruby>
                </div>
                
                <!-- No base word-->
                <div class="vocab-word" v-if="uniqueWords[selection[0].uniqueWordIndex].base_word == ''">
                    <ruby>{{uniqueWords[selection[0].uniqueWordIndex].word}}<rt>{{uniqueWords[selection[0].uniqueWordIndex].reading}}</rt></ruby>
                </div>

                <!-- Vocab edit fields-->
                <div id="vocab-word-edit" v-if="vocabEditMode == 'word'">
                    <div class="vocab-word-edit-line">
                        <div class="vocab-word-edit-cell">Form</div>
                        <div class="vocab-word-edit-cell">Word</div>
                        <div class="vocab-word-edit-cell">Reading</div>
                    </div>
                    <div class="vocab-word-edit-line">
                        <div class="vocab-word-edit-cell">Selected</div>
                        <div class="vocab-word-edit-cell">
                            {{uniqueWords[selection[0].uniqueWordIndex].word}}
                        </div>
                        <div class="vocab-word-edit-cell"><input type="text" v-model="uniqueWords[selection[0].uniqueWordIndex].reading" @change="updateNewWord"></div>
                    </div>
                    <div class="vocab-word-edit-line">
                        <div class="vocab-word-edit-cell">Base</div>
                        <div class="vocab-word-edit-cell"><input type="text" v-model="uniqueWords[selection[0].uniqueWordIndex].base_word" @change="updateNewWord"></div>
                        <div class="vocab-word-edit-cell"><input type="text" v-model="uniqueWords[selection[0].uniqueWordIndex].base_word_reading" @change="updateNewWord"></div>
                    </div>
                    <div class="vocab-word-edit-line button">
                        <button class="btn btn-black small vocab-box-button" @click="vocabEditMode = ''">Close</button>
                    </div>
                </div>
            </div>

            <!-- Phrase -->
            <div class="vocab-phrase-box" v-if="selection.length > 1 && vocabEditMode !== 'translation'">
                <!-- Phrase text -->
                <span class="title">Phrase</span>
                <span class="vocab-edit" @click="vocabEditMode = 'phrase'; updateVocabBoxPosition();" v-if="vocabEditMode == '' && selectedPhrase !== -1"><i class="fa fa-pen"></i> Edit</span>
                <span class="show-phrase-reading" @click="settings.displayPhraseReading = !settings.displayPhraseReading; updateVocabBoxPosition();" v-if="selection.length > 1 && vocabEditMode == ''">
                    <template v-if="!settings.displayPhraseReading"><i class="fa fa-eye"></i> Show reading</template>
                    <template v-if="settings.displayPhraseReading"><i class="fa fa-eye-slash"></i> Hide reading</template>
                </span>
                <div class="vocab-phrase">
                    <template v-for="(word, index) in selection" v-if="word.word !== 'NEWLINE'">{{ word.word }}</template>
                </div>

                <!-- Phrase reading -->
                <template v-if="vocabEditMode == '' && settings.displayPhraseReading">
                    <span class="title">Reading</span>
                    <div class="vocab-phrase-reading">
                        {{ phraseReading }}
                    </div>
                </template>

                <!-- Phrase reading editing-->
                <span class="title" v-if="vocabEditMode == 'phrase'">Phrase reading</span>
                <textarea type="text" class="phrase-reading" v-model="phraseReading" @change="updatePhrase" v-if="vocabEditMode == 'phrase'"></textarea>

                
                <button class="btn btn-green small vocab-box-button" @click="saveNewPhrase" v-if="selectedPhrase == -1">Save new phrase</button>
                <button class="btn btn-black small vocab-box-button" @click="vocabEditMode = ''" v-if="vocabEditMode == 'phrase'">Close</button>
                <button id="delete-phrase-button" class="btn btn-red small vocab-box-button" @click="deletePhrase" v-if="vocabEditMode == 'phrase'">Delete phrase</button>
            </div>

            <!-- Translations -->
            <div :class="{'vocab-translation': true}" v-if="(selection.length == 1 || selectedPhrase !== -1) && vocabEditMode == ''">
                <span class="title">Translation</span>
                <span class="vocab-edit" @click="vocabEditMode = 'translation'; updateVocabBoxPosition();" v-if="vocabEditMode == ''"><i class="fa fa-pen"></i> Edit</span>
                <ul v-if="(selectedTranslation.length > 1 || selectedTranslation[0] !== '') && vocabEditMode == ''">
                    <li v-for="translation, index in selectedTranslation" :key="index">{{ translation }}</li>
                </ul>
            </div>

            <!-- Translation editing -->
            <div :class="{'vocab-translation': true }" v-if="vocabEditMode == 'translation'">
                <span class="title">Search term</span>
                <input id="search-box" type="text" v-model="vocabSearch" @change="makeJishoRequest">
                <span class="title">Translation</span>
                
                <textarea v-model="uniqueWords[selection[0].uniqueWordIndex].translation" @change="updateNewWord" v-if="selection.length == 1"></textarea>
                <textarea v-model="phraseTranslation" @change="updatePhrase" v-if="selectedPhrase !== -1"></textarea>

                <span class="title">Search results</span>
                <div id="vocab-search-results">
                    <div class="vocab-search-result" v-for="(definition, definitionIndex) in searchResults" :key="definitionIndex" @click="addDefinitionToInput(definition.english)">
                        <div class="target-language" :title="definition.reading"><ruby>{{ definition.japanese }}<rt>{{ definition.reading }}</rt></ruby></div>
                        <div class="own-language" :title="definition.english">{{ definition.english }}</div>
                        <div class="add-result-button"><i class="fa fa-plus"></i></div>
                    </div>
                </div>
                <button class="btn btn-black small vocab-box-button" @click="vocabEditMode = ''">Close</button>
            </div>

            <!-- Stage buttons -->
            <div class="stage-buttons-box" v-if="(selection.length == 1 || selectedPhrase !== -1) && vocabEditMode == ''">
                <span class="title">Stage</span>
                <div :class="{'stage-buttons': true, 'hidden-ignore-button': selection.length > 1}"><!--
                    --><div @click="setStage(-7)" :class="{'stage-button': true, 'selected': selectionStage == -7}">7</div><!--
                    --><div @click="setStage(-6)" :class="{'stage-button': true, 'selected': selectionStage == -6}">6</div><!--
                    --><div @click="setStage(-5)" :class="{'stage-button': true, 'selected': selectionStage == -5}">5</div><!--
                    --><div @click="setStage(-4)" :class="{'stage-button': true, 'selected': selectionStage == -4}">4</div><!--
                    --><div @click="setStage(-3)" :class="{'stage-button': true, 'selected': selectionStage == -3}">3</div><!--
                    --><div @click="setStage(-2)" :class="{'stage-button': true, 'selected': selectionStage == -2}">2</div><!--
                    --><div @click="setStage(-1)" :class="{'stage-button': true, 'selected': selectionStage == -1}">1</div><!--
                    --><div @click="setStage(0)" :class="{'stage-button': true, 'selected': selectionStage == 0}"><i class="fa fa-book"></i></div><!--
                    --><div @click="setStage(1)" :class="{'stage-button': true, 'selected': selectionStage == 1}" v-if="selection.length == 1"><b>X</b></div>
                </div>
            </div>
        </div>
        <div v-if="!finished" id="reader" :class="{'plain-text-mode': settings.plainTextMode, 'japanese-text': settings.japaneseText, 'hidden': toolbar !== ''}">
            <template v-for="(word, wordIndex) in words">
                <template v-if="word.word.indexOf('NEWLINE') == -1  && _language !== 'japanese'">
                    <template v-if="spaceFreeWords.includes(word.word)">
                        <div :wordindex="wordIndex" :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': !settings.highlightWords, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                            @mousedown.stop="startSelection($event, wordIndex)" @mouseup.stop="finishSelection" @mousemove="updateSelection($event, wordIndex)" @mouseenter="hoverPhraseSelection(wordIndex)" @mouseleave="removePhraseHover()">{{ word.word }}</div>
                    </template><!--
                    --><template v-if="!spaceFreeWords.includes(word.word)"><!--
                        --><div :wordindex="wordIndex" :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': !settings.highlightWords, 'plain-text-': true, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                            @mousedown.stop="startSelection($event, wordIndex)" @mouseup.stop="finishSelection" @mousemove="updateSelection($event, wordIndex)" @mouseenter="hoverPhraseSelection(wordIndex)" @mouseleave="removePhraseHover()">{{ word.word }}</div><!--
                    --></template>

                </template><!--
                --><div v-if="word.word.indexOf('NEWLINE') == -1 && _language == 'japanese'" :wordindex="wordIndex" :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': !settings.highlightWords, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                    @mousedown.stop="startSelection($event, wordIndex)" @mouseup.stop="finishSelection" @mousemove="updateSelection($event, wordIndex)" @mouseenter="hoverPhraseSelection(wordIndex)" @mouseleave="removePhraseHover()">{{ word.word }}</div><!--
                    
                --><br v-if="word.word == 'NEWLINE'"><!--
            --></template>
            <br><br><br><br><br><br>
            <button id="finish-reading-button" class="btn btn-green" @click="finish()"><i class="fa fa-check"></i>&nbsp;&nbsp;Finish reading</button>
            <br><br><br><br>
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
                    highlightWords: true,
                    plainTextMode: false,
                    japaneseText: false,
                    fontSize: 20,
                    maximumTextWidth: '800px',
                    displaySuggestedTranslations: false,
                    displayPhraseReading: false,
                    displayGlossaryReadings: false,
                    autoMoveWordsToKnown: false
                },
                toolbar: '',
                spaceFreeWords: ['.', ',', ':', '?', '!', '-', '*', ' ', '\r\n', '\r\n '],
                finished: false,
                newlySavedWords: 0,
                learnedWords: 0,
                progressedWords: 0,
                glossary: [],
                words: JSON.parse(this._text),
                uniqueWords: JSON.parse(this._uniqueWords),
                phrases: JSON.parse(this._phrases),
                deletedPhrases: [],
                selection: [],
                selectedPhrase: -1,
                selectionStage: null,
                selectedTranslation: [],
                ongoingSelection: [],
                ongoingSelectionStartingWord: {
                    wordIndex: -1,
                },
                vocabBoxPosition: {
                    left: 0,
                    top: 0
                },
                vocabBoxSize: {
                    width: 400
                },
                selectionOngoing: false,
                searchResults: [],
                vocabEditMode: '',
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

            this.settings.highlightWords = this.$cookie.get('highlight-words') == 'true';
            this.settings.plainTextMode = this.$cookie.get('plain-text-mode') == 'true';
            this.settings.japaneseText = this.$cookie.get('japanese-text') == 'true';
            this.settings.fontSize =  parseInt(this.$cookie.get('font-size'));
            this.settings.maximumTextWidth =  this.$cookie.get('maximum-text-width');
            this.settings.displaySuggestedTranslations = this.$cookie.get('display-suggested-translations') == 'true';
            this.settings.displayPhraseReading = this.$cookie.get('display-phrase-reading') == 'true';
            this.settings.displayGlossaryReadings = this.$cookie.get('display-glossary-readings') == 'true';
            this.settings.autoMoveWordsToKnown = this.$cookie.get('auto-move-words-to-known') == 'true';

            if (this.settings.highlightWords === null) {
                this.settings.highlightWords =  true;
            }

            if (this.settings.plainTextMode === null) {
                this.settings.plainTextMode =  true;
            }

            if (this.settings.japaneseText === null) {
                this.settings.japaneseText =  true;
            }

            if (this.$cookie.get('font-size') === null) {
                this.settings.fontSize =  20;
            }

            if (this.settings.maximumTextWidth === null) {
                this.settings.maximumTextWidth =  '800px';
            }

            if (this.settings.displaySuggestedTranslations === null) {
                this.settings.displaySuggestedTranslations =  false;
            }

            if (this.settings.displayPhraseReading === null) {
                this.settings.displayPhraseReading =  false;
            }

            if (this.settings.displayGlossaryReadings === null) {
                this.settings.displayGlossaryReadings =  false;
            }

            if (this.settings.autoMoveWordsToKnown === null) {
                this.settings.autoMoveWordsToKnown =  false;
            }

            this.saveSettings();
            document.getElementById('app').addEventListener('mouseup', this.finishSelection);
            this.$forceUpdate();
            this.updatePhraseBorders();
            this.updateGlossary();
        },
        methods: {
            backToChapters: function() {
                if (window.confirm('Are you sure you want to go back to chapters?')) {
                    window.location.href = '/lessons/' + this._courseId;
                }
            },
            saveSettings: function() {
                if (this.settings.fontSize < 15) {
                    this.settings.fontSize = 15;
                }

                if (this.settings.fontSize > 25) {
                    this.settings.fontSize = 25;
                }

                this.$cookie.set('highlight-words', this.settings.highlightWords, 3650);
                this.$cookie.set('plain-text-mode', this.settings.plainTextMode, 3650);
                this.$cookie.set('japanese-text', this.settings.japaneseText, 3650);
                this.$cookie.set('font-size', this.settings.fontSize, 3650);
                this.$cookie.set('maximum-text-width', this.settings.maximumTextWidth, 3650);
                this.$cookie.set('display-suggested-translations', this.settings.displaySuggestedTranslations, 3650);
                this.$cookie.set('display-phrase-reading', this.settings.displayPhraseReading, 3650);
                this.$cookie.set('display-glossary-readings', this.settings.displayGlossaryReadings, 3650);
                this.$cookie.set('auto-move-words-to-known', this.settings.autoMoveWordsToKnown, 3650);
            },
            toggleToolbar: function(newToolbar) {
                this.unselectWord();
                this.updateGlossary();
                
                if (this.toolbar == newToolbar) {
                    this.toolbar = '';
                    return;
                }
                
                this.toolbar = newToolbar;
                if (newToolbar == 'chapters') {
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
            updateGlossary: function() {
                this.glossary = [];
                for (let i = 0; i < this.phrases.length; i++) {
                    if (this.phrases[i].stage < 0) {
                        this.glossary.push({
                            word: this.phrases[i].words.join(''),
                            stage: this.phrases[i].stage < 0 ? this.phrases[i].stage * -1 : this.phrases[i].stage,
                            reading: this.phrases[i].reading,
                            base_word: '',
                            base_word_reading: '',
                            translation: this.phrases[i].translation,
                        });
                    }
                }

                for (let i = 0; i < this.uniqueWords.length; i++) {
                    if (this.uniqueWords[i].stage < 0) {
                        this.glossary.push({
                            word: this.uniqueWords[i].word,
                            stage: this.uniqueWords[i].stage < 0 ? this.uniqueWords[i].stage * -1 : this.uniqueWords[i].stage,
                            reading: this.uniqueWords[i].reading,
                            base_word: this.uniqueWords[i].base_word,
                            base_word_reading: this.uniqueWords[i].base_word_reading,
                            translation: this.uniqueWords[i].translation,
                        });
                    }
                }

                this.glossary.sort((a, b) => {
                    return b.stage - a.stage;
                });
            },
            updateNewWord: function() {
                this.selectedTranslation = this.uniqueWords[this.selection[0].uniqueWordIndex].translation.split(';');
                if (this.selection.length == 1 && this.uniqueWords[this.selection[0].uniqueWordIndex].stage == 2) {
                    this.setStage(-5);
                }
            },
            startSelection: function(event, wordIndex) {
                if (event == undefined) {
                    return;
                }

                this.selectionOngoing = true;
                this.selectedTranslation = '';
                this.vocabEditMode = '';
                
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
                    position: event.target.getBoundingClientRect(),
                };
                
                this.ongoingSelection = [selectedWord];
                this.words[wordIndex].selected = true;
                this.ongoingSelectionStartingWord.wordIndex = wordIndex;
                this.updateSelectedWordLookupCount(selectedWord.word, selectedWord.uniqueWordIndex);
            },
            updateSelection: function(event, wordIndex) {
                if (!this.ongoingSelection.length || event == undefined || event.buttons !== 1) {
                    return;
                }

                if (wordIndex > this.ongoingSelection[0].wordIndex && 
                    wordIndex < this.ongoingSelection[this.ongoingSelection.length - 1].wordIndex) {
                        return;
                }

                var firstWordIndex = this.ongoingSelectionStartingWord.wordIndex;;
                var lastWordIndex = wordIndex;
                
                if (firstWordIndex > lastWordIndex) {
                    var firstWordIndex = wordIndex;
                    var lastWordIndex = this.ongoingSelectionStartingWord.wordIndex;
                }

                if (lastWordIndex - firstWordIndex > 14) {
                    lastWordIndex -= lastWordIndex - firstWordIndex - 14;
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
                        sentenceIndex: this.words[i].sentenceIndex
                    };

                    this.ongoingSelection.push(selectedWord);
                }
            },
            finishSelection: function() {
                this.selectionOngoing = false;
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
                    this.selectedTranslation = this.phrases[this.getSelectedPhraseIndex()].translation.split(';')
                } else if (this.selection.length == 1) {
                    this.uniqueWords[this.selection[0].uniqueWordIndex].checked = true;
                    this.selectedTranslation = this.uniqueWords[this.selection[0].uniqueWordIndex].translation.split(';');
                    
                }

                this.updateVocabBoxPosition();
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
            updateVocabBoxPosition: function() {
                if (!this.selection.length) {
                    return;
                }

                var reader = document.getElementById('reader-box').getBoundingClientRect();
                if (this.selection.length == 1) {
                    var positions = document.querySelector('[wordindex="' + this.selection[0].wordIndex + '"]').getBoundingClientRect();
                } else if (this.selection.length > 1) {
                    var positions = document.querySelector('[wordindex="' + this.selection[parseInt(this.selection.length / 2)].wordIndex + '"]').getBoundingClientRect();
                }

                this.vocabBoxPosition.left = positions.right - reader.left - this.vocabBoxSize.width / 2 - (positions.right - positions.left) / 2;

                if (this.vocabBoxPosition.left < 90) {
                    this.vocabBoxPosition.left = 90;
                } else if (this.vocabBoxPosition.left > reader.right - reader.left - this.vocabBoxSize.width - 30) {
                    this.vocabBoxPosition.left = reader.right - reader.left - this.vocabBoxSize.width - 30;
                }

                this.vocabBoxPosition.top = positions.bottom + 12 + document.getElementById('app').scrollTop;
                
                this.$nextTick(() => {
                    var vocabBox = document.getElementById('vocab-box');
                    if (vocabBox) {
                        vocabBox.scrollIntoViewIfNeeded(false);
                    }
                });
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
                if (!this.selection.length) {
                    return;
                }

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
                this.searchResults = [];
                for (var i = 0; i < data.length; i++) {
                    for (var j = 0; j < data[i].senses.length; j++) {
                        
                        var definitions = data[i].senses[j].english_definitions;
                        for (var k = 0; k < definitions.length; k++) {
                            this.searchResults.push({
                                english: definitions[k],
                                japanese: data[i].senses[j].restrictions.length ? data[i].senses[j].restrictions[0] : data[i].slug,
                                reading: data[i].japanese[0].reading,
                            });
                        }
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
                this.vocabEditMode = '';
                this.selectedPhrase = -1;
                this.selection = [];
                
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                    this.words[i].hover = false;
                }
            },
            addDefinitionToInput: function(definition) {
                if (this.selection.length == 1) {
                    if (this.uniqueWords[this.selection[0].uniqueWordIndex].translation.length) {
                        this.uniqueWords[this.selection[0].uniqueWordIndex].translation += ';';
                    }

                    this.uniqueWords[this.selection[0].uniqueWordIndex].translation += definition;
                    this.selectedTranslation = this.uniqueWords[this.selection[0].uniqueWordIndex].translation.split(';');
                } else {
                    if (this.phraseTranslation.length) {
                        this.phraseTranslation += ';';
                    }

                    this.phraseTranslation += definition;
                    this.updatePhrase();
                    this.selectedTranslation = this.phraseTranslation.split(';');
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

                this.selectedTranslation = this.phraseTranslation.split(';');
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
                this.updateVocabBoxPosition();
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
                    lessonId: this.$props._lessonId,
                    autoMoveWordsToKnown: this.settings.autoMoveWordsToKnown
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
