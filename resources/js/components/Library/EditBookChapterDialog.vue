<template>
    <v-dialog v-model="value" persistent width="800px">
        <v-card class="rounded-lg">
            <!-- Card title -->
            <v-card-title>
                <!-- Edit chapter title -->
                <template v-if="$props.chapterId !== -1">
                    <v-icon class="mr-2">mdi-text-box-edit</v-icon>Edit chapter
                </template>

                <!-- New chapter title -->
                <template v-if="$props.chapterId == -1">
                    <v-icon class="mr-2">mdi-text-box-plus</v-icon>Add chapter
                </template>

                <v-spacer />
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <!-- Form -->
            <v-card-text>
                <v-form ref="editChapterForm" v-if="!loading">
                    <label class="font-weight-bold mt-2">Name</label>
                    <v-text-field 
                        filled
                        dense
                        rounded
                        v-model="name"
                        :rules="[rules.chapterName]"
                        :disabled="type !== 'text' || loading"
                    ></v-text-field>
                    
                    <label class="font-weight-bold mt-2">Text</label>
                    <v-textarea
                        v-model="text"
                        filled
                        dense
                        rounded
                        no-resize
                        height="300px"
                        maxlength="10000"
                        counter="10000"
                        :disabled="type !== 'text' || loading"
                    ></v-textarea>
                    
                    <!-- Save result alerts -->
                    <v-alert
                        class="my-3" 
                        border="left"
                        type="error"
                        v-if="saveResult == 'error'"
                    >
                        An error has occurred while saving.
                    </v-alert>

                    <v-alert
                        class="my-3" 
                        border="left"
                        type="success"
                        v-if="saveResult == 'success'"
                    >
                        Chapter has been saved successfully.
                    </v-alert>

                    <!-- Subtitle editing is not enabled alert -->
                    <v-alert
                        v-if="type !== 'text'"
                        class="my-3" 
                        border="left"
                        type="error"
                    >
                        You cannot edit subtitles yet, it will be implemented in a future update.
                    </v-alert>
                </v-form>
            </v-card-text>

            <!-- Action buttons -->
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>

                <v-btn 
                    rounded 
                    depressed
                    color="primary" 
                    @click="save"
                    :disabled="!isFormValid || saving || saveResult == 'success' || type !== 'text'"
                    :loading="saving"
                >
                    Save
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        data: function() {
            return {
                isFormValid: false,
                loading: true,
                saving: false,
                saveResult: '',
                rules: {
                    chapterName: (value) => {
                        if (!value.length) {
                            this.isFormValid = false;
                            return 'You must type in a name for the chapter.';
                        }

                        if (value.length > 128) {
                            this.isFormValid = false;
                            return 'The chapter name must be below 128 characters.';
                        }

                        this.isFormValid = true;
                        return true;
                    }
                },
                name: '',
                text: '',
                type: 'text',
            }
        },
        props: {
            value : Boolean,
            bookId: Number,
            chapterId: Number
        },
        emits: ['input'],
        mounted() {
            this.loadChapter();
        },
        methods: {
            save() {
                this.saveResult = '';
                if (!this.$refs.editChapterForm.validate()) {
                    return;
                }

                this.saving = true;
                var data = {
                    'name': this.name,
                    'raw_text': this.text,
                    'book': this.$props.bookId
                };

                if (this.$props.chapterId !== -1) {
                    data.lesson_id = this.$props.chapterId;
                }
                
                axios.post('/chapter/save', data).then((response) => {
                    if (response.data == 'success') {
                        this.saveResult = 'success';

                        setTimeout(() => {
                            this.$emit('chapter-saved');
                            this.close();
                        }, 750);
                    } else {
                        this.saveResult = 'error';
                    }
                }).catch((error) => {
                    this.saving = false;
                    this.saveResult = 'error';
                }).then(() =>{
                    this.saving = false;
                });
            },
            loadChapter() {
                if (this.$props.chapterId !== -1) {
                    axios.get('/chapter/get/edit/' + this.$props.chapterId).then((response) => {
                        this.name = response.data.name;
                        this.text = response.data.raw_text;
                        this.type = response.data.type;
                        this.loading = false;
                        this.$nextTick(() => {
                            this.$refs.editChapterForm.validate();
                        });
                    });
                } else {
                    this.loading = false;
                    this.$nextTick(() => {
                        this.$refs.editChapterForm.validate();
                    });
                }
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
