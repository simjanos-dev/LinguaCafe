<template>
    <v-container id="vocabulary">
        <!-- search filter -->
        <div id="vocabulary-search-field">
            <v-btn rounded depressed color="primary" @click="applyFilter('text')"><v-icon>mdi-magnify</v-icon> Search</v-btn>
            <v-text-field rounded outlined dense color="primary" label="Search term" v-model="filters.text" @keyup.enter="applyFilter('text')"></v-text-field>
        </div>

        <!-- filters -->
        <v-container fluid>
            <v-row id="filters" :class="{'hidden': filtersHidden}">
                <!-- Stage filter -->
                <v-menu offset-y>
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn class="filter-menu px-1" plain v-bind="attrs" v-on="on">
                            Stage
                            <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                            <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                        </v-btn>
                    </template>
                    <v-list class="filter-popup pa-0" dense>
                        <v-list-item-group color="primary">
                            <v-list-item :class="{'v-list-item--active': filters.stage == 'any'}" @click="applyFilter('stage', 'any')">Any</v-list-item>
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
                        <v-btn class="filter-menu px-1" plain v-bind="attrs" v-on="on">
                            Book
                            <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                            <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                        </v-btn>
                    </template>
                    <v-list class="filter-popup pa-0" dense>
                        <v-list-item-group color="primary">
                            <v-list-item :class="{'v-list-item--active': filters.book == 'any'}" @click="applyFilter('book', 'any', -1)">Any</v-list-item>
                            <v-list-item 
                                v-for="(book, index) in books" :key="index"
                                :class="{'v-list-item--active': filters.book == book.id}"
                                @click="applyFilter('book', book.id, index)">{{ book.name }}</v-list-item>
                            </v-list-item>
                        </v-list-item-group>
                    </v-list>
                </v-menu>

                <!-- Chapter filter -->
                <v-menu offset-y v-if="filters.bookIndex !== -1 && books.length">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn class="filter-menu px-1" plain v-bind="attrs" v-on="on">
                            Chapter
                            <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                            <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                        </v-btn>
                    </template>
                    <v-list class="filter-popup pa-0" dense>
                        <v-list-item-group color="primary">
                            <v-list-item :class="{'v-list-item--active': filters.chapter == 'any'}" @click="applyFilter('chapter', 'any')">Any</v-list-item>
                            <v-list-item 
                                v-for="(chapter, index) in books[filters.bookIndex].chapters" :key="index"
                                :class="{'v-list-item--active': filters.chapter == chapter.id}"
                                @click="applyFilter('chapter', chapter.id, index)">{{ chapter.name }}</v-list-item>
                            </v-list-item>
                        </v-list-item-group>
                    </v-list>
                </v-menu>

                <!-- Translation filter -->
                <v-menu offset-y>
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn class="filter-menu px-1" plain v-bind="attrs" v-on="on">
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
                        <v-btn class="filter-menu px-1" plain v-bind="attrs" v-on="on">
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
                        <v-btn class="filter-menu px-1" plain v-bind="attrs" v-on="on">
                            Order by
                            <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                            <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                        </v-btn>
                    </template>
                    <v-list class="filter-popup pa-0" dense>
                        <v-list-item-group color="primary">
                            <v-list-item :class="{'v-list-item--active': filters.orderBy == 'words'}" @click="applyFilter('orderBy', 'words')"><v-icon class="mr-1">mdi-sort-alphabetical-ascending</v-icon>Word</v-list-item>
                            <v-list-item :class="{'v-list-item--active': filters.orderBy == 'words desc'}" @click="applyFilter('orderBy', 'words desc')"><v-icon class="mr-1">mdi-sort-alphabetical-descending</v-icon>Word</v-list-item>
                            <v-list-item :class="{'v-list-item--active': filters.orderBy == 'stage'}" @click="applyFilter('orderBy', 'stage')"><v-icon class="mr-1">mdi-sort-numeric-ascending</v-icon>Stage</v-list-item>
                            <v-list-item :class="{'v-list-item--active': filters.orderBy == 'stage desc'}" @click="applyFilter('orderBy', 'stage desc')"><v-icon class="mr-1">mdi-sort-numeric-descending</v-icon>Stage</v-list-item>
                        </v-list-item-group>
                    </v-list>
                </v-menu>

                <!-- Show filters -->
                <v-btn class="filter-menu show-filters px-1" plain @click="filtersHidden = !filtersHidden">
                    <v-icon small class="mr-1" v-if="filtersHidden">mdi-eye</v-icon>
                    <v-icon small class="mr-1" v-if="!filtersHidden">mdi-eye-off</v-icon>Show filters
                </v-btn>
                
                <!-- search result info -->
                <v-spacer></v-spacer>
                <div id="search-result-info" class="filter-menu">
                    {{ wordCount }} words
                </div>

                <!-- Export -->
                <v-menu offset-y>
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn class="filter-menu export px-1" plain v-bind="attrs" v-on="on">
                            <v-icon small class="mr-1">mdi-file-download</v-icon>Export
                            <v-icon v-if="attrs['aria-expanded'] === 'true' ">mdi-chevron-up</v-icon>
                            <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                        </v-btn>
                    </template>
                    <v-list class="filter-popup pa-0" dense>
                        <v-list-item-group color="primary">
                            <v-list-item><v-icon class="mr-1">mdi-file-excel</v-icon> Excel</v-list-item>
                            <v-list-item><v-icon class="mr-1">mdi-file-delimited</v-icon>Csv</v-list-item>
                        </v-list-item-group>
                    </v-list>
                </v-menu>
            </v-row>
        </v-container>

        <!-- vocabulary list -->
        <v-simple-table id="vocabulary-list" class="py-0 no-hover border rounded-lg" dense>
            <thead>
                <tr>
                    <th class="word">Word</th>
                    <th class="reading">Reading</th>
                    <th class="word-with-reading">Word</th>
                    <th class="stage px-1">Stage</th>
                    <th class="translation">Definitions</th>
                    <th class="actions">Options</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(word, index) in words" :key="index">
                    <td class="word">{{ word.word }}</td>
                    <td class="reading">{{ word.reading }}</td>                    
                    <td class="word-with-reading"><ruby>{{ word.word }}<rt>{{ word.reading }}</rt></ruby></td>
                    
                    <td class="stage px-1" :stage="word.stage" v-if="word.stage < 0">
                        <div :style="{'background-color': $vuetify.theme.currentTheme.highlightedWord}">{{ word.stage * -1 }}</div>
                    </td>
                    <td class="stage px-1" :stage="word.stage" v-if="word.stage == 0">
                        <div :style="{'background-color': $vuetify.theme.currentTheme.background}">0</div>
                    </td>
                    <td class="stage px-1" :stage="word.stage" v-if="word.stage == 1">
                        <div :style="{'background-color': $vuetify.theme.currentTheme.background}">X</div>
                    </td>
                    <td class="stage px-1" :stage="word.stage" v-if="word.stage == 2">
                        <div :style="{'background-color': $vuetify.theme.currentTheme.newWord}">New</div>
                    </td>
                    
                    <td class="translation">{{ word.translation }}</td>
                    <td class="actions"><v-btn icon><v-icon>mdi-dots-horizontal</v-icon></v-btn></td>
                </tr>
            </tbody>
        </v-simple-table>

        <div id="small-screen-search-result-info">
            {{ wordCount }} words
        </div>

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
                // filtersHidden is only for small screens
                filtersHidden: true,
                visiblePopup: '',
                paginationLimitBefore: 3,
                paginationLimitAfter: 3,
                words: [],
                wordCount: 0,
                books: [],
                pageCount: 1,
                currentPage: 1,

                filters: {
                    bookIndex: -1,
                    stage: 'any',
                    book: 'any',
                    chapter: 'any',
                    translation: 'any',
                    phrases: 'both',
                    orderBy: 'words',
                    text: '',
                }
            }
        },
        props: {
        },
        mounted() {
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

            axios.post('/vocabulary/search', {
                text: (this.filters.text == '') ? 'anytext' : this.filters.text,
                book: this.filters.book,
                chapter: this.filters.chapter,
                stage: this.filters.stage,
                translation: this.filters.translation,
                phrases: this.filters.phrases,
                orderBy: this.filters.orderBy,
                page: this.currentPage,
            }).then(function (response) {
                var data = response.data;
                this.filters.bookIndex = data.bookIndex;
                this.words = data.words;
                this.books = data.books;
                this.pageCount = data.pageCount;
                this.currentPage = parseInt(data.currentPage);
                this.wordCount = data.wordCount;

                if (this.pageCount - this.currentPage < 3) {
                    this.paginationLimitBefore += 3 - (this.pageCount - this.currentPage);
                    console.log(this.pageCount, this.currentPage);
                }

                if (this.currentPage < 4) {
                    this.paginationLimitAfter += 4 - this.currentPage;
                }

                if (this.filters.text == 'anytext') {
                    this.filters.text = '';
                }

            }.bind(this)).catch(function (error) {}).then(function () {});
        },
        methods: {
            toggleFilter: function(newItem) {
                if (this.visiblePopup == newItem) {
                    this.visiblePopup = '';
                } else {
                    this.visiblePopup = newItem;
                }
            },
            applyFilter: function(filter, newValue = -1, newBookIndex = -1) {
                // text is a v-model, while other
                // filters are buttons, so they need
                // need params to transfer value
                if (filter !== 'text') {
                    this.filters[filter] = newValue;
                }

                if (filter == 'book') {
                    this.filters.chapter = 'any';
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
