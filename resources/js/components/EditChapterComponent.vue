<template>
    <form method="post" id="edit-chapter-form" enctype="multipart/form-data" v-if="book !== -1">
        <div class="form-line">
            Name
            <input type="text" v-model="name" required>
        </div>
        
        <div class="form-line">
            Text
            <textarea name="raw_text" rows="12" v-model="text"></textarea>
        </div>

        <button class="btn btn-primary" @click.prevent="saveChapter">Save</button>
        <router-link class="sidebar-button" :to="'/chapters/' + book">
            <button class="btn btn-primary" type="button">Cancel</button>
        </router-link>
        <div class="text-box red" v-if="error">
            Something went wrong. Please try again.
        </div>
    </form>
</template>

<script>
    export default {
        data: function() {
            return {
                name: '',
                text: '',
                book: -1,
                chapter: -1,
                error: false
            }
        },
        props: {
            
        },
        mounted() {
            this.book = this.$route.params.bookId;

            if (this.$route.params.chapterId !== undefined) {
                this.chapter = this.$route.params.chapterId;
                axios.post('/chapter/get/edit', {
                    'chapterId': this.$route.params.chapterId,
                }).then(function (response) {
                    this.name = response.data.name;
                    this.text = response.data.raw_text;
                    this.book = this.$route.params.bookId;
                }.bind(this)).catch(function (error) {
                    
                }).then(function () {

                });
            }
        },
        methods: {
            saveChapter: function() {
                var data = {
                    'name': this.name,
                    'raw_text': this.text,
                    'book': this.book
                };

                if (this.chapter !== -1) {
                    data.lesson_id = this.chapter;
                }
                
                axios.post('/chapter/save', data).then(function (response) {
                    if (response.data == 'success') {
                        this.$router.push('/chapters/' + this.book);
                    } else {
                        this.error = true;
                    }
                }.bind(this)).catch(function (error) {
                    
                }).then(function () {

                });
            }
        }
    }
</script>
