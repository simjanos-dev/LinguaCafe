<template>
    <v-container id="chapters" class="d-flex flex-column justify-center flex-nowrap" v-if="book !== null">
        <!-- Review dialog -->
        <start-review-dialog 
            v-model="startReviewDialog.visible" 
            :book-id="startReviewDialog.bookId" 
            :book-name="startReviewDialog.bookName"
            :chapter-id="startReviewDialog.chapterId" 
            :chapter-name="startReviewDialog.chapterName">
        </start-review-dialog>
        
        <div outlined class="d-flex chapter button-box mx-auto mt-6 mb-2">
            <v-spacer></v-spacer>
            <v-btn class="mx-0 ml-2" rounded color="primary" :to="'/chapters/create/' + book.id">Create chapter</v-btn>
        </div>

        <v-card outlined :class="{'chapter': true, 'rounded-lg': true, 'mx-auto': true, 'my-6': index}" v-for="(chapter, index) in chapters" :key="index">
            <div class="d-flex flex-wrap flex-sm-nowrap justify-space-between">
                <v-card-text class="pa-0">
                    <v-card-title class="chapter-title pa-3">
                        {{ chapter.name }}
                        <v-spacer></v-spacer>
                        <v-menu rounded offset-y bottom left nudge-top="-5">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn icon v-bind="attrs" v-on="on"><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                            </template>
                            <v-btn width="100" class="menu-button" tile color="white" :to="'/chapters/edit/' + book.id + '/' + chapter.id">Edit</v-btn>
                            <v-btn width="100" class="menu-button" tile color="white" @click="showStartReviewDialog(book.id, book.name, chapter.id, chapter.name)">Review</v-btn>
                            <v-btn width="100" class="menu-button" tile color="white">Delete</v-btn>
                        </v-menu>
                    </v-card-title>
                    <v-simple-table dense class="chapter-info-table pb-4  mx-auto">
                        <tbody>
                            <tr>
                                <td width="200px">Words</td>
                                <td class="text-center"><div class="info-table-value">{{ chapter.wordCount.total }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Unique words</td>
                                <td class="text-center"><div class="info-table-value">{{ chapter.wordCount.unique }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Known words</td>
                                <td class="text-center"><div class="info-table-value">{{ chapter.wordCount.known }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Highlighted words</td>
                                <td class="text-center"><div class="info-table-value highlighted-words px-2 rounded-xl">{{ chapter.wordCount.highlighted }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">New words</td>
                                <td class="text-center"><div class="info-table-value new-words px-2 rounded-xl">{{ chapter.wordCount.new }}</div></td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                    <v-card-actions class="pa-3">
                        <v-spacer></v-spacer>
                        <v-btn rounded color="primary" :to="'/chapters/read/' + chapter.id">Read</v-btn>
                    </v-card-actions>
                </v-card-text>
            </div>
        </v-card>
    </v-container>
</template>

<script>
    export default {
        data: function() {
            return {
                book: null,
                chapters: [],
                randomChapter: 0,
                startReviewDialog: {
                    visible: false,
                    bookId: -1,
                    bookName: '',
                    chapterId: -1,
                    chapterName: '',
                }
            }
        },
        props: {
            
        },
        mounted() {
            axios.post('/chapters', {
                'bookId': this.$route.params.bookId,
            }).then(function (response) {
                this.book = response.data.book;
                this.chapters = response.data.chapters;
                this.randomChapter = this.chapters[Math.floor(Math.random() * this.chapters.length)].id;

            }.bind(this)).catch(function (error) {
                
            }).then(function () {

            });
        },
        methods: {
            showStartReviewDialog: function(bookId, bookName, chapterId, chapterName) {
                this.startReviewDialog.bookName = bookName;
                this.startReviewDialog.bookId = bookId;
                this.startReviewDialog.chapterName = chapterName;
                this.startReviewDialog.chapterId = chapterId;
                this.startReviewDialog.visible = true;
            }
        }
    }
</script>
