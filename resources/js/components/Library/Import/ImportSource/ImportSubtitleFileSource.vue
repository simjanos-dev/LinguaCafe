<template>
    <div class="d-flex flex-column align-stretch">
        <!-- Subtitle file -->
        <label class="font-weight-bold">Subtitle file</label>
        <v-file-input
            v-model="subtitleFile"
            filled
            dense
            rounded
            persistent-hint
            hint="Accepted formats: .srt .ass"
            ref="subtitleFile"
            accept=".srt,.ass"
            placeholder="Subtitle file"
            prepend-icon="mdi-book"
            :rules="[rules.subtitleFileRule]"
            @change="subtitleFileSelected"
        ></v-file-input>

    </div>
</template>

<script>
import axios from 'axios';

    export default {
        data: function() {
            return {
                subtitles: null,
                subtitleFile: null,
                isFormValid: false,
                rules: {
                    subtitleFileRule: (value) => {
                        if (value === null || value === undefined) {
                            return 'You must select a file.';
                        }
                        
                        let extension = value.name.split('.');
                        extension = extension[extension.length - 1];
                        if (extension !== 'srt' && extension !== 'ass') {
                            return 'The selected file must a .srt or .ass file.';
                        }

                        return true;
                    }
                }
            }
        },
        props: {
            language: String,
        },
        mounted() {
        },
        methods: {
            subtitleFileSelected() {
                // validate
                this.subtitles = null;
                if (!this.$refs.subtitleFile.validate()) {
                    // disable continue button in import dialog
                    this.$emit('subtitle-selected', {
                        subtitles: null,
                        isImportSourceValid: false
                    });

                    return;
                }


                var formData = new FormData();
                formData.set('subtitleFile', this.subtitleFile);

                axios.post('/youtube/get-subtitle-file-content', formData).then((response) => {
                    this.subtitles = response.data;

                    this.$emit('subtitle-selected', {
                        subtitles: this.subtitles,
                        isImportSourceValid: true
                    });
                });

            }
        }
    }
</script>
