    <template>
        <div>
        <v-card outlined class="book opened detailed rounded-lg mx-auto my-6">
            <!-- Edit book chapter dialog for adding new chapter -->
            <edit-book-chapter-dialog
                v-if="editBookChapterDialog.active"
                v-model="editBookChapterDialog.active"
                :book-id="editBookChapterDialog.bookId"
                :chapter-id="editBookChapterDialog.chapterId"
                @chapter-saved="chapterSaved"
            ></edit-book-chapter-dialog>
            
            <div class="book-box">
                <!-- Cover image -->
                <div class="cover-image-box">
                    <img 
                        class="cover-image" 
                        :src="'/images/book_images/' + book.cover_image"
                    ></img>
                </div>
                
                <!-- Title bar -->
                <v-card-text class="book-information pa-0 pl-3">
                    <v-card-title class="book-title pa-3">
                        <div class="book-title-text default-font">{{ book.name }}</div>
                        <v-spacer></v-spacer>
                        <v-btn icon @click.stop="closeBook"><v-icon>mdi-arrow-left</v-icon></v-btn>
                        <v-menu content-class="book-menu" rounded offset-y bottom left nudge-top="-5">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn icon v-bind="attrs" v-on="on"><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                            </template>
                            <v-btn class="menu-button" tile color="white" @click="loadBookWordCounts()">Load word counts</v-btn>
                            <v-btn class="menu-button" tile color="white" @click="showEditBookDialog()">Edit</v-btn>
                            <v-btn class="menu-button" tile color="white" @click="showStartReviewDialog()">Review</v-btn>
                            <v-btn class="menu-button" tile color="white" @click="showDeleteBookDialog()">Delete</v-btn>
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
                                <td class="text-center">
                                    <div class="info-table-value">
                                        <template v-if="wordCountDisplayType == 0">
                                            {{ formatNumber(book.wordCount.known) }}
                                        </template>
                                        <template v-else>
                                            {{ (book.wordCount.known / book.wordCount.unique * 100).toFixed(1) }}%
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td width="200px">Highlighted words</td>
                                <td class="text-center">
                                    <div class="info-table-value highlighted-words px-2 rounded-xl">
                                        <template v-if="wordCountDisplayType == 0">
                                            {{ formatNumber(book.wordCount.highlighted) }}
                                        </template>
                                        <template v-else>
                                            {{ (book.wordCount.highlighted / book.wordCount.unique * 100).toFixed(1) }}%
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td width="200px">New words</td>
                                <td class="text-center">
                                    <div class="info-table-value new-words px-2 rounded-xl">
                                        <template v-if="wordCountDisplayType == 0">
                                            {{ formatNumber(book.wordCount.new) }}
                                        </template>
                                        <template v-else>
                                            {{ (book.wordCount.new / book.wordCount.unique * 100).toFixed(1) }}%
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn rounded class="mx-0" color="primary" @click="addChapter"><v-icon> mdi-plus</v-icon>Add chapter</v-btn>
                    </v-card-actions>
                </v-card-text>
            </div>
        </v-card>

        <!-- Chapters -->
        <v-card outlined class="book opened detailed rounded-lg mx-auto my-6">
            <v-card-title class="book-title pa-3">
                Chapters
                <v-spacer />
                <v-btn-toggle 
                    v-model="wordCountDisplayType" 
                    mandatory 
                    rounded 
                    dense
                >
                    <v-btn small class="px-1" min-width="40px">
                        <v-icon small>mdi-numeric</v-icon>
                    </v-btn>
                    <v-btn small class="px-1" min-width="40px">
                        <v-icon small>mdi-percent</v-icon>
                    </v-btn>
                </v-btn-toggle>
            </v-card-title>
            <v-card-text>
                <book-chapters
                    ref="bookChapters"
                    :book-id="book.id"
                    :word-count-display-type="wordCountDisplayType"
                    @chapter-saved="chapterSaved"
                ></book-chapters>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                wordCountDisplayType: 0,
                editBookChapterDialog: {
                    active: false,
                    bookId: -1,
                    chapterId: -1,
                },
            }
        },
        props: {
            book: Object
        },
        mounted() {
            this.loadBookWordCounts();
        },
        methods: {
            addChapter() {
                this.editBookChapterDialog.active = true;
                this.editBookChapterDialog.bookId = this.book.id;
                this.editBookChapterDialog.chapterId = -1;
            },
            chapterSaved() {
                this.loadBookWordCounts();
                this.$refs.bookChapters.loadChapters();
            },
            loadBookWordCounts() {
                this.book.wordCountLoading = true;
                this.book.wordCount = null;

                axios.get('/books/get-word-counts/' + this.book.id).then((response) => {
                    if (response.data !== 'error') {
                        this.book.wordCountLoading = false;
                        this.book.wordCount = response.data;
                    }
                });
            },
            showEditBookDialog() {
                this.$emit('show-edit-book-dialog', this.book);
            },
            showDeleteBookDialog() {
                this.$emit('show-delete-book-dialog', this.book);
            },
            showStartReviewDialog() {
                this.$emit('show-start-review-dialog', this.book.id, this.book.name);
            },
            closeBook() {
                this.$emit('close-book');
            },
            formatNumber: formatNumber
        }
    }
</script>
