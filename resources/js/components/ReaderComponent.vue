<template>
    <div id="fullscreen-box" :class="{'fullscreen-mode': settings.fullscreen}" :style="{'background-color': $vuetify.theme.currentTheme.background}">
        <div id="reader-box" :style="{'max-width': settings.maximumTextWidth}" v-if="lessonId !== null">
            <div v-if="!finished" id="toolbar" class="d-flex " :style="{'width': settings.maximumTextWidth}">
                <v-btn-toggle group class="toolbar-button-group menus ma-0 mb-16" v-model="toolbarMenu">
                    <v-btn icon class="toolbar-button pa-0" @click="setToolbar('text')" value="text" title="Text"><v-icon>mdi-text-box-outline</v-icon></v-btn>
                    <v-btn icon class="toolbar-button pa-0" @click="setToolbar('chapters')" value="chapters" title="Chapters"><v-icon>mdi-bookmark</v-icon></v-btn>
                    <v-btn icon class="toolbar-button pa-0" @click="setToolbar('glossary')" value="glossary" title="Glossary"><v-icon>mdi-format-list-bulleted</v-icon></v-btn>
                    <v-btn icon class="toolbar-button pa-0" @click="setToolbar('settings')" value="settings" title="Settings"><v-icon>mdi-tune</v-icon></v-btn>
                </v-btn-toggle>

                
                <v-btn-toggle class="toolbar-button-group ma-0 d-none d-sm-flex" group multiple v-model="toolbarOptions">
                    <v-btn icon class="toolbar-button pa-0" @click="settings.plainTextMode = !settings.plainTextMode; saveSettings();"><v-icon>mdi-marker</v-icon></v-btn>
                    <v-btn icon class="toolbar-button pa-0" @click="settings.japaneseText = !settings.japaneseText; saveSettings();"><v-icon>mdi-syllabary-hiragana</v-icon></v-btn>
                    <v-btn icon class="toolbar-button pa-0" @click="settings.highlightWords = !settings.highlightWords; saveSettings();"><v-icon>mdi-format-letter-matches</v-icon></v-btn>
                </v-btn-toggle>
                
                <v-spacer></v-spacer>
                <div class="toolbar-button-group zoom ma-0">
                    <v-btn icon class="toolbar-button pa-0 ma-1" @click="settings.fontSize ++; unselectWord(); saveSettings();"><v-icon>mdi-magnify-plus</v-icon></v-btn>
                    <v-btn icon class="toolbar-button pa-0 ma-1" @click="settings.fontSize --; unselectWord(); saveSettings();"><v-icon>mdi-magnify-minus</v-icon></v-btn>
                </div>

                <v-btn-toggle class="toolbar-button-group ma-0" group multiple v-model="toolbarFullscreen">
                    <v-btn icon class="toolbar-button pa-0" @click="fullscreen" v-if="!settings.fullscreen"><v-icon>mdi-arrow-expand-all</v-icon></v-btn>
                    <v-btn icon class="toolbar-button pa-0" @click="exitFullscreen" v-if="settings.fullscreen"><v-icon>mdi-arrow-collapse-all</v-icon></v-btn>
                </v-btn-toggle>
            </div>

            <!-- Settings -->
            <div id="toolbar-header" v-if="toolbar !== 'text'" :style="{'background-color': $vuetify.theme.currentTheme.background}">
                <template v-if="toolbar == 'chapters'">Chapters</template>
                <template v-if="toolbar == 'glossary'">Glossary</template>
                <template v-if="toolbar == 'settings'">Settings</template>
            </div>
            <div id="settings" :class="{'visible': toolbar == 'settings'}">
                <!-- Highlight words -->
                <div class="setting switch">
                    <div class="setting-label">Highlight words:</div>
                    <div class="setting-input switch">
                        <v-switch
                            color="success"
                            v-model="settings.highlightWords" 
                            @change="saveSettings(); updateToolbarButtonGroup()"
                        ></v-switch>
                    </div>
                </div>

                <!-- Maximum text width -->
                <div class="setting">
                    <div class="setting-label">Maximum text width:</div>
                    <div class="setting-input slider">
                        <vue-slider 
                            :data="{
                                '800px': '800px',
                                '1000px': '1000px',
                                '1200px': '1200px',
                                '1400px': '1400px',
                                '1500px': '1600px',
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
                <div class="setting switch">
                    <div class="setting-label">Japanese vertical text:</div>
                    <div class="setting-input switch">
                        <v-switch
                            color="success"
                            v-model="settings.japaneseText" 
                            @change="saveSettings(); updateToolbarButtonGroup()"
                        ></v-switch>
                    </div>
                </div>

                <!-- Auto move words to known -->
                <div class="setting switch">
                    <div class="setting-label">Auto move words to known:</div>
                    <div class="setting-input switch">
                        <v-switch
                            color="success"
                            v-model="settings.autoMoveWordsToKnown" 
                            @change="saveSettings(); updateToolbarButtonGroup()"
                        ></v-switch>
                    </div>
                </div>
            </div>
            
            <!-- Chapters -->
            <div v-if="!finished" id="chapters" :class="{'visible': toolbar == 'chapters'}">
                <template v-for="(lesson, index) in lessons">
                    <div class="chapter-connect-line" v-if="index" v-for="i in 3"></div>
                    <v-card :id="lesson.id == lessonId ? 'selected-chapter' : ''" class="chapter rounded-lg pa-3 mx-auto" :key="index">
                        <div class="chapter-title">
                            {{ lesson.name }}
                            <div v-if="lesson.id == lessonId"> Current chapter</div>
                        </div>
                        <div class="chapter-read">Read count: <span>{{ lesson.read_count}}</span></div>
                        <div class="chapter-words">Words: <span>{{ lesson.wordCount.total }}</span></div>
                        <div class="chapter-unique-words">Unique words: <span>{{ lesson.wordCount.unique }}</span></div>
                        <div class="chapter-known-words">Known words: <span>{{ lesson.wordCount.known }}</span></div>
                        <div class="chapter-highlighted-words">Highlighted words: <span class="highlighted">{{ lesson.wordCount.highlighted }}</span></div>
                        <div class="chapter-new-words">New words: <span class="new">{{ lesson.wordCount.new }}</span></div>
                        <v-card-actions class="pa-0 mt-3">
                            <v-spacer></v-spacer>
                            <v-btn rounded width="80px" color="primary" :small="$vuetify.breakpoint.xsOnly" :to="'/chapters/read/' + lesson.id" v-if="lesson.id != lessonId">Read</v-btn>
                        </v-card-actions>
                    </v-card>
                </template>
            </div>

            <!-- Glossary -->
            <div v-if="!finished" id="glossary" :class="{'visible': toolbar == 'glossary'}">
                <template v-for="(word, index) in glossary">
                    <div class="glossary-entry " :key="index">
                        <div class="glossary-title">
                            <!-- Glossary entry stage -->
                            <div class="stage" :stage="word.stage">
                                {{ Math.abs(word.stage) }}
                            </div>
                            
                            <!-- Glossary entry word-->
                            <div class="word" v-if="word.base_word == ''">
                                <ruby>{{ word.word }}<rt>{{ word.reading }}</rt></ruby>
                            </div>
                            <div class="word" v-if="word.base_word !== ''">
                                <ruby>{{ word.base_word }}<rt>{{ word.base_word_reading }}</rt></ruby>
                                <i class="fas fa-long-arrow-alt-right"></i> 
                                <ruby>{{ word.word }}<rt>{{ word.reading }}</rt></ruby> 
                            </div>
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
            <v-card 
                id="vocab-box" 
                :class="{
                    'editing': vocabEditMode == 'word' || vocabEditMode == 'phrase', 
                    'new-phrase': selection.length > 1 && selectedPhrase == -1, 
                    'translation-edit': vocabEditMode == 'translation',
                    'rounded-lg': true,
                    'closed': vocabBoxClosed
                }" 
                :style="{
                    'top': vocabBoxPosition.top + 'px', 
                    'left': vocabBoxPosition.left + 'px',
                    'width': vocabBoxSize.width + 'px'
                }" 
                v-if="selection.length && !finished && !selectionOngoing " 
                @mouseup.stop=";"
            >
                <v-tabs id="vocab-box-tabs" grow background-color="primary" height="36" v-model="vocabBoxTab" @change="scrollToVocabBox">
                    <v-tab class="px-2" v-if="selection.length == 1">Word</v-tab>
                    <v-tab class="px-2" v-if="selection.length > 1">Phrase</v-tab>
                    <v-tab class="px-2">Edit</v-tab>
                    <v-tab class="px-2">Inflections</v-tab>
                </v-tabs>
                
                <v-card-text class="pa-2">
                    <v-tabs-items v-model="vocabBoxTab">
                        <!-- Word/phrase tab -->
                        <v-tab-item :value="0">
                            <!-- Single word -->
                            <template v-if="selection.length == 1">
                                <div class="vocab-box-subheader">Word</div>
                                <!-- With base word -->
                                <div id="word" class="pl-2" v-if="uniqueWords[selection[0].uniqueWordIndex].base_word !== ''">
                                    <ruby>{{uniqueWords[selection[0].uniqueWordIndex].base_word}}<rt>{{uniqueWords[selection[0].uniqueWordIndex].base_word_reading}}</rt></ruby>
                                    <v-icon>mdi-arrow-right-thick</v-icon>
                                    <ruby>{{uniqueWords[selection[0].uniqueWordIndex].word}}<rt>{{uniqueWords[selection[0].uniqueWordIndex].reading}}</rt></ruby>
                                </div>
                                
                                <!-- No base word -->
                                <div id="word" class="pl-2" v-if="uniqueWords[selection[0].uniqueWordIndex].base_word == ''">
                                    <ruby>{{uniqueWords[selection[0].uniqueWordIndex].word}}<rt>{{uniqueWords[selection[0].uniqueWordIndex].reading}}</rt></ruby>
                                </div>
                            </template>
                            
                            <!-- Phrase -->
                            <template v-if="selection.length > 1">
                                <div class="vocab-box-subheader">Phrase</div>
                                <!-- Phrase text -->
                                <div id="phrase" class="pl-2">
                                    <template v-for="(word, index) in selection" v-if="word.word !== 'NEWLINE'">{{ word.word }}</template>
                                </div>

                                <!-- Phrase reading -->
                                <template>
                                    <div class="vocab-box-subheader mt-2">Reading</div>
                                    <div id="reading" class="pl-2">{{ phraseReading }}</div>
                                </template>
                            </template>

                            <!-- Translation -->
                            <template v-if="selectedTranslation !== '' && (selectedTranslation.length > 1 || selectedTranslation[0] !== '')">
                                <div class="vocab-box-subheader mt-2">Definitions</div>
                                <ul id="definitions" class="ma-0">
                                    <template>
                                        <li v-for="translation, index in selectedTranslation" :key="index">{{ translation }}</li>
                                    </template>
                                </ul>
                            </template>

                            <!-- Stage buttons-->
                            <template v-if="selection.length == 1 || selectedPhrase !== -1">
                                <div class="vocab-box-subheader mt-2">Stage</div>
                                <div :class="{'d-block': true, 'text-center': true, 'mt-1': false, 'mb-6': selection.length == 1}">
                                    <div id="stage-buttons" class="v-item-group theme--light v-btn-toggle v-btn-toggle--rounded primary--text">
                                        <v-btn :value="-7" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == -7}" @click="setStage(-7)">7</v-btn>
                                        <v-btn :value="-6" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == -6}" @click="setStage(-6)">6</v-btn>
                                        <v-btn :value="-5" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == -5}" @click="setStage(-5)">5</v-btn>
                                        <v-btn :value="-4" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == -4}" @click="setStage(-4)">4</v-btn>
                                        <v-btn :value="-3" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == -3}" @click="setStage(-3)">3</v-btn>
                                        <v-btn :value="-2" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == -2}" @click="setStage(-2)">2</v-btn>
                                        <v-btn :value="-1" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == -1}" @click="setStage(-1)">1</v-btn>
                                        <v-btn :value="0" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == 0}" @click="setStage(0)"><v-icon>mdi-check</v-icon></v-btn>
                                        <v-btn :value="1" :class="{'stage-button': true, 'v-btn--active': vocabBoxSelectedStage == 1}" @click="setStage(1)" v-if="selection.length == 1"><v-icon>mdi-close</v-icon></v-btn>
                                    </div>
                                </div>
                            </template>

                            <!-- Save and delete phrase buttons -->
                            <v-card-actions class="pa-0">
                                <v-spacer></v-spacer>
                                <v-btn 
                                    class="mt-2"
                                    small
                                    rounded
                                    color="success"
                                    @click="saveNewPhrase"
                                    v-if="selection.length > 1 && selectedPhrase == -1"
                                >Save phrase</v-btn>
                                <v-btn 
                                    class="mt-2"
                                    small
                                    rounded
                                    color="error"
                                    @click="deletePhrase"
                                    v-if="selectedPhrase != -1"
                                >Delete phrase</v-btn>
                            </v-card-actions>
                        </v-tab-item>

                        <!-- Edit tab -->
                        <v-tab-item :value="1" v-if="selection.length == 1 || selectedPhrase !== -1">
                            <!-- Word editing -->
                            <v-simple-table dense id="word-edit-table" class="no-hover mx-auto" v-if="selection.length == 1">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Base</th>
                                        <th></th>
                                        <th>Selected</th>
                                    </tr>
                                </thead>
                                    <tbody>
                                    <tr>
                                        <th>Word</th>
                                        <td>
                                            <v-text-field 
                                                class="word-input"
                                                filled
                                                dense
                                                hide-details
                                                v-model="uniqueWords[selection[0].uniqueWordIndex].base_word"
                                            ></v-text-field>
                                        </td>
                                        <td><v-icon>mdi-arrow-right-thick</v-icon></td>
                                        <td id="selected-word-field" class="text-left pl-4">{{ uniqueWords[selection[0].uniqueWordIndex].word }}</td>
                                    </tr>
                                    <tr>
                                        <th>Reading</th>
                                        <td>
                                            <v-text-field 
                                                class="word-input my-1"
                                                filled
                                                dense
                                                hide-details
                                                v-model="uniqueWords[selection[0].uniqueWordIndex].base_word_reading"
                                            ></v-text-field>
                                        </td>
                                        <td><v-icon>mdi-arrow-right-thick</v-icon></td>
                                        <td>
                                            <v-text-field 
                                                class="word-input my-1"
                                                filled
                                                dense
                                                hide-details
                                                v-model="uniqueWords[selection[0].uniqueWordIndex].reading"
                                            ></v-text-field>
                                        </td>
                                    </tr>
                                </tbody>
                            </v-simple-table>
                            
                            <!-- Phrase editing -->
                            <template v-if="selectedPhrase !== -1">
                                <div class="vocab-box-subheader">Phrase reading</div>
                                <v-textarea
                                    filled
                                    dense
                                    no-resize
                                    hide-details
                                    height="80px"
                                    v-model="phraseReading"
                                    @change="updatePhrase"
                                ></v-textarea>
                            </template>

                            <!-- Translation editing -->

                            <!-- Word translation -->
                            <div class="vocab-box-subheader mt-2">Translation</div>
                            <v-textarea
                                filled
                                dense
                                no-resize
                                hide-details
                                height="80px"
                                v-model="uniqueWords[selection[0].uniqueWordIndex].translation"
                                @change="updateNewWord"
                                v-if="selection.length == 1"
                            ></v-textarea>
                            
                            <!-- Phrase translation -->
                            <v-textarea
                                filled
                                dense
                                no-resize
                                hide-details
                                height="80px"
                                v-model="phraseTranslation"
                                @change="updatePhrase"
                                v-if="selectedPhrase !== -1"
                            ></v-textarea>

                            <!-- Search term -->
                            <div class="vocab-box-subheader mt-2">Dictionary search</div>
                            <v-text-field 
                                class="mb-3"
                                filled
                                dense
                                hide-details
                                width="100%"
                                v-model="vocabSearch"
                                @change="makeSearchRequest"
                            ></v-text-field>

                            <!-- Search results -->
                            <div id="search-results" class="mb-4 pa-2">
                                <div class="search-result jmdict" v-for="(searchResult, searchresultIndex) in searchResults" :key="searchresultIndex">
                                    <div class="search-result-title rounded px-2">{{ searchResult.word }} <div class="dictionary">jmdict</div></div>
                                    <div class="search-result-definition rounded" v-for="(definition, definitionIndex) in searchResult.definitions" :key="definitionIndex" @click="addDefinitionToInput(definition)">
                                        {{ definition }} <v-icon>mdi-plus</v-icon>
                                    </div>
                                    <template v-if="searchResult.otherForms.length">
                                        <div class="vocab-box-subheader">Other forms:</div>
                                        <div class="d-flex flex-wrap">
                                            <div v-for="(form, formIndex) in searchResult.otherForms" :key="formIndex">
                                                {{ form }}<span class="mr-2" v-if="formIndex < searchResult.otherForms.length - 1">, </span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                        </v-tab-item>

                        <!-- Inflections tab -->
                        <v-tab-item :value="2" class="pb-4">
                            <v-simple-table dense id="inflections-table" class="no-hover mx-auto" v-if="inflections.length">
                                <thead>
                                    <tr>
                                        <th>Form</th>
                                        <th>Affirmative</th>
                                        <th>Negative</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(inflection, index) in inflections" :key="index">
                                        <td class="px-2">{{ inflection.name }}</td>
                                        <td class="px-1">{{ inflection.affPlain }}</td>
                                        <td class="px-1">{{ inflection.negPlain }}</td>
                                    </tr>
                                </tbody>
                            </v-simple-table>
                        </v-tab-item>
                    </v-tabs-items>
                </v-card-text>
                <v-btn id="close-vocab-box-button" rounded elevation="2" color="error" @click="unselectWord()"><v-icon>mdi-close</v-icon> Close</v-btn>
            </v-card>

            <!-- Text -->
            <div v-if="!finished" id="reader" :class="{'plain-text-mode': settings.plainTextMode, 'japanese-text': settings.japaneseText, 'hidden': toolbar !== 'text'}">
                <template v-for="(word, wordIndex) in words">
                    <template v-if="word.word.indexOf('NEWLINE') == -1 && word.word !== '\r\n' && language !== 'japanese'">
                        <template v-if="spaceFreeWords.includes(word.word)">
                            <div :wordindex="wordIndex" :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': !settings.highlightWords, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                                @mousedown.stop="startSelection($event, wordIndex)" @touchstart="startSelectionTouch($event, wordIndex)" @mouseup.stop="finishSelection($event)" @touchend.stop="finishSelection($event)" @touchmove="updateSelectionTouch($event, wordIndex);" @mousemove="updateSelectionMouse($event, wordIndex);" @pointerenter="hoverPhraseSelection(wordIndex);" @mouseleave="removePhraseHover()">{{ word.word }}</div>
                        </template><!--
                        --><template v-if="!spaceFreeWords.includes(word.word)"><!--
                            --><div :wordindex="wordIndex" :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': !settings.highlightWords, 'plain-text-': true, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                                @mousedown.stop="startSelection($event, wordIndex)" @touchstart="startSelectionTouch($event, wordIndex)" @mouseup.stop="finishSelection($event)" @touchend.stop="finishSelection($event)" @touchmove="updateSelectionTouch($event, wordIndex);" @mousemove="updateSelectionMouse($event, wordIndex);" @pointerenter="hoverPhraseSelection(wordIndex);" @mouseleave="removePhraseHover()">{{ word.word }}</div><!--
                        --></template>

                    </template><!--
                    --><div v-if="word.word.indexOf('NEWLINE') == -1 && language == 'japanese'" :wordindex="wordIndex" :stage="word.stage" :phrasestage="word.phraseStage" :class="{'no-highlight': !settings.highlightWords, 'plain-text-mode': settings.plainTextMode, word: true, highlighted: word.selected || word.hover, phrase: word.phraseIndexes.length > 0, 'phrase-start': word.phraseStart, 'phrase-end': word.phraseEnd}" :style="{'font-size': settings.fontSize + 'px'}" 
                        @mousedown.stop="startSelection($event, wordIndex)" @touchstart="startSelectionTouch($event, wordIndex)" @mouseup.stop="finishSelection($event)" @touchend.stop="finishSelection($event)" @touchmove="updateSelectionTouch($event, wordIndex);" @mousemove="updateSelectionMouse($event, wordIndex);" @pointerenter="hoverPhraseSelection(wordIndex);" @mouseleave="removePhraseHover()">{{ word.word }}</div><!--
                        
                    --><br v-if="word.word == 'NEWLINE'"><!--
                --></template>
                <br><br><br><br><br><br>
                <v-alert
                    class="my-3" 
                    border="left"
                    type="error"
                    v-if="finishError"
                    >
                    Something went wrong. Please try again.
                </v-alert>
                <v-btn rounded class="mt-8 mb-16" color="success" @click="finish()"><v-icon>mdi-text-box-check</v-icon> Finish reading</v-btn   >
                <br><br><br><br>
            </div>
            
            <!-- Finish -->
            <div v-if="finished" id="finished-box">
                <div id="lesson-finished-text">Congratulations! You have finished {{ lessonName }}!</div>

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
                            <td> {{ wordCount }} </td>
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
                <div class="text-center">
                    <v-btn rounded :small="$vuetify.breakpoint.xsOnly" color="primary" :to="'/chapters/' + bookId">Go to lessons</v-btn>
                    <v-btn rounded :small="$vuetify.breakpoint.xsOnly" color="primary" :to="'/chapters/read/' + nextLesson" v-if="nextLesson !== -1">Go to next lesson</v-btn>
                </div>
            </div>
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
                    autoMoveWordsToKnown: false,
                    fullscreen: false,
                },
                toolbarMenu: 'text',
                toolbarOptions: [],
                toolbarFullscreen: [],
                toolbar: 'text',
                spaceFreeWords: ['.', ',', ':', '?', '!', '-', '*', ' ', '\r\n', '\r\n '],
                finished: false,
                finishError: false,
                newlySavedWords: 0,
                learnedWords: 0,
                progressedWords: 0,
                glossary: [],
                words: [],
                uniqueWords: [],
                phrases: [],
                deletedPhrases: [],
                selection: [],
                selectedPhrase: -1,
                selectedTranslation: [],
                ongoingSelection: [],
                ongoingSelectionStartingWord: {
                    wordIndex: -1,
                },
                touchTimer: null,
                vocabBoxTab: 0,
                vocabBoxPosition: {
                    left: 0,
                    top: 0
                },
                vocabBoxSize: {
                    width: window.innerWidth > 440 ? 400 : window.innerWidth - 20,
                },
                vocabBoxClosed: true,
                vocabBoxSelectedStage: 0,
                selectionOngoing: false,
                searchResults: [],
                vocabEditMode: '',
                allSearchResultsVisible: false,
                showAllSearchResults: false,
                vocabSearch: '',
                phraseTranslation: '',
                phraseReading: '',
                nextLesson: -1,


                // to load:
                words: [],
                uniqueWords: [],
                phrases: [],
                bookName: null,
                lessonId: null,
                wordCount: 0,
                lessonName: null,
                bookId: null,
                language: null,
                lessons: [],
            }
        },
        props: {
        },
        mounted() {
            window.oncontextmenu = function(event) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            };


            axios.post('/chapter/get/reader', {
                'chapterId': this.$route.params.chapterId,
            }).then(function (response) {
                var data = response.data;
                this.words = data.words;
                this.uniqueWords = data.uniqueWords;
                this.phrases = data.phrases;
                this.bookName = data.bookName;
                this.lessonId = data.lessonId;
                this.wordCount = data.wordCount;
                this.lessonName = data.lessonName;
                this.bookId = data.bookId;
                this.language = data.language;
                this.lessons = data.lessons;

                this.afterMounted();
            }.bind(this)).catch(function (error) {
            }).then(function () {
            });
        },
        // this runs after the initial data
        // was downloaded with axios
        methods: {
            afterMounted: function() {
                document.getElementById('app').addEventListener('mouseup', this.finishSelection);
                window.addEventListener('resize', this.updateVocabBoxPosition);
                document.getElementById('fullscreen-box').addEventListener('fullscreenchange', this.updateFullscreen);
                for (let i = 0; i < this.lessons.length; i++) {
                    if (this.lessons[i].id == this.lessonId && i < this.lessons.length - 1 && this.lessons[i + 1].read_count) {
                        this.nextLesson = this.lessons[i + 1].id;
                        break;
                    }
                }

                this.settings.highlightWords = this.$cookie.get('highlight-words') === 'true';
                this.settings.plainTextMode = this.$cookie.get('plain-text-mode') === 'true';
                this.settings.japaneseText = this.$cookie.get('japanese-text') === 'true';
                this.settings.fontSize =  parseInt(this.$cookie.get('font-size'));
                this.settings.maximumTextWidth =  this.$cookie.get('maximum-text-width');
                this.settings.displaySuggestedTranslations = this.$cookie.get('display-suggested-translations') === 'true';
                this.settings.autoMoveWordsToKnown = this.$cookie.get('auto-move-words-to-known') === 'true';

                if (this.$cookie.get('highlight-words') === null) {
                    this.settings.highlightWords = true;
                }

                if (this.$cookie.get('plain-text-mode') === null) {
                    this.settings.plainTextMode = false;
                }

                if (this.$cookie.get('japanese-text') === null) {
                    console.log('oh');
                    this.settings.japaneseText =  false;
                }

                if (this.$cookie.get('font-size') === null) {
                    this.settings.fontSize =  20;
                }

                if (this.$cookie.get('maximum-text-width') === null) {
                    this.settings.maximumTextWidth =  '800px';
                }

                if (this.$cookie.get('display-suggested-translations') === null) {
                    this.settings.displaySuggestedTranslations =  false;
                }

                if (this.$cookie.get('auto-move-words-to-known') === null) {
                    this.settings.autoMoveWordsToKnown =  true;
                }

                this.saveSettings();
                this.$forceUpdate();
                this.updatePhraseBorders();
                this.updateGlossary();
                this.updateToolbarButtonGroup();
            },
            fullscreen: function() {
                if (document.fullscreenEnabled) {
                    document.getElementById('fullscreen-box').requestFullscreen();
                    this.settings.fullscreen = true;
                }
            },
            exitFullscreen: function() {
                document.exitFullscreen();
                this.settings.fullscreen = false;
            },
            updateFullscreen: function() {
                this.settings.fullscreen = document.fullscreenElement !== null;

                this.toolbarFullscreen = this.settings.fullscreen ? [0] : [];
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
                this.$cookie.set('auto-move-words-to-known', this.settings.autoMoveWordsToKnown, 3650);
            },
            setToolbar: function(newToolbar) {
                this.unselectWord();
                this.updateGlossary();
                
                this.toolbar = newToolbar;
                if (newToolbar == 'chapters') {
                    setTimeout(() => {
                        document.getElementById('selected-chapter').scrollIntoView();
                    }, 305);
                } else {
                    document.getElementById('fullscreen-box').scrollTo(0, 0);
                }
            },
            updateToolbarButtonGroup: function() {
                this.toolbarOptions = [];
                if (this.settings.plainTextMode) {
                    this.toolbarOptions.push(0);
                }

                if (this.settings.japaneseText) {
                    this.toolbarOptions.push(1);
                }

                if (this.settings.highlightWords) {
                    this.toolbarOptions.push(2);
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
                            stage: this.phrases[i].stage,
                            reading: this.phrases[i].reading,
                            base_word: '',
                            base_word_reading: '',
                            translation: this.phrases[i].translation,
                        });
                    }
                }

                for (let i = 0; i < this.uniqueWords.length; i++) {
                    if (this.uniqueWords[i].stage < 0 || this.uniqueWords[i].stage == 2) {
                        this.glossary.push({
                            word: this.uniqueWords[i].word,
                            stage: this.uniqueWords[i].stage,
                            reading: this.uniqueWords[i].reading,
                            base_word: this.uniqueWords[i].base_word,
                            base_word_reading: this.uniqueWords[i].base_word_reading,
                            translation: this.uniqueWords[i].translation,
                        });
                    }
                }

                this.glossary.sort((a, b) => {
                    return a.stage - b.stage;
                });
            },
            updateNewWord: function() {
                this.selectedTranslation = this.uniqueWords[this.selection[0].uniqueWordIndex].translation.split(';');
                if (this.selection.length == 1 && this.uniqueWords[this.selection[0].uniqueWordIndex].stage == 2) {
                    this.setStage(-5);
                }
            },
            startSelectionTouch: function(event, wordIndex) {
                this.touchTimer = setTimeout(() => {
                    this.startSelection(event, wordIndex);
                }, 500);
                
            },
            startSelection: function(event, wordIndex) {
                this.touchTimer = null;
                if (event == undefined) {
                    return;
                }

                if (event.cancelable) {
                    event.preventDefault();
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
            updateSelectionMouse: function(event, wordIndex) {
                if (!this.ongoingSelection.length || event == undefined || event.buttons !== 1 || this.touchTimer) {
                    return;
                }

                this.updateSelection(wordIndex);
            },
            updateSelectionTouch: function(event, wordIndex) {
                if (this.touchTimer) {
                    clearTimeout(this.touchTimer);
                    this.touchTimer = null;
                    return;
                } else {
                    if (event.cancelable) {
                        event.preventDefault();
                    }
                }
                
                var touch = event.changedTouches[0];
                var element = document.elementFromPoint( touch.clientX, touch.clientY );

                var wordIndex = null;
                console.log(element);
                if (element !== null && element.classList.contains('word')) {
                    wordIndex = element.getAttribute('wordindex');
                }

                if (wordIndex !== null && this.ongoingSelection.length) {
                    
                    this.updateSelection(wordIndex);
                }
            },
            updateSelection: function(wordIndex) {
                if (this.touchTimer) {
                    return;
                }

                if (wordIndex == this.ongoingSelection[0].wordIndex ||
                    wordIndex == this.ongoingSelection[this.ongoingSelection.length - 1].wordIndex) {
                        return;
                }

                var firstWordIndex = this.ongoingSelectionStartingWord.wordIndex;
                var lastWordIndex = wordIndex;
                
                if (firstWordIndex > lastWordIndex) {
                    var firstWordIndex = wordIndex;
                    var lastWordIndex = this.ongoingSelectionStartingWord.wordIndex;
                }
                
                if (firstWordIndex < this.ongoingSelectionStartingWord.wordIndex - 14) {
                    firstWordIndex = this.ongoingSelectionStartingWord.wordIndex - 14;
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
            finishSelection: function(event) {
                if (!event.cancelable) {
                    return;
                }

                if (this.touchTimer) {
                    clearTimeout(this.touchTimer);
                    this.touchTimer = null;
                    return;
                }

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
                        if (this.uniqueWords[this.ongoingSelection[0].uniqueWordIndex].base_word !== '') {
                            this.vocabSearch = this.uniqueWords[this.ongoingSelection[0].uniqueWordIndex].base_word;
                        } else {
                            this.vocabSearch = this.uniqueWords[this.ongoingSelection[0].uniqueWordIndex].word;
                        }
                        
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
                    console.log('sentence index:', this.selection[0].sentenceIndex);
                    this.uniqueWords[this.selection[0].uniqueWordIndex].checked = true;
                    this.selectedTranslation = this.uniqueWords[this.selection[0].uniqueWordIndex].translation.split(';');
                }

                this.updateVocabBoxPosition();
                this.makeSearchRequest();
                this.updatePhraseBorders();
                this.vocabBoxTab = 0;
                this.vocabBoxClosed = false;
            },
            removePhraseHover: function() {
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].hover = false;
                }
            },
            updateSelectedWordStage: function() {
                if (this.selectedPhrase == -1 && this.selection.length) {
                    this.vocabBoxSelectedStage = parseInt(this.uniqueWords[this.selection[0].uniqueWordIndex].stage);
                } else if (this.selectedPhrase !== -1){
                    this.vocabBoxSelectedStage = parseInt(this.phrases[this.selectedPhrase].stage);
                }

                if (this.vocabBoxSelectedStage == 2) {
                    this.vocabBoxSelectedStage = undefined;
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
                    console.log('new example sentence:', exampleSentence);
                }

            },
            updateVocabBoxPosition: function() {
                this.vocabBoxSize.width = window.innerWidth > 440 ? 400 : window.innerWidth - 24;

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


                if (window.innerWidth  < 440) {
                    this.vocabBoxPosition.left = 8;
                } else if (this.vocabBoxPosition.left < 5) {
                    this.vocabBoxPosition.left = 5;
                } else if (this.vocabBoxPosition.left > reader.right - reader.left - this.vocabBoxSize.width) {
                    this.vocabBoxPosition.left = reader.right - reader.left - this.vocabBoxSize.width;
                }

                if (this.settings.fullscreen) {
                    this.vocabBoxPosition.top = positions.bottom + 12 + document.getElementById('fullscreen-box').scrollTop;
                } else {
                    this.vocabBoxPosition.top = positions.bottom + 12 + document.getElementById('app').scrollTop - (document.getElementById('fullscreen-box').getBoundingClientRect().top + document.getElementById('app').scrollTop);
                }

                this.scrollToVocabBox();
            },
            scrollToVocabBox: function() {
                setTimeout(() => {
                    var vocabBox = document.getElementById('vocab-box');
                    if (vocabBox) {
                        vocabBox.scrollIntoViewIfNeeded(false);
                    }
                }, 450);
            },
            updateSelectedWordLookupCount: function(word, uniqueWordIndex) {
                this.uniqueWords[uniqueWordIndex].lookup_count ++;

                for (var i  = 0; i < this.words.length; i++) {
                    if (this.words[i].word.toLowerCase() == word) {
                        this.words[i].lookup_count = this.uniqueWords[uniqueWordIndex].lookup_count;
                    }
                }
            },
            makeSearchRequest: function() {
                this.searchResults = [];
                this.inflections = [];
                if (!this.selection.length) {
                    return;
                }

                // search inflections
                axios.post('/dictionary/search/inflections', {
                    dictionary: 'jmdict',
                    term: this.vocabSearch
                })
                .then(function (response) {
                    var data = JSON.parse(response.data);
                    var displayedInflections = ['Non-past', 'Non-past, polite', 'Past', 'Past, polite', 'Te-form', 'Potential', 'Passive', 'Causative', 'Causative Passive', 'Imperative'];
                    
                    for (var i = 0; i < data.length; i++) {
                        if (!displayedInflections.includes(data[i].name)) {
                            continue;
                        }

                        var index = this.inflections.findIndex(item => item.name === data[i].name);
                        if (index == -1) {
                            this.inflections.push({
                                name: data[i].name,
                            });

                            index = this.inflections.length - 1;
                        }

                        // add different forms to the item
                        if (data[i].form == 'aff-plain:') {
                            this.inflections[index].affPlain = data[i].value;
                        }

                        if (data[i].form == 'aff-formal:') {
                            this.inflections[index].affFormal = data[i].value;
                        }

                        if (data[i].form == 'neg-plain:') {
                            this.inflections[index].negPlain = data[i].value;
                        }

                        if (data[i].form == 'neg-formal:') {
                            this.inflections[index].negFormal = data[i].value;
                        }
                    }
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                })
                .then(function () {
                });

                // search word
                axios.post('/dictionary/search', {
                    dictionary: 'jmdict',
                    term: this.vocabSearch
                })
                .then(function (response) {
                    this.processSearchRequest(response.data);
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                })
                .then(function () {
                });
            },
            processSearchRequest: function(data) {
                this.searchResults = [];
                for (var i = 0; i < data.length; i++) {
                    this.searchResults.push({
                        word: data[i].words.shift(),
                        otherForms: data[i].words,
                        definitions: data[i].definitions
                    });
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
                this.vocabBoxClosed = true;
                setTimeout(() => {
                    this.vocabEditMode = '';
                    this.selectedPhrase = -1;
                    this.selection = [];
                    
                    for (let i  = 0; i < this.words.length; i++) {
                        this.words[i].selected = false;
                        this.words[i].hover = false;
                    }
                }, 120);
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
            stageChanged: function() {
                if (this.selectedPhrase == -1) {
                    this.vocabBoxSelectedStage = this.uniqueWords[this.selection[0].uniqueWordIndex].stage;
                } else {
                    this.vocabBoxSelectedStage = this.phrases[this.selectedPhrase].stage;
                }
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
                    
                    // phrase start
                    this.words[i].phraseStart = false;
                    this.words[i].phraseEnd = false;
                    if (this.words[i].phraseIndexes.length && (i == 0 || !this.words[i - 1].phraseIndexes.length)) {
                        this.words[i].phraseStart = true;
                    }
                    
                    // phrase end
                    if (this.words[i].phraseIndexes.length && (i + 1 == this.words.length || !this.words[i + 1].phraseIndexes.length)) {
                        this.words[i].phraseEnd = true;
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
                if (!phraseIndexes.length) {
                    return;
                }

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
                axios.post('/chapter/finish', {
                    uniqueWords: JSON.stringify(this.uniqueWords),
                    phrases: JSON.stringify(this.phrases),
                    deletedPhrases: JSON.stringify(this.deletedPhrases),
                    sentences: JSON.stringify(this.sentences),
                    language: this.language,
                    lessonId: this.lessonId,
                    autoMoveWordsToKnown: this.settings.autoMoveWordsToKnown
                })
                .then(function (response) {
                    if (response.data == 'success') {
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
                    } else {
                        this.finishError = true;
                    }
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
