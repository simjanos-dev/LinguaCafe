<template>
    <div id="books">
        <router-link class="sidebar-button" to="/books/create"><button id="create-book-button" class="btn btn-primary">Create new book</button></router-link>
        <br><br>
        <div class="book" v-for="(book, index) in books" :key="index" >
            <div class="image-box">
                <img class="book-cover" :src="'/images/book_images/' + book.cover_image" v-if="book.cover_image !== ''">
            </div>
            <div class="information-box">
                <div class="name">{{ book.name }}</div>
                <div class="information">Words: <span>{{ book.wordCount.total }}</span></div>
                <div class="information">Unique words: <span>{{ book.wordCount.unique }}</span></div>
                <div class="information">Known words: <span>{{ book.wordCount.known }}</span></div>
                <div class="information">Highlighted words: <span class="highlighted"><i class="fa fa-book-open"></i> {{ book.wordCount.highlighted }}</span></div>
                <div class="information">New words: <span class="new"><i class="fa fa-eye-slash"></i> {{ book.wordCount.new }}</span></div>

                <div class="buttons">
                    <router-link class="sidebar-button" :to="'/chapters/' + book.id"> <button class="btn btn-secondary texts-button"><i class="fa fa-book-open"></i> Chapters</button></router-link>
                    <router-link class="sidebar-button" :to="'/review/' + book.id"> <button class="btn btn-primary texts-button"><i class="fa fa-keyboard"></i> Practice</button></router-link>
                </div>
            </div>
        </div>
    </div>
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
