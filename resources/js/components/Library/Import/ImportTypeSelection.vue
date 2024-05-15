<template>
    <div>
        <label class="font-weight-bold">Select an import option</label>
        <div class="import-type-group flex-wrap">
            <template v-if="language == 'thai'">
                <v-alert
                    class="w-full"
                    color="error"
                    type="error"
                    border="left"
                >
                    There was a problem with importing Thai texts, so I temporarily disabled this function. It will be fixed in v0.13. Until that you can only import text by
                    clicking on the ,,Create book" button, then inside the opened book the ,,Add chapter" button.
                </v-alert>
            </template>
            <template v-else>
                <!--Plain text -->
                <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('plain-text')">
                    <div class="import-type-button-icon-box">
                        <v-icon large>mdi-text-box</v-icon>
                    </div>
                    <span>Plain text</span>
                </div>
                
                <!-- Text file -->
                <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('text-file')">
                    <div class="import-type-button-icon-box">
                        <v-icon large>mdi-file-document</v-icon>
                    </div>
                    <span>Text file</span>
                </div>

                <!-- E-book -->
                <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('e-book')">
                    <div class="import-type-button-icon-box">
                        <v-icon large>mdi-book</v-icon>
                    </div>
                    <span>E-book</span>
                </div>
            
                <!-- Youtube -->
                <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('youtube')">
                    <div class="import-type-button-icon-box">
                        <v-icon large>mdi-youtube</v-icon>
                    </div>
                    <span>Youtube</span>
                </div>

                <!-- Jellyfin subtitle -->
                <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('jellyfin-subtitle')">
                    <div class="import-type-button-icon-box">
                        <v-icon large>mdi-movie</v-icon>
                    </div>
                    <span>Jellyfin subtitle</span>
                </div>

                <!-- Subtitle file -->
                <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('subtitle-file')">
                    <div class="import-type-button-icon-box">
                        <v-icon large>mdi-subtitles</v-icon>
                    </div>
                    <span>Subtitle file</span>
                </div>

                <!-- Website -->
                <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('website')" v-if="websiteImportSupported">
                    <div class="import-type-button-icon-box">
                        <v-icon large>mdi-web</v-icon>
                    </div>
                    <span>Website</span>
                </div>

                <!-- Website not supported or loading data -->
                <div class="import-type-button rounded-lg mx-2 mb-4" v-if="!websiteImportSupported">
                    <div class="import-type-button-icon-box">
                        <v-icon large v-if="loading">mdi-web</v-icon>
                        <div class="pt-4" v-if="!loading">Language not supported</div>
                    </div>
                    <span>
                        <!-- Loading -->
                        <v-progress-circular
                            v-if="loading"
                            indeterminate
                            color="primary"
                            size="16"
                            width="2"
                        ></v-progress-circular>

                        <!-- Not supported label -->
                        <template v-if="!loading">
                            Website
                        </template>
                    </span>
                </div>
            </template>

            <!--
            <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('rss')">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-rss-box</v-icon>
                </div>
                <span>RSS feed</span>
            </div>
            
            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-file-document</v-icon>
                </div>
                <span>PDF</span>
            </div>

            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-play-circle</v-icon>
                </div>
                <span>Mpv player</span>
            </div>

            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-chat-processing</v-icon>
                </div>
                <span>Manga</span>
            </div>
            -->
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                loading: true,
                websiteImportSupported: false,
            }
        },
        props: {
            language: String
        },
        mounted() {
            axios.get('/config/get/linguacafe.languages.website_import_supported_languages').then((response) => {
                this.websiteImportSupported = response.data.includes(this.$props.language);
                this.loading = false;
            });
        },
        methods: {
            selectImportType(type) {
                this.$emit('import-type-selected', type);
            }
        }
    }
</script>
