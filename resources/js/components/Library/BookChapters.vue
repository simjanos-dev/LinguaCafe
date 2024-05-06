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
        
        <!-- Desktop chapter list -->
        <v-simple-table class="book-chapter-information-table no-hover">
            <thead>
                <tr>
                    <th class="text-center">Chapter</th>
                    <th class="text-center px-1" >Total</th>
                    <th class="text-center px-1" >Unique</th>
                    <th class="text-center px-1" >Known</th>
                    <th class="text-center px-1" >Highlighted</th>
                    <th class="text-center px-1" >New</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Chapter list skeleton loader -->
                <template v-if="chaptersLoading">
                    <tr v-for="index in 4" :key="index" class="skeleton-row">
                        <td>
                            <v-skeleton-loader
                                class="rounded-pill"
                                type="card"
                            ></v-skeleton-loader>
                        </td>
                        <td class="text-center">
                            <v-skeleton-loader
                                class="rounded-pill"
                                type="card"
                            ></v-skeleton-loader>
                        </td>
                        <td class="text-center">
                            <v-skeleton-loader
                                class="rounded-pill"
                                type="card"
                            ></v-skeleton-loader>
                        </td>
                        <td class="text-center">
                            <v-skeleton-loader
                                class="rounded-pill"
                                type="card"
                            ></v-skeleton-loader>
                        </td>
                        <td class="text-center">
                            <v-skeleton-loader
                                class="rounded-pill"
                                type="card"
                            ></v-skeleton-loader>
                        </td>
                        <td class="text-center">
                            <v-skeleton-loader
                                class="rounded-pill"
                                type="card"
                            ></v-skeleton-loader>
                        </td>
                        <td class="text-center">
                            <v-skeleton-loader
                                class="rounded-pill mr-2"
                                type="card"
                            ></v-skeleton-loader>
                            <v-skeleton-loader
                                class="rounded-pill"
                                type="card"
                            ></v-skeleton-loader>
                        </td>
                    </tr>
                </template>

                <!-- Chapter list -->
                <template v-if="!chaptersLoading">
                    <tr v-for="(chapter, index) in chapters" :key="index">
                        <td class="default-font">{{ chapter.name }}</td>
                        <td class="text-center px-1">{{ formatNumber(chapter.wordCount.total) }}</td>
                        <td class="text-center px-1">{{ formatNumber(chapter.wordCount.unique) }}</td>
                        <td class="text-center px-1">{{ formatNumber(chapter.wordCount.known) }}</td>
                        <td class="text-center px-1"><div class="info-table-value highlighted-words px-2 rounded-xl mx-auto">{{ formatNumber(chapter.wordCount.highlighted) }}</div></td>
                        <td class="text-center px-1"><div class="info-table-value new-words px-2 rounded-xl mx-auto">{{ formatNumber(chapter.wordCount.new) }}</div></td>
                        <td class="text-center">
                            <v-btn icon :to="'/chapters/read/' + chapter.id" title="Read"><v-icon>mdi-book-open-variant</v-icon></v-btn>
                            <v-menu rounded offset-y bottom left nudge-top="-5">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn icon v-bind="attrs" v-on="on"><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                                </template>
                                <v-btn
                                    width="100"
                                    class="menu-button"
                                    tile
                                    color="white"
                                    @click="showEditChapterDialog(chapter.id)"
                                >
                                    Edit
                                </v-btn>
                                <v-btn
                                    width="100"
                                    class="menu-button"
                                    tile
                                    color="white"
                                    @click="showStartReviewDialog(book.id, book.name, chapter.id, chapter.name)"
                                >
                                    Review
                                </v-btn>
                                <v-btn
                                    width="100"
                                    class="menu-button"
                                    tile
                                    color="white"
                                    @click="showDeleteChapterDialog(chapter)"
                                >
                                    Delete
                                </v-btn>
                            </v-menu>
                        </td>
                    </tr>
                </template>
            </tbody>
        </v-simple-table>
        <!-- Mobile chapter list -->
        <div class="book-chapter-information-mobile-table border rounded-lg">
            <!-- Chapter skeletons -->
            <template v-if="chaptersLoading">
            <v-card
                    v-for="index in 4" :key="index"
                    class="expansion-card"
                    elevation="0"
                >
                <v-card-title class="expansion-card-title py-3 px-2" @click="toggleExpansion(index)">
                    <v-skeleton-loader
                        class="rounded-pill"
                        type="card"
                    ></v-skeleton-loader>
                    
                    <v-spacer />
                    
                    <v-skeleton-loader
                        class="rounded-pill"
                        type="card"
                    ></v-skeleton-loader>
                </v-card-title>
            </v-card>
            </template>

            <!-- Chapters -->
            <template v-if="!chaptersLoading">
                <v-card
                    v-for="(chapter, index) in chapters" :key="index"
                    :class="{'expansion-card': true, 'open': chapter.expanded}"
                    elevation="0"
                >
                    <v-card-title class="expansion-card-title default-font py-3 px-2" @click="toggleExpansion(index)">
                        {{ chapter.name }}
                        <v-spacer />
                        <v-icon class="mr-4">{{ chapter.expanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                    </v-card-title>
                    <v-card-text class="expansion-card-content">
                        <v-simple-table dense class="expansion-table no-hover no-lines">
                            <tbody>
                                <tr>
                                    <td>Total words:</td>
                                    <td>{{ formatNumber(chapter.wordCount.total) }}</td>
                                </tr>
                                <tr>
                                    <td>Unique words:</td>
                                    <td>{{ formatNumber(chapter.wordCount.unique) }}</td>
                                </tr>
                                <tr>
                                    <td>Known words:</td>
                                    <td>{{ formatNumber(chapter.wordCount.known) }}</td>
                                </tr>
                                <tr>
                                    <td>Highlighted words:</td>
                                    <td>
                                        <div class="info-table-value highlighted-words px-2 rounded-xl mx-auto">{{ formatNumber(chapter.wordCount.highlighted) }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>New words:</td>
                                    <div class="info-table-value new-words px-2 rounded-xl mx-auto">{{ formatNumber(chapter.wordCount.new) }}</div>
                                </tr>

                                <td class="text-center px-1"></td>
                            </tbody>
                        </v-simple-table>
                        <div class="d-flex mt-4">
                            <v-spacer />
                            <v-btn rounded text :to="'/chapters/read/' + chapter.id" title="Read">Read</v-btn>
                            <v-menu rounded offset-y bottom left nudge-top="-5">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn icon v-bind="attrs" v-on="on"><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                                </template>
                                <v-btn
                                    width="100"
                                    class="menu-button"
                                    tile
                                    color="white"
                                    @click="showEditChapterDialog(chapter.id)"
                                >
                                    Edit
                                </v-btn>
                                <v-btn
                                    width="100"
                                    class="menu-button"
                                    tile
                                    color="white"
                                    @click="showStartReviewDialog(book.id, book.name, chapter.id, chapter.name)"
                                >
                                    Review
                                </v-btn>
                                <v-btn
                                    width="100"
                                    class="menu-button"
                                    tile
                                    color="white"
                                    @click="showDeleteChapterDialog(chapter)"
                                >
                                    Delete
                                </v-btn>
                            </v-menu>
                        </div>
                    </v-card-text>
                </v-card>
            </template>
        </div>
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
        },
        mounted() {
            this.loadChapters();
        },
        methods: {
            chapterSaved() {
                this.$emit('chapter-saved');
            },
            toggleExpansion(expansionIndex) {
                for (let chapterIndex = 0; chapterIndex < this.chapters.length; chapterIndex++) {
                    if (chapterIndex == expansionIndex) {
                        this.chapters[chapterIndex].expanded = !this.chapters[chapterIndex].expanded;
                    } else {
                        this.chapters[chapterIndex].expanded = false;
                    }
                }
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
                        response.data.chapters[chapterIndex].expanded = false;
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
