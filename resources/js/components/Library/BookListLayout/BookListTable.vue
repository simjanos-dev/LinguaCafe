<script setup>
import NoBookCover from '../../Icons/NoBookCover.vue';
</script>

<template>
    <div id="book-list" class="table-layout">
        <!-- Book list detailed -->
        <v-card outlined class="border rounded-lg mt-4">
            <v-card-title>
                <v-text-field
                    v-model="booksTextFilter"
                    append-icon="mdi-magnify"
                    label="Search"
                    filled
                    dense
                    hide-details
                    single-line
                    rounded
                ></v-text-field>
            </v-card-title>

            <v-data-table
                class="ma-4 mb-0 no-hover"
                :headers="[
                    {
                        text: 'Cover',
                        value: 'cover_image',
                        align: 'center',
                        width: '140px',
                        sortable: false,
                    },
                    {
                        text: 'Title',
                        value: 'name',
                        align: 'left',
                    },
                    {
                        text: 'Length',
                        value: 'word_count',
                        align: 'center',
                        width: '140px',
                    },
                    {
                        text: 'Actions',
                        value: 'actions',
                        align: 'center',
                        width: '140px',
                        sortable: false,
                    },
                ]"
                :items="books"
                :search="booksTextFilter"
            >
                <!-- Cover image -->
                <template v-slot:item.cover_image="{ item }">
                    <img
                        v-if="item.cover_image"
                        class="cover-image rounded-lg ma-2"
                        :src="'/images/book_images/' + item.cover_image"
                    ></img>
                    <div v-else class="cover-image d-flex align-items-center mx-auto my-2">
                        <NoBookCover class="px-1" />
                    </div>
                </template>
                
                <!-- Length -->
                <template v-slot:item.word_count="{ item }">
                    {{ formatNumber(item.word_count) }}
                </template>
                
                <!-- Actions -->
                <template v-slot:item.actions="{ item }">
                    <v-btn icon title="Open book" @click="openBook(item.id)"><v-icon>mdi-book-open</v-icon></v-btn>
                    <v-menu content-class="book-menu" rounded offset-y bottom left nudge-top="-5">
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn icon v-bind="attrs" v-on="on"  title="Actions">
                                <v-icon>mdi-dots-horizontal</v-icon>
                            </v-btn>
                        </template>
                        <v-btn class="menu-button" tile color="white" @click="showEditBookDialog(item)">Edit</v-btn>
                        <v-btn class="menu-button" tile color="white" @click="showStartReviewDialog(item)">Review</v-btn>
                        <v-btn class="menu-button" tile color="white" @click="showDeleteBookDialog(item)">Delete</v-btn>
                    </v-menu>
                </template>

            </v-data-table>
        </v-card>
    </div>
</template>

<script>
    import {formatNumber} from './../../../helper.js';
    export default {
        data: function() {
            return {
                booksTextFilter: '',
            }
        },
        props: {
            books: Array
        },
        mounted() {
        },
        methods: {
            openBook(bookId) {
                console.log('books', this.$props.books);
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
