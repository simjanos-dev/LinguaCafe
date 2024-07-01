<template>
    <v-container class="book-chapters py-0">
        <!-- Error dialog -->
        <error-dialog
            v-if="errorDialog.active"
            v-model="errorDialog.active" 
            content="An error has occurred while deleting the chapter."
        ></error-dialog>

        <!-- Edit book chapter dialog -->
        <edit-book-chapter-dialog
            v-if="editBookChapterDialog.active"
            v-model="editBookChapterDialog.active" 
            :book-id="$props.bookId"
            :chapter-id="editBookChapterDialog.chapterId"
            @chapter-saved="chapterSaved"
        >
        </edit-book-chapter-dialog>

        <!-- Delete book chapter dialog -->
        <delete-book-chapter-dialog
            v-if="deleteBookChapterDialog.active"
            v-model="deleteBookChapterDialog.active" 
            :chapter-id="deleteBookChapterDialog.chapterId"
            :chapter-name="deleteBookChapterDialog.chapterName"
            @confirm="deleteChapter"
        >
        </delete-book-chapter-dialog>
        
        <!-- Review dialog -->
        <start-review-dialog 
            v-model="startReviewDialog.active" 
            :book-id="startReviewDialog.bookId" 
            :book-name="startReviewDialog.bookName"
            :chapter-id="startReviewDialog.chapterId" 
            :chapter-name="startReviewDialog.chapterName">
        </start-review-dialog>
        

        <!-- Chapter list -->
        <v-data-table
            class="my-4 mb-0 no-hover"
            :headers="[
                { text: 'Chapter', value: 'name'},
                { text: 'Total', value: 'wordCount.total', align: 'center' },
                { text: 'Unique', value: 'wordCount.unique', align: 'center' },
                { text: 'Known', value: 'wordCount.known', align: 'center' },
                { text: 'Highlighted', value: 'wordCount.highlighted', align: 'center' },
                { text: 'New', value: 'wordCount.new', align: 'center' },
                { text: 'Actions', value: 'actions', sortable: false },
            ]"
            :items="chapters"
            :loading="chaptersLoading"
            :items-per-page="-1"
            hide-default-footer
        >
            <!-- Total words -->
            <template v-slot:item.wordCount.total="{ item }">
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    {{ formatNumber(item.wordCount.total) }}
                </template>
            </template>

            <!-- Unique words -->
            <template v-slot:item.wordCount.unique="{ item }">
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    {{ formatNumber(item.wordCount.unique) }}
                </template>
            </template>

            <!-- Known words -->
            <template v-slot:item.wordCount.known="{ item }">
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    <template v-if="$props.wordCountDisplayType == 0">
                        {{ formatNumber(item.wordCount.known) }}
                    </template>
                    <template v-else>
                        {{ (item.wordCount.known / item.wordCount.unique * 100).toFixed(1) }}%
                    </template>
                </template>
            </template>

            <!-- Highlighted words -->
            <template v-slot:item.wordCount.highlighted="{ item }">
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    <div class="highlighted-words px-2 rounded-xl mx-auto">
                        <template v-if="$props.wordCountDisplayType < 2">
                            {{ formatNumber(item.wordCount.highlighted) }}
                        </template>
                        <template v-else>
                            {{ (item.wordCount.highlighted / item.wordCount.unique * 100).toFixed(1) }}%
                        </template>
                    </div>
                </template>
            </template>


            <!-- New words -->
            <template v-slot:item.wordCount.new="{ item }">
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    <div class="new-words px-2 rounded-xl mx-auto">
                        <template v-if="$props.wordCountDisplayType < 2">
                            {{ formatNumber(item.wordCount.new) }}
                        </template>
                        <template v-else>
                            {{ (item.wordCount.new / item.wordCount.unique * 100).toFixed(1) }}%
                        </template>
                    </div>
                </template>
            </template>

            <!-- Actions -->
            <template v-slot:item.actions="{ item }">
                <div class="d-flex justify-center">
                    <!-- Action buttons -->
                    <template v-if="item.processing_status == 'processed'">
                        <v-btn icon :to="'/chapters/read/' + item.id" title="Read"><v-icon>mdi-book-open-variant</v-icon></v-btn>
                        <v-menu rounded offset-y bottom left nudge-top="-5">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn icon v-bind="attrs" v-on="on"><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                            </template>
                            <v-btn
                                width="100"
                                class="menu-button"
                                tile
                                color="white"
                                @click="showEditChapterDialog(item.id)"
                            >
                                Edit
                            </v-btn>
                            <v-btn
                                width="100"
                                class="menu-button"
                                tile
                                color="white"
                                @click="showStartReviewDialog(book.id, book.name, item.id, item.name)"
                            >
                                Review
                            </v-btn>
                            <v-btn
                                width="100"
                                class="menu-button"
                                tile
                                color="white"
                                @click="showDeleteChapterDialog(item)"
                            >
                                Delete
                            </v-btn>
                        </v-menu>
                    </template>

                    <!-- Chapter importing loader -->
                    <template v-else-if="item.processing_status === 'unprocessed'">
                        <v-progress-linear
                            rounded
                            height="6"
                            indeterminate
                            color="warning"
                        ></v-progress-linear>
                    </template>

                    <!-- Chapter importing failed -->
                    <template v-else-if="item.processing_status === 'failed'">
                        <v-chip small color="error">Import failed</v-chip>
                    </template>
                </div>
            </template>
        </v-data-table>
    </v-container>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                book: null,
                bookWordCount: null,
                chapters: [],
                chaptersLoading: false,
                randomChapter: 0,
                errorDialog: {
                    active: false,
                },
                editBookChapterDialog: {
                    active: false,
                    chapterId: -1,
                },
                deleteBookChapterDialog: {
                    active: false,
                    chapterId: -1,
                },
                startReviewDialog: {
                    active: false,
                    bookId: -1,
                    bookName: '',
                    chapterId: -1,
                    chapterName: '',
                }
            }
        },
        props: {
            bookId: Number,
            wordCountDisplayType: Number,
        },
        mounted() {
            this.loadChapters();
            this.$store.getters['global/echo'].private('chapters-word-count-calculated.' + this.$store.getters['global/userUuid']).listen('ChaptersWordCountCalculatedEvent', (message) => {
                var wordCounts = message.wordCounts;

                wordCounts.forEach((item) => {
                    this.chapters[item.index].wordCount = item.wordCounts;
                    this.chapters[item.index].wordCountsLoaded = true;
                });
            });
        },
        beforeDestroy() {
            this.$store.getters['global/echo'].private('chapters-word-count-calculated.' + this.$store.getters['global/userUuid']).stopListening('ChaptersWordCountCalculatedEvent');
        },
        methods: {
            chapterSaved() {
                this.$emit('chapter-saved');
            },
            showEditChapterDialog(chapterId) {
                this.editBookChapterDialog.active = true;
                this.editBookChapterDialog.chapterId = chapterId;
            },
            showDeleteChapterDialog(chapter) {
                this.deleteBookChapterDialog.active = true;
                this.deleteBookChapterDialog.chapterId = chapter.id;
                this.deleteBookChapterDialog.chapterName = chapter.name;
            },
            deleteChapter() {
                axios.post('/chapters/delete', {
                    'chapterId': this.deleteBookChapterDialog.chapterId,
                }).catch(() => {
                    this.errorDialog.active = true;
                }).then((response) => {
                    if (response.status === 200) {
                        this.loadChapters();
                    } else {
                        this.errorDialog.active = true;
                    }
                });
            },
            loadChapters() {
                this.chaptersLoading = true;
                this.chapters = [];

                axios.post('/chapters', {
                    'bookId': this.$props.bookId,
                }).then((response) => {
                    for (let chapterIndex = 0; chapterIndex < response.data.chapters.length; chapterIndex++) {
                        response.data.chapters[chapterIndex].wordCountsLoaded = false;
                    }
                    
                    this.book = response.data.book;
                    this.chapters = response.data.chapters;

                    if (this.chapters.length) {
                        this.randomChapter = this.chapters[Math.floor(Math.random() * this.chapters.length)].id;
                    } else {
                        this.randomChapter = 0;
                    }

                    this.chaptersLoading = false;
                    
                });

                setTimeout(() => {
                    axios.get('/chapters/word-counts/' + this.$props.bookId).then();
                }, 100);
            },
            showStartReviewDialog(bookId, bookName, chapterId, chapterName) {
                this.startReviewDialog.bookName = bookName;
                this.startReviewDialog.bookId = bookId;
                this.startReviewDialog.chapterName = chapterName;
                this.startReviewDialog.chapterId = chapterId;
                this.startReviewDialog.active = true;
            },
            formatNumber: formatNumber
        }
    }
</script>
