<template>
    <div id="vocabulary" @click="visiblePopup = ''">
        <!-- search filter -->
        <div id="vocabulary-search-field">
            <button class="btn btn-secondary "><i class="fa fa-search"></i> Search</button>
            <input class="" type="text" placeholder="Search term">
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
                        <div class="popup-button" @click="applyFilter('stage', 'any')">Any</div>
                        <div class="popup-button" @click="applyFilter('stage', 2)">New</div>
                        <div class="popup-button" @click="applyFilter('stage', 1)">Ignored</div>
                        <div class="popup-button" @click="applyFilter('stage', 0)">Learned</div>
                        <div class="popup-button" @click="applyFilter('stage', -1)">1</div>
                        <div class="popup-button" @click="applyFilter('stage', -2)">2</div>
                        <div class="popup-button" @click="applyFilter('stage', -3)">3</div>
                        <div class="popup-button" @click="applyFilter('stage', -4)">4</div>
                        <div class="popup-button" @click="applyFilter('stage', -5)">5</div>
                        <div class="popup-button" @click="applyFilter('stage', -6)">6</div>
                        <div class="popup-button" @click="applyFilter('stage', -7)">7</div>
                        
                    </div>
                </div>
            </div>

            <!-- book filter -->
            <div class="vocabulary-filter book">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('book')">
                        Book 
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'book'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'book'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'book'}">
                        <div class="popup-button" @click="applyFilter('book', 'any', -1)">Any</div>
                        <div class="popup-button" v-for="(book, index) in _books" :key="index" @click="applyFilter('book', book.id, index)">{{ book.name }}</div>
                    </div>
                </div>
            </div>

            <!-- chapter filter -->
            <div class="vocabulary-filter chapter" v-if="filters.book !== 'any'">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('chapter')">
                        Chapter 
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'chapter'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'chapter'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'chapter'}">
                        <div class="popup-button" @click="applyFilter('chapter', 'any')">Any</div>
                        <div class="popup-button" v-for="(chapter, index) in _books[filters.bookIndex].chapters" :key="index" @click="applyFilter('chapter', chapter.id)">{{ chapter.name }}</div>
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
                        <div class="popup-button" @click="applyFilter('translation', 'any')">Any</div>
                        <div class="popup-button" @click="applyFilter('translation', 'not empty')">Not empty</div>
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
                        <div class="popup-button" @click="applyFilter('phrases', 'both')">Both</div>
                        <div class="popup-button" @click="applyFilter('phrases', 'only words')">Only words</div>
                        <div class="popup-button" @click="applyFilter('phrases', 'only phrases')">Only phrases</div>
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
                        <div class="popup-button" @click="applyFilter('orderBy', 'words')"><i class="fa fa-sort-alpha-down"></i> Word</div>
                        <div class="popup-button" @click="applyFilter('orderBy', 'words-desc')"><i class="fa fa-sort-alpha-down-alt"></i> Word</div>
                        <div class="popup-button" @click="applyFilter('orderBy', 'stage')"><i class="fa fa-sort-numeric-down"></i> Stage</div>
                        <div class="popup-button" @click="applyFilter('orderBy', 'stage-desc')"><i class="fa fa-sort-numeric-down-alt"></i> Stage</div>
                        
                    </div>
                </div>
            </div>

            <!-- import -->
            <div class="vocabulary-filter import">
                <div class="vocabulary-filter-box">
                    <span @click.stop="toggleFilter('import')">
                        <i class="fa fa-file-import"></i> Import
                        <i class="fa fa-angle-down" v-if="visiblePopup !== 'import'"></i>
                        <i class="fa fa-angle-up" v-if="visiblePopup == 'import'"></i>
                    </span>
                    <div :class="{'filter-popup': true, 'visible': visiblePopup == 'import'}">
                        <div class="popup-button">Excel</div>
                        <div class="popup-button">Csv</div>
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
                        <div class="popup-button">Excel</div>
                        <div class="popup-button">Csv</div>
                    </div>
                </div>
            </div>

            <!-- search result info -->
            <div class="vocabulary-filter search-result-info">
                {{ _words.length }} words found
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

        <div id="vocabulary-list">
            <div class="vocabulary-line header">
                <div class="word">Word</div>
                <div class="reading">Reading</div>
                <div class="word-with-reading">Word</div>
                <div class="stage">Stage</div>
                <div class="translations">Definitions</div>
                <div class="actions">Options</div>
            </div>    

            <div class="vocabulary-line" v-for="(word, index) in _words" :key="index">
                <div class="word">{{ word.word }}</div>
                <div class="reading">{{ word.reading }}</div>
                <div class="word-with-reading"><ruby>{{ word.word }}<rt>{{ word.reading }}</rt></ruby></div>
                <div class="stage" :stage="word.stage"><span>{{ word.stage < 0 ? word.stage * -1 : word.stage }}</span></div>
                <div class="translations">{{ word.translation }}</div>
                <div class="actions">
                    <i class="fa fa-ellipsis-h" @click.stop="toggleFilter('word' + index)"></i>
                        <div :class="{'vocabulary-popup': true, 'visible': visiblePopup == ('word' + index)}">
                            <div class="popup-button"><i class="fa fa-pen"></i> Edit</div>
                        </div>
                </div>
            </div>
        </div>

        <!-- search result info -->
        <div id="small-screen-search-result-info">
            {{ _words.length }} words found
        </div>

        <div id="vocabulary-pagination">
            <!-- normal pagination -->
            <div class="pagination-button" v-if="_currentPage > 4">1</div>
            <div class="pagination-button dots" v-if="_currentPage > 5">...</div>
            <template v-for="(page, index) in _pageCount">
                <div :class="{'pagination-button': true, 'selected': page == _currentPage}" :key="index" v-if="page >= _currentPage - paginationLimitBefore && page <= _currentPage + paginationLimitAfter">{{ page }}</div>
            </template>
            <div class="pagination-button dots" v-if="_currentPage < _pageCount - 4">...</div>
            <div class="pagination-button" v-if="_currentPage < _pageCount - 3">{{ _pageCount }}</div>

            <!-- pagination below 500px width -->
            <div class="pagination-button basic first">First</div>
            <div class="pagination-button basic" v-if="_currentPage > 0">{{ _currentPage - 1 }}</div>
            <div class="pagination-button basic selected">{{ _currentPage }}</div>
            <div class="pagination-button basic" v-if="_currentPage < _pageCount - 1">{{ _currentPage + 1 }}</div>
            <div class="pagination-button basic last">Last</div>
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
                
                filters: {
                    bookIndex: -1,
                    stage: 'any',
                    book: 'any',
                    chapter: 'any',
                    translation: 'any',
                    phrases: 'both',
                    orderBy: 'word'
                }
            }
        },
        props: {
            _words: Array,
            _books: Array,
            _pageCount: Number,
            _currentPage: Number
        },
        mounted() {
            document.getElementById('app').addEventListener('scroll', () => { this.visiblePopup = '' });
            if (this._pageCount - this._currentPage < 3) {
                this.paginationLimitBefore += 3 - (this._pageCount - this._currentPage);
            }

            if (this._currentPage < 4) {
                this.paginationLimitAfter += 4 - this._currentPage;
            }
        },
        methods: {
            toggleFilter: function(newItem) {
                if (this.visiblePopup == newItem) {
                    this.visiblePopup = '';
                } else {
                    this.visiblePopup = newItem;
                }
            },
            applyFilter: function(filter, newValue, newBookIndex = -1) {
                this.filters[filter] = newValue;

                if (filter == 'book') {
                    this.filters.chapter = 'any';
                    this.filters.bookIndex = newBookIndex;
                }

                console.log(this.filters);
            }
        }
    }
</script>
