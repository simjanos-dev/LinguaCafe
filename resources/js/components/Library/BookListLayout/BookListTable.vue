<template>
    <!-- Book list detailed -->
    <div id="book-list" class="table-layout">
        <v-simple-table class="no-hover border rounded-lg mt-4">
            <thead>
                <tr>
                    <th class="book-cover text-center">Cover</th>
                    <th class="book-title text-center px-1" >Title</th>
                    <!-- <th class="text-center px-1" >Unique</th>
                    <th class="text-center px-1" >Known</th>
                    <th class="text-center px-1" >Highlighted</th>
                    <th class="text-center px-1" >New</th> -->
                    <th class="book-actions text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr class="book" v-for="(book, index) in books">
                    <td class="book-cover">
                        <img
                            class="cover-image rounded-lg ma-2"
                            :src="'/images/book_images/' + book.cover_image"
                        ></img>
                    </td>
                    <td class="book-title">
                        {{ book.name }}
                    </td>
                    <td class="book-actions">
                        <v-btn icon title="Open book" @click="openBook(book.id)"><v-icon>mdi-book-open</v-icon></v-btn>
                        <v-menu content-class="book-menu" rounded offset-y bottom left nudge-top="-5">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn icon v-bind="attrs" v-on="on"  title="Actions">
                                    <v-icon>mdi-dots-horizontal</v-icon>
                                </v-btn>
                            </template>
                            <v-btn class="menu-button" tile color="white" @click="showEditBookDialog(book)">Edit</v-btn>
                            <v-btn class="menu-button" tile color="white" @click="showStartReviewDialog(book)">Review</v-btn>
                            <v-btn class="menu-button" tile color="white" @click="showDeleteBookDialog(book)">Delete</v-btn>
                        </v-menu>
                    </td>
                </tr>
            </tbody>
        </v-simple-table>
    </div>
</template>

<script>
    import {formatNumber} from './../../../helper.js';
    export default {
        data: function() {
            return {
                
            }
        },
        props: {
            books: Array
        },
        mounted() {
        },
        methods: {
            openBook(bookId) {
                this.$emit('open-book', bookId);
            },
            showEditBookDialog(book) {
                this.$emit('show-edit-book-dialog', book);
            },
            showDeleteBookDialog(book) {
                this.$emit('show-delete-book-dialog', book);
            },
            showStartReviewDialog(book) {
                this.$emit('show-start-review-dialog', book.id, book.name);
            },
            formatNumber: formatNumber
        }
    }
</script>
