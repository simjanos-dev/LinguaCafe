<template>
    <v-form ref="form" id="edit-chapter-form" class="mx-auto mt-6" v-if="book !== -1">
        <v-text-field 
            filled
            dense
            label="Name"
            v-model="name"
            :rules="[rules.required]"
        ></v-text-field>
        
        <v-textarea
            filled
            dense
            no-resize
            label="Text"
            v-model="text"
        ></v-textarea>

        <v-btn 
            class="mx-0"
            depressed 
            :disabled="saving"
            :loading="saving"
            :dark="!saving"
            :color="saving ? '' : 'primary'"
            @click="saveChapter"
        >Save</v-btn>
        
        <v-btn 
            class="mx-0"
            depressed
            color="primary"
            :to="'/chapters/' + book"
        >Cancel</v-btn>
        
        <v-alert
            class="my-3" 
            border="left"
            type="error"
            v-if="error"
            >
            Something went wrong. Please try again.
        </v-alert>
    </v-form>
</template>

<script>
    export default {
        data: function() {
            return {
                rules: {
                    required: (value) => {
                        return !!value || 'Required.';
                    },
                },
                error: false,
                saving: false,
                name: '',
                text: '',
                book: -1,
                chapter: -1,
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
                if (!this.$refs.form.validate()) {
                    return;
                }

                this.saving = true;
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
                }.bind(this)).catch((error) => {
                    this.saving = false;
                    this.error = true;
                }).then(() =>{
                    this.saving = false;
                });
            }
        }
    }
</script>
