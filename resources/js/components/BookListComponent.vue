<template>
    <v-container id="books" class="d-flex flex-column justify-center flex-nowrap">
        <!-- Review dialog -->
        <start-review-dialog 
            v-model="startReviewDialog.visible" 
            :book-id="startReviewDialog.bookId" 
            :book-name="startReviewDialog.bookName">
        </start-review-dialog>
        
        <v-card elevation="0" class="book button-box mx-auto mt-6 mb-2">
            <v-card-actions class="px-0">
                <v-spacer></v-spacer>
                <v-btn rounded class="mx-0" color="primary" :to="'/books/create'"><v-icon>mdi-plus</v-icon> Create book</v-btn>
            </v-card-actions>
        </v-card>
        <v-card outlined class="book rounded-lg mx-auto my-6" v-for="(book, index) in books" :key="index">
            <div class="book-box">
                <v-img class="cover-image" :src="'/images/book_images/' + book.cover_image"></v-img>
                <v-card-text class="pa-0 pl-3">
                    <v-card-title class="book-title pa-3">
                        {{ book.name }}
                        <v-spacer></v-spacer>
                        
                        <v-menu rounded offset-y bottom left nudge-top="-5">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn icon v-bind="attrs" v-on="on"><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                            </template>
                            <v-btn width="100" class="menu-button" tile color="white">Edit</v-btn>
                            <v-btn width="100" class="menu-button" tile color="white" @click="showStartReviewDialog(book.id, book.name)">Review</v-btn>
                            <v-btn width="100" class="menu-button" tile color="white">Delete</v-btn>
                        </v-menu>
                    </v-card-title>
                    <v-simple-table dense class="book-info-table no-hover pb-4  mx-auto">
                        <tbody>
                            <tr>
                                <td width="200px">Words</td>
                                <td class="text-center"><div class="info-table-value">{{ book.wordCount.total }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Unique words</td>
                                <td class="text-center"><div class="info-table-value">{{ book.wordCount.unique }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Known words</td>
                                <td class="text-center"><div class="info-table-value">{{ book.wordCount.known }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Highlighted words</td>
                                <td class="text-center"><div class="info-table-value highlighted-words px-2 rounded-xl">{{ book.wordCount.highlighted }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">New words</td>
                                <td class="text-center"><div class="info-table-value new-words px-2 rounded-xl">{{ book.wordCount.new }}</div></td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn rounded color="secondary" :to="'/chapters/' + book.id">Chapters</v-btn>
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
                books: [],
                startReviewDialog: {
                    visible: false,
                    bookId: -1,
                    bookName: '',
                }
            }
        },
        props: {
            
        },
        mounted() {
            axios.post('/books').then(function (response) {
                this.books = response.data;
            }.bind(this)).catch(function (error) {
                this.error = error;
            }).then(function () {

            });
        },
        methods: {
            showStartReviewDialog: function(bookId, bookName) {
                this.startReviewDialog.bookName = bookName;
                this.startReviewDialog.bookId = bookId;
                this.startReviewDialog.visible = true;
            }
        }
    }
</script>
