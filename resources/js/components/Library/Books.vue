<template>
    <v-container id="books" :class="{'cover-layout': layout == 'cover', 'book-opened': anyChapterVisible}">
        <!-- Error dialog -->
        <error-dialog
            v-if="errorDialog.active"
            v-model="errorDialog.active" 
            content="An error has occurred while deleting the book."
        ></error-dialog>

        <!-- Review dialog -->
        <start-review-dialog 
            v-model="startReviewDialog.visible" 
            :book-id="startReviewDialog.bookId" 
            :book-name="startReviewDialog.bookName"
        ></start-review-dialog>

        <!-- Import dialog -->
        <import-dialog
            v-if="importDialog.active"
            v-model="importDialog.active"
            :language="$props.language"
            @import-finished="importFinished"
        ></import-dialog>

        <!-- Edit or add book dialog -->
        <edit-book-dialog
            v-if="editBookDialog.active"
            v-model="editBookDialog.active" 
            :book-id="editBookDialog.bookId"
            :book-name="editBookDialog.bookName"
            :book-cover="editBookDialog.bookCover"
            @book-saved="loadBooks"
        ></edit-book-dialog>

        <!-- Delete book dialog -->
        <delete-book-dialog
            v-if="deleteBookDialog.active"
            v-model="deleteBookDialog.active" 
            :book-id="deleteBookDialog.bookId"
            :book-name="deleteBookDialog.bookName"
            @confirm="deleteBook"
        ></delete-book-dialog>

        <!-- Edit book chapter dialog for adding new chapter -->
        <edit-book-chapter-dialog
            v-if="editBookChapterDialog.active"
            v-model="editBookChapterDialog.active" 
            :book-id="editBookChapterDialog.bookId"
            :chapter-id="-1"
            @chapter-saved="chapterAdded"
        ></edit-book-chapter-dialog>

        
        <!-- Toolbar -->
        <div id="toolbar" class="d-flex mx-auto mt-6 mb-2">
              <v-menu offset-y class="rounded-lg">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn :color="theme == 'eink' ? 'white' : ''" rounded depressed v-bind="attrs" v-on="on">
                            Layout
                            <v-icon v-if="attrs['aria-expanded'] === 'true'">mdi-chevron-up</v-icon>
                            <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                        </v-btn>
                    </template>
                    <v-btn 
                        class="menu-button justify-start" 
                        tile 
                        color="white"
                        @click="layout = 'cover';"
                    >
                        <v-icon class="mr-1">mdi-view-module</v-icon>
                        Cover only
                    </v-btn>
                    <v-btn 
                        class="menu-button justify-start" 
                        tile 
                        color="white"
                        @click="layout = 'detailed';"
                    >
                        <v-icon class="mr-1">mdi-view-agenda</v-icon>
                        Detailed
                    </v-btn>
                </v-menu>

                <v-spacer></v-spacer>
                <v-menu offset-y class="rounded-lg">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn class="library-small-screen" :color="theme == 'eink' ? 'white' : ''" rounded depressed v-bind="attrs" v-on="on">
                            Library
                            <v-icon v-if="attrs['aria-expanded'] === 'true'">mdi-chevron-up</v-icon>
                            <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                        </v-btn>
                    </template>
                    <v-btn 
                        class="menu-button justify-start" 
                        tile 
                        color="white"
                        @click="showEditBookDialog(null)"
                    >
                        <v-icon class="mr-1">mdi-book-plus</v-icon>
                        Create book
                    </v-btn>
                    <v-btn 
                        class="menu-button justify-start" 
                        tile 
                        color="white"
                        @click="importDialog.active = true;"
                    >
                        <v-icon class="mr-1">mdi-import</v-icon>
                        Import
                    </v-btn>
                </v-menu>

                <v-btn 
                    rounded 
                    class="library-large-screen mx-0" 
                    color="primary" 
                    @click="showEditBookDialog(null)"
                >
                    <v-icon class="mr-1">mdi-book-plus</v-icon>Create book
                </v-btn>
                <v-btn 
                    rounded 
                    class="library-large-screen ml-2" 
                    color="primary" 
                    @click="importDialog.active = true;"
                >
                    <v-icon class="mr-1">mdi-import</v-icon>Import
                </v-btn>
        </div>

        <!-- Book list -->
        <div id="book-list" :class="{'cover-layout': layout == 'cover', 'chapters-visible': anyChapterVisible}">
            <v-card
                outlined 
                :id="'book-' + book.id"
                :class="{
                    'book': true, 
                    'cover-layout': layout == 'cover',
                    'rounded-lg': true,
                    'mx-auto': layout == 'detailed',
                    'my-6': layout == 'detailed',
                    'ma-2': layout == 'cover',
                    'hidden': anyChapterVisible && !book.chaptersVisible,
                    'chapters-visible': book.chaptersVisible
                }"
                v-for="(book, index) in books" 
                :key="index"
            >
                <div class="book-box">
                    <!-- Cover image -->
                    <div class="cover-image-box">
                        <img 
                            class="cover-image" 
                            :src="'/images/book_images/' + book.cover_image"
                            @click="openBook(book.id)"
                        ></img>
                    </div>
                    
                    <!-- Title bar -->
                    <v-card-text class="book-information pa-0 pl-3">
                        <v-card-title class="book-title pa-3">
                            {{ book.name }}
                            <v-spacer></v-spacer>
                            <v-btn icon @click.stop="toggleChapters(book.id)" v-if="book.chaptersVisible"><v-icon>mdi-arrow-left</v-icon></v-btn>
                            <v-menu content-class="book-menu" rounded offset-y bottom left nudge-top="-5">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn icon v-bind="attrs" v-on="on"><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                                </template>
                                <v-btn class="menu-button" tile color="white" @click="loadBookWordCounts(book.id, index)">Load word counts</v-btn>
                                <v-btn class="menu-button" tile color="white" @click="showEditBookDialog(book)">Edit</v-btn>
                                <v-btn class="menu-button" tile color="white" @click="showStartReviewDialog(book.id, book.name)">Review</v-btn>
                                <v-btn class="menu-button" tile color="white" @click="showDeleteBookDialog(book)">Delete</v-btn>
                            </v-menu>
                        </v-card-title>

                        <!-- Word counts loading animation -->
                        <div class="book-info-not-loaded-box mb-1" v-if="book.wordCount === null">
                            <template v-if="book.wordCountLoading">
                                <v-progress-circular indeterminate color="primary" />
                            </template>
                        </div>

                        <!-- Word counts -->
                        <v-simple-table dense class="book-info-table no-hover pb-4  mx-auto" v-if="book.wordCount !== null">
                            <tbody>
                                <tr>
                                    <td width="200px">Total words</td>
                                    <td class="text-center"><div class="info-table-value">{{ formatNumber(book.wordCount.total) }}</div></td>
                                </tr>
                                <tr>
                                    <td width="200px">Unique words</td>
                                    <td class="text-center"><div class="info-table-value">{{ formatNumber(book.wordCount.unique) }}</div></td>
                                </tr>
                                <tr>
                                    <td width="200px">Known words</td>
                                    <td class="text-center"><div class="info-table-value">{{ formatNumber(book.wordCount.known) }}</div></td>
                                </tr>
                                <tr>
                                    <td width="200px">Highlighted words</td>
                                    <td class="text-center"><div class="info-table-value highlighted-words px-2 rounded-xl">{{ formatNumber(book.wordCount.highlighted) }}</div></td>
                                </tr>
                                <tr>
                                    <td width="200px">New words</td>
                                    <td class="text-center"><div class="info-table-value new-words px-2 rounded-xl">{{ formatNumber(book.wordCount.new) }}</div></td>
                                </tr>
                            </tbody>
                        </v-simple-table>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn rounded class="mx-0" color="primary" @click="toggleChapters(book.id)" v-if="!book.chaptersVisible">Open</v-btn>
                        <v-btn rounded class="mx-0" color="primary"  v-if="book.chaptersVisible" @click="addChapter(book.id)"><v-icon> mdi-plus</v-icon>Add chapter</v-btn>
                    </v-card-actions>
                    </v-card-text>
                </div>

                <book-chapters
                    v-if="book.chaptersVisible"
                    :book-id="book.id"
                    :ref="'bookChapters'"
                ></book-chapters>
            </v-card>
        </div>
    </v-container>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                layout: 'detailed',
                theme: (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme'),
                books: [],
                anyChapterVisible: false,
                errorDialog: {
                    active: false,
                },
                importDialog: {
                    active: false,
                },
                editBookDialog: {
                    active: false,
                    bookId: -1
                },
                deleteBookDialog: {
                    active: false,
                    bookId: -1,
                    bookName: '',
                },
                editBookChapterDialog: {
                    active: false,
                    bookId: -1,
                },
                startReviewDialog: {
                    visible: false,
                    bookId: -1,
                    bookName: '',
                }
            }
        },
        props: {
            language: String
        },
        mounted() {
            this.loadBooks();
        },
        methods: {
            loadBookWordCounts(bookId, index) {
                this.books[index].wordCountLoading = true;
                this.books[index].wordCount = null;

                axios.get('/book/get-word-counts/' + bookId).then((response) => {
                    if (response.data !== 'error') {
                        this.books[index].wordCountLoading = false;
                        this.books[index].wordCount = response.data;
                    }
                });
            },
            addChapter(bookId) {
                this.editBookChapterDialog.active = true;
                this.editBookChapterDialog.bookId = bookId;
            },
            chapterAdded() {
                this.$refs.bookChapters[0].loadChapters();
            },
            showEditBookDialog(book = null) {
                this.editBookDialog.active = true;
                if (book === null) {
                    this.editBookDialog.bookId = -1;
                    this.editBookDialog.bookCover = 'default.jpg';
                    this.editBookDialog.bookName = '';
                } else {
                    this.editBookDialog.bookId = book.id;
                    this.editBookDialog.bookCover = book.cover_image;
                    this.editBookDialog.bookName = book.name;
                }
            },
            showDeleteBookDialog(book) {
                this.deleteBookDialog.active = true;
                this.deleteBookDialog.bookId = book.id;
                this.deleteBookDialog.bookName = book.name;
            },
            deleteBook() {
                axios.post('/book/delete', {
                    'bookId': this.deleteBookDialog.bookId,
                }).then((response) => {
                    if (response.data == 'success') {
                        this.loadBooks();
                    } else {
                        this.errorDialog.active = true;
                    }
                });
            },
            openBook(bookId) {
                if (!this.anyChapterVisible && this.layout == 'cover') {
                    this.toggleChapters(bookId);
                }
            },
            toggleChapters(bookId) {
                for (let bookIndex = 0; bookIndex < this.books.length; bookIndex ++) {
                    if (bookId !== this.books[bookIndex].id) {
                        this.books[bookIndex].chaptersVisible = false;
                    } else {
                        this.anyChapterVisible = !this.books[bookIndex].chaptersVisible;
                        this.books[bookIndex].chaptersVisible = !this.books[bookIndex].chaptersVisible;
                        setTimeout(() => {
                            document.getElementById('book-' + bookId).scrollIntoView();
                        }, 500);
                    }
                }
            },
            showStartReviewDialog(bookId, bookName) {
                this.startReviewDialog.bookName = bookName;
                this.startReviewDialog.bookId = bookId;
                this.startReviewDialog.visible = true;
            },
            importFinished() {
                this.importDialog.active = false;
                this.loadBooks();
            },
            loadBooks() {
                axios.post('/books').then((response) => {
                    this.anyChapterVisible = false;
                    for (let bookIndex = 0; bookIndex < response.data.length; bookIndex ++) {
                        response.data[bookIndex].chaptersVisible = false;
                        response.data[bookIndex].wordCountLoading = false;
                    }

                    this.books = response.data;
                });
            },
            formatNumber: formatNumber
        }
    }
</script>
