<template>
    <div id="chapter-list">
        <div id="chapter-book" v-if="book !== null">
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
            </div>
            <div class="buttons">
                <router-link to="/books">
                    <button class="btn btn-primary texts-button"><i class="fa fa-book-open"></i> Library</button>
                </router-link>
                <router-link :to="'/chapters/create/' + book.id">
                    <button class="btn btn-primary texts-button"><i class="fa fa-folder-plus"></i> Create chapter</button>
                </router-link>
                <template v-if="chapters.length">
                    <router-link :to="'/review/' + book.id">
                        <button class="btn btn-primary texts-button"><i class="fa fa-keyboard"></i> Practice</button>
                    </router-link>
                    
                    <router-link :to="'/chapters/read/' + randomChapter">
                        <button class="btn btn-primary texts-button"><i class="fa fa-random"></i> Random chapter</button>
                    </router-link>
                </template>
            </div>
        </div>
        <div id="chapters">
            <div class="chapter" v-for="(chapter, index) in chapters" :key="index">
                <div class="name">{{ chapter.name }}</div>
                <div class="information">Read: <span>{{ chapter.read_count }}</span></div>
                <div class="information">Words: <span>{{ chapter.wordCount.total }}</span></div>
                <div class="information">Unique words: <span>{{ chapter.wordCount.unique }}</span></div>
                <div class="information">Known words: <span>{{ chapter.wordCount.known }}</span></div>
                <div class="information">Highlighted words: <span class="highlighted"><i class="fa fa-book-open"></i> {{ chapter.wordCount.highlighted }}</span></div>
                <div class="information">New words: <span class="new"><i class="fa fa-eye-slash"></i> {{ chapter.wordCount.new }}</span></div>
                <div class="buttons">
                    <router-link :to="'/chapters/read/' + chapter.id">
                        <button class="btn btn-secondary texts-button"><i class="fa fa-book-open"></i> Read</button>
                    </router-link>
                    <router-link :to="'/review/' + book.id + '/' + chapter.id">
                        <button class="btn btn-primary texts-button"><i class="fa fa-keyboard"></i>  Vocabulary</button>
                    </router-link>
                    <router-link :to="'/chapters/edit/' + book.id + '/' + chapter.id">
                        <button class="btn btn-primary texts-button"><i class="fa fa-pen"></i> Edit</button>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                book: null,
                chapters: [],
                randomChapter: 0,
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
        }
    }
</script>
