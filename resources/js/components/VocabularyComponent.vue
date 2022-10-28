<template>
    <div id="vocabulary">
        <!-- search filter -->
        <div id="vocabulary-search-field">
            <button class="btn btn-secondary" @click="applyFilter('text')"><i class="fa fa-search"></i> Search</button>
            <input class="" type="text" placeholder="Search term" v-model="filters.text" @keyup.enter="applyFilter('text')">
        </div>

        <!-- filters -->
        <div id="vocabulary-filters" :class="{'filters-hidden': filtersHidden}">
            <!-- stage filter -->
            <div class="vocabulary-filter stage">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('stage')">
                        Stage 
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'stage'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'stage'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'stage'}">
                        <div :class="{'popup-button': true, 'selected': filters.stage == 'any'}" @click="applyFilter('stage', 'any')">Any</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == 2}" @click="applyFilter('stage', 2)">New</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == 1}" @click="applyFilter('stage', 1)">Ignored</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == 0}" @click="applyFilter('stage', 0)">Learned</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == -1}" @click="applyFilter('stage', -1)">1</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == -2}" @click="applyFilter('stage', -2)">2</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == -3}" @click="applyFilter('stage', -3)">3</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == -4}" @click="applyFilter('stage', -4)">4</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == -5}" @click="applyFilter('stage', -5)">5</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == -6}" @click="applyFilter('stage', -6)">6</div>
                        <div :class="{'popup-button': true, 'selected': filters.stage == -7}" @click="applyFilter('stage', -7)">7</div>
                        
                    </div>
                </div>
            </div>

            <!-- book filter -->
            <div class="vocabulary-filter book" v-if="books.length">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('book')">
                        Book 
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'book'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'book'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'book'}">
                        <div :class="{'popup-button': true, 'selected': filters.book == 'any'}" @click="applyFilter('book', 'any', -1)">Any</div>
                        <div :class="{'popup-button': true, 'selected': filters.book == book.id}" v-for="(book, index) in books" :key="index" @click="applyFilter('book', book.id, index)">{{ book.name }}</div>
                    </div>
                </div>
            </div>

            <!-- chapter filter -->
            <div class="vocabulary-filter chapter" v-if="filters.bookIndex !== -1">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('chapter')">
                        Chapter 
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'chapter'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'chapter'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'chapter'}">
                        <div :class="{'popup-button': true, 'selected': filters.chapter == 'any'}" @click="applyFilter('chapter', 'any')">Any</div>
                        <div :class="{'popup-button': true, 'selected': filters.chapter == chapter.id}" v-for="(chapter, index) in books[filters.bookIndex].chapters" :key="index" @click="applyFilter('chapter', chapter.id)">{{ chapter.name }}</div>
                    </div>
                </div>
            </div>

            <!-- translation filter -->
            <div class="vocabulary-filter translation">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('translation')">
                        Translation 
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'translation'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'translation'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'translation'}">
                        <div :class="{'popup-button': true, 'selected': filters.translation == 'any'}" @click="applyFilter('translation', 'any')">Any</div>
                        <div :class="{'popup-button': true, 'selected': filters.translation == 'not empty'}" @click="applyFilter('translation', 'not empty')">Not empty</div>
                    </div>
                </div>
            </div>

            <!-- phrases filter -->
            <div class="vocabulary-filter phrases">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('phrases')">
                        Phrases 
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'phrases'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'phrases'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'phrases'}">
                        <div :class="{'popup-button': true, 'selected' : filters.phrases == 'both'}" @click="applyFilter('phrases', 'both')">Both</div>
                        <div :class="{'popup-button': true, 'selected' : filters.phrases == 'only words'}" @click="applyFilter('phrases', 'only words')">Only words</div>
                        <div :class="{'popup-button': true, 'selected' : filters.phrases == 'only phrases'}" @click="applyFilter('phrases', 'only phrases')">Only phrases</div>
                    </div>
                </div>
            </div>

            <!-- order by filter -->
            <div class="vocabulary-filter order-by">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('order-by')">
                        Order by 
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'order-by'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'order-by'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'order-by'}">
                        <div :class="{'popup-button': true, 'selected' : filters.orderBy == 'words'}" @click="applyFilter('orderBy', 'words')"><i class="fa fa-sort-alpha-down"></i> Word</div>
                        <div :class="{'popup-button': true, 'selected' : filters.orderBy == 'words desc'}" @click="applyFilter('orderBy', 'words desc')"><i class="fa fa-sort-alpha-down-alt"></i> Word</div>
                        <div :class="{'popup-button': true, 'selected' : filters.orderBy == 'stage'}" @click="applyFilter('orderBy', 'stage')"><i class="fa fa-sort-numeric-down"></i> Stage</div>
                        <div :class="{'popup-button': true, 'selected' : filters.orderBy == 'stage desc'}" @click="applyFilter('orderBy', 'stage desc')"><i class="fa fa-sort-numeric-down-alt"></i> Stage</div>
                    </div>
                </div>
            </div>

            <!-- export -->
            <div class="vocabulary-filter export">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('export')">
                        <i class="fa fa-file-download"></i> Export
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'export'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'export'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'export'}">
                        <div :class="{'popup-button': true}">Excel</div>
                        <div :class="{'popup-button': true}">Csv</div>
                    </div>
                </div>
            </div>

            <!-- search result info -->
            <div class="vocabulary-filter search-result-info">
                {{ wordCount }} words found
            </div>

            <!-- show / hide filters -->
            <div class="vocabulary-filter show-filters">
                <div class="vocabulary-filter-box">
                    <span @click.stop="filtersHidden = !filtersHidden" v-if="filtersHidden">
                        <i class="fa fa-filter"></i> Show filters
                    </span>

                    <span @click.stop="filtersHidden = !filtersHidden" v-if="!filtersHidden">
                        <i class="fa fa-filter"></i> Hide filters
                    </span>
                </div>
            </div>
        </div>

        <!-- vocabulary list -->
        <div id="vocabulary-list">
            <div class="vocabulary-line header">
                <div class="word">Word</div>
                <div class="reading">Reading</div>
                <div class="word-with-reading">Word</div>
                <div class="stage">Stage</div>
                <div class="translations">Definitions</div>
                <div class="actions">Options</div>
            </div>    

            <div class="vocabulary-line" v-for="(word, index) in words" :key="index">
                <div class="word">{{ word.word }}</div>
                <div class="reading">{{ word.reading }}</div>
                <div class="word-with-reading"><ruby>{{ word.word }}<rt>{{ word.reading }}</rt></ruby></div>
                
                <div class="stage" :stage="word.stage" v-if="word.stage < 0"><span>{{ word.stage * -1 }}</span></div>
                <div class="stage" :stage="word.stage" v-if="word.stage == 0"><span>0</span></div>
                <div class="stage" :stage="word.stage" v-if="word.stage == 1"><span>X</span></div>
                <div class="stage" :stage="word.stage" v-if="word.stage == 2"><span>New</span></div>

                <div class="translations">{{ word.translation }}</div>
                <div class="actions">
                    <i class="fa fa-ellipsis-h" @click.stop="toggleFilter('word' + index)"></i>
                        <div :class="{'vocabulary-popup': true, 'visible': visiblePopup == ('word' + index)}">
                            <div :class="{'popup-button': true}"><i class="fa fa-pen"></i> Edit</div>
                        </div>
                </div>
            </div>
        </div>

        <!-- search result info -->
        <div id="small-screen-search-result-info">
            {{ wordCount }} words found
        </div>

        <div id="vocabulary-pagination">
            <!-- normal pagination -->
            <div class="pagination-button" v-if="currentPage > 4" @click="moveToPage(1)">1</div>
            <div class="pagination-button dots" v-if="currentPage > 5">...</div>
            <template v-for="(page, index) in pageCount">
                <div :class="{'pagination-button': true, 'selected': page == currentPage}" :key="index" v-if="page >= currentPage - paginationLimitBefore && page <= currentPage + paginationLimitAfter" @click="moveToPage(page)">{{ page }}</div>
            </template>
            <div class="pagination-button dots" v-if="currentPage < pageCount - 4">...</div>
            <div class="pagination-button" v-if="currentPage < pageCount - 3" @click="moveToPage(pageCount)">{{ pageCount }}</div>

            <!-- pagination below 500px width -->
            <div class="pagination-button basic first" @click="moveToPage(1)">First</div>
            <div class="pagination-button basic" v-if="currentPage > 0" @click="moveToPage(currentPage - 1)">{{ currentPage - 1 }}</div>
            <div class="pagination-button basic selected" @click="moveToPage(currentPage)">{{ currentPage }}</div>
            <div class="pagination-button basic" v-if="currentPage < pageCount - 1" @click="moveToPage(currentPage + 1)">{{ currentPage + 1 }}</div>
            <div class="pagination-button basic last" @click="moveToPage(pageCount)">Last</div>
        </div>
    </div>
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
                chapters: [],
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
                    text: 'anytext',
                }
            }
        },
        props: {
        },
        mounted() {
            document.getElementById('app').addEventListener('scroll', () => { this.visiblePopup = ''; });
            document.getElementById('app').addEventListener('click', () => { this.visiblePopup = ''; });
            
            if (this.$route.params.text !== undefined) {
                this.filters.text = this.$route.params.text;
                this.filters.stage = this.$route.params.stage;
                this.filters.book = this.$route.params.book;
                this.filters.chapter = this.$route.params.chapter;
                this.filters.translation = this.$route.params.translation;
                this.filters.phrases = this.$route.params.phrases;
                this.filters.orderBy = this.$route.params.orderBy;
                this.currentPage = this.$route.params.page;
            }

            axios.post('/vocabulary/search', {
                text: this.filters.text,
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
