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
                <!-- Count -->
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    {{ formatNumber(item.wordCount.total) }}
                </template>

                <!-- Skeleton -->
                <v-skeleton-loader
                        v-else
                        class="chapter-word-count-skeleton rounded-pill"
                        type="image"
                ></v-skeleton-loader>
            </template>

            <!-- Unique words -->
            <template v-slot:item.wordCount.unique="{ item }">
                <!-- Count -->
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    {{ formatNumber(item.wordCount.unique) }}
                </template>

                <!-- Skeleton -->
                <v-skeleton-loader
                        v-else
                        class="chapter-word-count-skeleton rounded-pill"
                        type="image"
                ></v-skeleton-loader>
            </template>

            <!-- Known words -->
            <template v-slot:item.wordCount.known="{ item }">
                <!-- Count -->
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    <template v-if="$props.wordCountDisplayType == 0">
                        {{ formatNumber(item.wordCount.known) }}
                    </template>
                    <template v-else-if="item.wordCount.unique">
                        {{ (item.wordCount.known / item.wordCount.unique * 100).toFixed(1) }}%
                    </template>
                    <template v-else>
                        0%
                    </template>
                </template>

                <!-- Skeleton -->
                <v-skeleton-loader
                        v-else
                        class="chapter-word-count-skeleton rounded-pill"
                        type="image"
                ></v-skeleton-loader>
            </template>

            <!-- Highlighted words -->
            <template v-slot:item.wordCount.highlighted="{ item }">
                <!-- Count -->
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    <div class="highlighted-words px-2 rounded-xl mx-auto">
                        <template v-if="$props.wordCountDisplayType < 2">
                            {{ formatNumber(item.wordCount.highlighted) }}
                        </template>
                        <template v-else-if="item.wordCount.unique">
                            {{ (item.wordCount.highlighted / item.wordCount.unique * 100).toFixed(1) }}%
                        </template>
                        <template v-else>
                            0%
                        </template>
                    </div>
                </template>

                <!-- Skeleton -->
                <v-skeleton-loader
                        v-else
                        class="chapter-word-count-skeleton rounded-pill"
                        type="image"
                ></v-skeleton-loader>
            </template>


            <!-- New words -->
            <template v-slot:item.wordCount.new="{ item }">
                <!-- Count -->
                <template v-if="item.processing_status === 'processed' && item.wordCountsLoaded">
                    <div class="new-words px-2 rounded-xl mx-auto">
                        <template v-if="$props.wordCountDisplayType < 2">
                            {{ formatNumber(item.wordCount.new) }}
                        </template>
                        <template v-else-if="item.wordCount.unique">
                            {{ (item.wordCount.new / item.wordCount.unique * 100).toFixed(1) }}%
                        </template>
                        <template v-else>
                            0%
                        </template>
                    </div>
                </template>

                <!-- Skeleton -->
                <v-skeleton-loader
                        v-else
                        class="chapter-word-count-skeleton rounded-pill"
                        type="image"
                ></v-skeleton-loader>
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
                        <v-chip small color="warning">importing</v-chip>
                    </template>

                    <!-- Chapter importing failed -->
                    <template v-else-if="item.processing_status === 'failed'">
                        <v-chip small color="error">failed</v-chip>
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
        },
        beforeDestroy() {
            if (this.wordCountPollingInterval) {
                clearInterval(this.wordCountPollingInterval);
            }
        },
        methods: {
            // Function that periodically checks if word counts are available for chapters
            startWordCountPolling() {
                // Poll every 2 seconds
                this.wordCountPollingInterval = setInterval(() => {
                    // Reqest for the word counts
                    axios.get('/chapters/word-counts/' + this.$props.bookId)
                        .then((response) => {
                            // Update chapters with new word counts
                            // - Extract chapters from response
                            const chapters = response.data || {};
                            
                            // - fill in the values
                            this.chapters.forEach((currentChapter) => {
                                // - If empty return
                                if (!chapters[currentChapter.id]) {
                                    return;
                                }
                                // - Fill in the numbers
                                if ('wordCount' in chapters[currentChapter.id] && chapters[currentChapter.id].wordCount !== null) {
                                    currentChapter.wordCount = chapters[currentChapter.id].wordCount;
                                    currentChapter.wordCountsLoaded = true;
                                }
                                // - Fill in the processing status
                                if ('processing_status' in chapters[currentChapter.id]) {
                                    currentChapter.processing_status = chapters[currentChapter.id].processing_status;
                                }
                            });
                            // Stop polling if all chapters are loaded
                            if (this.chapters.every(ch => ch.wordCountsLoaded || ch.processing_status !== 'processed')) {
                                clearInterval(this.wordCountPollingInterval);
                                this.wordCountPollingInterval = null;
                            }
                        });
                }, 
                // time interval
                2000
            );
            },
            chapterSaved() {
                this.$emit('word-count-changed');
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
                axios.delete(`/chapters/delete/${this.deleteBookChapterDialog.chapterId}`).catch(() => {
                    this.errorDialog.active = true;
                }).then((response) => {
                    this.$emit('word-count-changed');
                });
            },
            loadChapters() {
                this.chaptersLoading = true;
                this.chapters = [];

                axios.all([
                    axios.get(`/books/${this.$props.bookId}`),
                    axios.post(`/chapters/${this.$props.bookId}`)
                ]).catch((error) => {
                    console.log('error:', error)
                }).then(axios.spread((bookResponse, chaptersResponse) => {
                    this.book = bookResponse.data.data;
                    this.chapters = chaptersResponse.data.data;

                    this.chapters.map((chapter) => {
                        chapter.wordCountsLoaded = false
                        return
                    })

                    if (this.chapters.length) {
                        this.randomChapter = this.chapters[Math.floor(Math.random() * this.chapters.length)].id;
                    } else {
                        this.randomChapter = 0;
                    }

                    this.chaptersLoading = false;

                    // Load chapter word counts
                    this.$nextTick(() => {
                        this.startWordCountPolling();
                    });
                }));
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
