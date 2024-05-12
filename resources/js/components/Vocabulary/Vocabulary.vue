<template>
    <v-container id="vocabulary">
        <!-- Vocabulary edit dialog -->
        <vocabulary-edit-dialog 
            v-if="vocabularyEditDialog.active"
            v-model="vocabularyEditDialog.active" 
            :item-id="vocabularyEditDialog.itemId" 
            :item-type="vocabularyEditDialog.itemType" 
            :language-spaces="languageSpaces"
            :language="$props.language"
            @saved="loadVocabularySearchPage"
        ></vocabulary-edit-dialog>

        <!-- Vocabulary export dialog -->
        <vocabulary-export-dialog 
            v-if="vocabularyExportDialog.active"
            v-model="vocabularyExportDialog.active" 
            :sample-words="words"
            :language="$props.language"
            :language-spaces="languageSpaces"
            @export-to-csv="exportToCsv"
        ></vocabulary-export-dialog>

        <!-- Vocabulary import dialog -->
        <vocabulary-import-dialog 
            v-if="vocabularyImportDialog.active"
            v-model="vocabularyImportDialog.active"
        ></vocabulary-import-dialog>

        <!-- Search header -->
        <v-card outlined class="rounded-lg px-4 pb-4 my-4" :loading="loading">
            <div class="subheader my-4 d-flex">
                Vocabulary
                <v-spacer></v-spacer>
                <v-chip id="search-result-info" class="pl-1"><v-icon class="mr-1" right>mdi-text-box-check</v-icon>{{ wordCount }} results</v-chip>
            </div>

            <!-- search filter -->
            <div id="vocabulary-search-field" class="mb-6">
                <v-btn rounded depressed color="primary" @click="applyFilter('text')"><v-icon>mdi-magnify</v-icon> Search</v-btn>
                <v-text-field class="pt-0" rounded label="Search term" v-model="filters.text" @keyup.enter="applyFilter('text')"></v-text-field>
            </div>

            <!-- filters -->
            <v-container fluid>
                <v-row id="filters" :class="{'hidden': filtersHidden}">
                    <!-- Stage filter -->
                    <v-menu offset-y>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn class="filter-menu pl-3 pr-2 mx-1" rounded depressed v-bind="attrs" v-on="on">
                                Level
                                <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                                <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list class="filter-popup pa-0" dense>
                            <v-list-item-group color="primary">
                                <v-list-item :class="{'v-list-item--active': filters.stage == -999}" @click="applyFilter('stage', -999)">Any</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == 2}" @click="applyFilter('stage', 2)">New</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == 1}" @click="applyFilter('stage', 1)">Ignored</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == 0}" @click="applyFilter('stage', 0)">Learned</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == -1}" @click="applyFilter('stage', -1)">1</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == -2}" @click="applyFilter('stage', -2)">2</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == -3}" @click="applyFilter('stage', -3)">3</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == -4}" @click="applyFilter('stage', -4)">4</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == -5}" @click="applyFilter('stage', -5)">5</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == -6}" @click="applyFilter('stage', -6)">6</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.stage == -7}" @click="applyFilter('stage', -7)">7</v-list-item>
                            </v-list-item-group>
                        </v-list>
                    </v-menu>

                    <!-- Book filter -->
                    <v-menu right offset-y v-if="books.length">
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn class="filter-menu pl-3 pr-2 mx-1" rounded depressed v-bind="attrs" v-on="on">
                                Book
                                <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                                <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list class="filter-popup pa-0" dense>
                            <v-list-item-group color="primary">
                                <v-list-item :class="{'v-list-item--active': filters.book == -1}" @click="applyFilter('book', -1, -1)">Any</v-list-item>
                                <v-list-item 
                                    v-for="(book, index) in books" :key="index"
                                    :class="{'default-font': true, 'v-list-item--active': filters.book == book.id}"
                                    @click="applyFilter('book', book.id, index)">{{ book.name }}</v-list-item>
                                </v-list-item>
                            </v-list-item-group>
                        </v-list>
                    </v-menu>

                    <!-- Chapter filter -->
                    <v-menu offset-y v-if="filters.bookIndex !== -1 && books.length">
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn class="filter-menu pl-3 pr-2 mx-1" rounded depressed v-bind="attrs" v-on="on">
                                Chapter
                                <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                                <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list class="filter-popup pa-0" dense>
                            <v-list-item-group color="primary">
                                <v-list-item :class="{'v-list-item--active': filters.chapter == -1}" @click="applyFilter('chapter', -1)">Any</v-list-item>
                                <v-list-item 
                                    v-for="(chapter, index) in books[filters.bookIndex].chapters" :key="index"
                                    :class="{'default-font': true, 'v-list-item--active': filters.chapter == chapter.id}"
                                    @click="applyFilter('chapter', chapter.id, index)">{{ chapter.name }}</v-list-item>
                                </v-list-item>
                            </v-list-item-group>
                        </v-list>
                    </v-menu>

                    <!-- Translation filter -->
                    <v-menu offset-y>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn class="filter-menu pl-3 pr-2 mx-1" rounded depressed v-bind="attrs" v-on="on">
                                Translation
                                <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                                <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list class="filter-popup pa-0" dense>
                            <v-list-item-group color="primary">
                                <v-list-item 
                                    :class="{'v-list-item--active': filters.translation == 'any'}"
                                    @click="applyFilter('translation', 'any')">Any
                                </v-list-item>
                                <v-list-item 
                                    :class="{'v-list-item--active': filters.translation == 'not empty'}" 
                                    @click="applyFilter('translation', 'not empty')">Not empty
                                </v-list-item>
                            </v-list-item-group>
                        </v-list>
                    </v-menu>

                    <!-- Phrases filter -->
                    <v-menu offset-y>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn class="filter-menu pl-3 pr-2 mx-1" rounded depressed v-bind="attrs" v-on="on">
                                Phrases
                                <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                                <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list class="filter-popup pa-0" dense>
                            <v-list-item-group color="primary">
                                <v-list-item 
                                    :class="{'v-list-item--active': filters.phrases == 'both'}"
                                    @click="applyFilter('phrases', 'both')">Both
                                </v-list-item>
                                <v-list-item 
                                    :class="{'v-list-item--active': filters.phrases == 'only words'}" 
                                    @click="applyFilter('phrases', 'only words')">Only words
                                </v-list-item>
                                <v-list-item 
                                    :class="{'v-list-item--active': filters.phrases == 'only phrases'}" 
                                    @click="applyFilter('phrases', 'only phrases')">Only phrases
                                </v-list-item>
                            </v-list-item-group>
                        </v-list>
                    </v-menu>

                    <!-- Search result order -->
                    <v-menu offset-y>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn class="filter-menu pl-3 pr-2 mx-1" rounded depressed v-bind="attrs" v-on="on">
                                Order by
                                <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                                <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list class="filter-popup pa-0" dense>
                            <v-list-item-group color="primary">
                                <v-list-item :class="{'v-list-item--active': filters.orderBy == 'words'}" @click="applyFilter('orderBy', 'words')"><v-icon class="mr-1">mdi-sort-alphabetical-ascending</v-icon>Word</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.orderBy == 'words desc'}" @click="applyFilter('orderBy', 'words desc')"><v-icon class="mr-1">mdi-sort-alphabetical-descending</v-icon>Word</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.orderBy == 'stage'}" @click="applyFilter('orderBy', 'stage')"><v-icon class="mr-1">mdi-sort-numeric-ascending</v-icon>Level</v-list-item>
                                <v-list-item :class="{'v-list-item--active': filters.orderBy == 'stage desc'}" @click="applyFilter('orderBy', 'stage desc')"><v-icon class="mr-1">mdi-sort-numeric-descending</v-icon>Level</v-list-item>
                            </v-list-item-group>
                        </v-list>
                    </v-menu>

                    <!-- Show filters -->
                    <v-btn class="filter-menu show-filters px-3" rounded depressed @click="filtersHidden = !filtersHidden">
                        <template v-if="filtersHidden"><v-icon small class="mr-1">mdi-eye</v-icon>Show filters</template>
                        <template v-else><v-icon small class="mr-1">mdi-eye-off</v-icon>Hide filters</template>
                    </v-btn>
                    
                    <!-- search result info -->
                    <v-spacer></v-spacer>

                    <!-- Export / import -->
                    <v-menu offset-y>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn class="filter-menu export pl-3 pr-2" rounded depressed v-bind="attrs" v-on="on">
                                <v-icon small class="mr-1">mdi-file-download</v-icon>Data
                                <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                                <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list class="filter-popup pa-0" dense>
                            <v-list-item @click="openExportDialog" :disabled="loading">
                                <v-icon class="mr-1">mdi-file-delimited</v-icon>Export
                            </v-list-item>
                            <v-list-item @click="openImportDialog" :disabled="loading">
                                <v-icon class="mr-1">mdi-file-delimited</v-icon>Import
                            </v-list-item>
                        </v-list>
                    </v-menu>
                </v-row>
            </v-container>
        </v-card>

        <!-- Vocabulary list -->
        <v-simple-table id="vocabulary-list" class="py-0 no-hover border rounded-lg" dense>
            <thead>
                <tr>
                    <th class="word">Word</th>
                    <th class="reading" v-if="($props.language == 'japanese' || $props.language == 'chinese')">Reading</th>
                    <th class="word-with-reading">Word</th>
                    <th class="stage px-1">Level</th>
                    <th class="translation">Definitions</th>
                    <th class="actions">Options</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(word, index) in words" :key="index">
                    <template v-if="word.type == 'phrase'">
                        <td class="word default-font" v-if="!languageSpaces">{{ (JSON.parse(word.word)).join('') }}</td>
                        <td class="word default-font" v-if="languageSpaces">{{ (JSON.parse(word.word)).join(' ') }}</td>
                    </template>
                    <template v-if="word.type == 'word'">
                        <td class="word default-font">{{ word.word }}</td>
                    </template>
                    <td class="reading default-font" v-if="($props.language == 'japanese' || $props.language == 'chinese')">{{ word.reading }}</td>
                    
                    <template v-if="word.type == 'phrase'">
                        <td class="word-with-reading default-font" v-if="!languageSpaces">
                            {{ (JSON.parse(word.word)).join('') }}
                        </td>
                        <td class="word-with-reading default-font" v-if="languageSpaces">
                            {{ (JSON.parse(word.word)).join(' ') }}
                        </td>
                    </template>
                    <template v-if="word.type == 'word'">
                        <td class="word-with-reading default-font"><ruby>{{ word.word }}<rt v-if="($props.language == 'japanese' || $props.language == 'chinese')">{{ word.reading }}</rt></ruby></td>
                    </template>

                    <td class="stage px-1" :stage="word.stage" v-if="word.stage < 0">
                        <div class="highlighted-word">{{ word.stage * -1 }}</div>
                    </td>
                    <td class="stage px-1" :stage="word.stage" v-if="word.stage == 0">
                        <div>0</div>
                    </td>
                    <td class="stage px-1" :stage="word.stage" v-if="word.stage == 1">
                        <div>X</div>
                    </td>
                    <td class="stage px-1" :stage="word.stage" v-if="word.stage == 2">
                        <div class="new-word">New</div>
                    </td>
                    
                    <td class="translation">{{ word.translation }}</td>
                    <td class="actions">
                        <v-btn 
                            v-if="word.type == 'word'"
                            icon 
                            title="Edit"
                            @click="editItem(word.id, 'Word')"
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                        <v-btn 
                            v-else
                            icon 
                            title="Edit"
                            @click="editItem(word.id, 'Phrase')"
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                    </td>
                </tr>
            </tbody>
        </v-simple-table>

        <div class="px-2">
            <v-pagination
                class="my-6"
                v-model="currentPage"
                :length="pageCount"
                :total-visible="10"
                prev-icon="mdi-menu-left"
                next-icon="mdi-menu-right"
                @input="moveToPage(currentPage)"
            ></v-pagination>
        </div>
    </v-container>
</template>

<script>
    export default {
        data: function() {
            return {
                loading: false,
                filtersHidden: true,
                visiblePopup: '',
                paginationLimitBefore: 3,
                paginationLimitAfter: 3,
                words: [],
                wordCount: 0,
                books: [],
                pageCount: 1,
                currentPage: 1,
                vocabularyExportDialog: {
                    active: false,
                },
                vocabularyImportDialog: {
                    active: false,
                },
                vocabularyEditDialog: {
                    active: false,
                    wordId: -1,
                    phraseId: -1
                },
                filters: {
                    bookIndex: -1,
                    stage: -999,
                    book: -1,
                    chapter: -1,
                    translation: 'any',
                    phrases: 'both',
                    orderBy: 'words',
                    text: ''
                },
                languageSpaces: true,
            }
        },
        props: {
            language: String
        },
        mounted() {
            this.loading = true;
            document.getElementById('app').addEventListener('scroll', () => { this.visiblePopup = ''; });
            document.getElementById('app').addEventListener('click', () => { this.visiblePopup = ''; });
            
            if (this.$route.params.text !== undefined) {
                this.filters.text = (this.$route.params.text == 'anytext') ? '' : this.$route.params.text;
                this.filters.stage = this.$route.params.stage;
                this.filters.book = this.$route.params.book;
                this.filters.chapter = this.$route.params.chapter;
                this.filters.translation = this.$route.params.translation;
                this.filters.phrases = this.$route.params.phrases;
                this.filters.orderBy = this.$route.params.orderBy;
                this.currentPage = parseInt(this.$route.params.page);
            }

            this.loadVocabularySearchPage();
        },
        methods: {
            loadVocabularySearchPage() {
                axios.post('/vocabulary/search', {
                    text: (this.filters.text == '') ? 'anytext' : this.filters.text,
                    book: parseInt(this.filters.book),
                    chapter: parseInt(this.filters.chapter),
                    stage: parseInt(this.filters.stage),
                    translation: this.filters.translation,
                    phrases: this.filters.phrases,
                    orderBy: this.filters.orderBy,
                    page: this.currentPage,
                }).then((response) => {
                    this.loading = false;
                    var data = response.data;
                    this.filters.bookIndex = data.bookIndex;
                    this.words = data.words;
                    this.books = data.books;
                    this.pageCount = data.pageCount;
                    this.currentPage = parseInt(data.currentPage);
                    this.wordCount = data.wordCount;
                    this.languageSpaces = data.languageSpaces;

                    if (this.pageCount - this.currentPage < 3) {
                        this.paginationLimitBefore += 3 - (this.pageCount - this.currentPage);
                    }

                    if (this.currentPage < 4) {
                        this.paginationLimitAfter += 4 - this.currentPage;
                    }

                    if (this.filters.text == 'anytext') {
                        this.filters.text = '';
                    }
                });
            },
            openExportDialog() {
                this.vocabularyExportDialog.active = true;
            },
            openImportDialog() {
                this.vocabularyImportDialog.active = true;
            },
            exportToCsv(fields) {
                var text = 'anytext';
                if (this.filters.text !== '') {
                    text = this.filters.text;
                }

                axios.post('/vocabulary/export-to-csv', {
                    fields: fields,
                    text: text,
                    stage: parseInt(this.filters.stage),
                    book: parseInt(this.filters.book),
                    chapter: parseInt(this.filters.chapter),
                    translation: this.filters.translation,
                    phrases: this.filters.phrases,
                    orderBy: this.filters.orderBy
                }).then((response) => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'vocabulary.csv');
                    document.body.appendChild(link);
                    link.click();
                });
            },
            editItem(itemId, itemType) {
                this.vocabularyEditDialog.active = true;
                this.vocabularyEditDialog.itemId = itemId;
                this.vocabularyEditDialog.itemType = itemType;
            },
            toggleFilter(newItem) {
                if (this.visiblePopup == newItem) {
                    this.visiblePopup = '';
                } else {
                    this.visiblePopup = newItem;
                }
            },
            applyFilter(filter, newValue = -1, newBookIndex = -1) {
                // text is a v-model, while other
                // filters are buttons, so they need
                // need params to transfer value
                if (filter !== 'text') {
                    this.filters[filter] = newValue;
                }

                if (filter == 'book') {
                    this.filters.chapter = -1;
                    this.filters.bookIndex = newBookIndex;
                }
                
                var text = 'anytext';
                if (this.filters.text !== '') {
                    text = encodeURI(this.filters.text);
                }
                
                var url = '/vocabulary/search' 
                    + '/' + text
                    + '/' + this.filters.stage
                    + '/' + this.filters.book
                    + '/' + this.filters.chapter
                    + '/' + encodeURI(this.filters.translation)
                    + '/' + encodeURI(this.filters.phrases)
                    + '/' + encodeURI(this.filters.orderBy) 
                    + '/1';

                if(this.$router.currentRoute.path !== url) {
                    this.$router.push(url);
                }
            },
            moveToPage(page) {
                // text is a v-model, while other
                // filters are buttons, so they need
                // need params to transfer value

                var text = 'anytext';
                if (this.filters.text !== '') {
                    text = encodeURI(this.filters.text);
                }
                
                var url = '/vocabulary/search' 
                    + '/' + text
                    + '/' + this.filters.stage
                    + '/' + this.filters.book
                    + '/' + this.filters.chapter
                    + '/' + encodeURI(this.filters.translation)
                    + '/' + encodeURI(this.filters.phrases)
                    + '/' + encodeURI(this.filters.orderBy)
                    + '/' + page;

                if(this.$router.currentRoute.path !== url) {
                    this.$router.push(url);
                }
            }
        }
    }
</script>
