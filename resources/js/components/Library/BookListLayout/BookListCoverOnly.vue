<template>
    <!-- Book list detailed -->
    <div id="book-list" class="cover-only d-flex flex-row flex-wrap justify-space-around">
        <v-card
            outlined
            :id="'book-' + book.id"
            class="book cover-only rounded-lg ma-1"
            v-for="(book, index) in books"
            :key="index"
        >
            <div class="book-box">
                <!-- Cover image -->
                <div class="cover-image-box rounded-lg">
                    <img
                        class="cover-image rounded-lg"
                        :src="'/images/book_images/' + book.cover_image"
                        @click="openBook(book.id)"
                    ></img>
                </div>
            </div>
        </v-card>
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
        formatNumber: formatNumber
    }
}
</script>
