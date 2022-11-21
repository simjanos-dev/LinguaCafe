<template>
    <v-container id="books" class="d-flex flex-column justify-center flex-nowrap">
        <v-card elevation="0" class="book button-box mx-auto mt-6 mb-2">
            <v-card-actions class="px-0">
                <v-spacer></v-spacer>
                <v-btn class="mx-0" depressed color="secondary" :to="'/books/create'">Create book</v-btn>
            </v-card-actions>
        </v-card>
        <v-card outlined :class="{'book': true, 'mx-auto': true, 'my-6': index}" v-for="(book, index) in books" :key="index">
            <div class="book-box">
                <v-img class="cover-image" :src="'/images/book_images/' + book.cover_image"></v-img>
                <v-card-text class="pa-0 pl-3">
                    <v-card-title class="book-title pa-3">
                        {{ book.name }}
                        <v-spacer></v-spacer>
                        <v-btn icon><v-icon>mdi-dots-horizontal</v-icon></v-btn>
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
                                <td class="text-center"><div class="info-table-value highlighted px-2" :style="{'background-color': $vuetify.theme.currentTheme.highlightedWord }">{{ book.wordCount.highlighted }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">New words</td>
                                <td class="text-center"><div class="info-table-value highlighted px-2" :style="{'background-color': $vuetify.theme.currentTheme.newWord }">{{ book.wordCount.new }}</div></td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                    <v-card-actions class="pa-3">
                        <v-spacer></v-spacer>
                        <v-btn depressed color="secondary" :to="'/review/' + book.id">Review</v-btn>
                        <v-btn class="ml-3" depressed color="secondary" :to="'/chapters/' + book.id">Chapters</v-btn>
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
        }
    }
</script>
