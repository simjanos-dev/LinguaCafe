<template>
    <div>
        <label class="font-weight-bold">Files</label>
        <div class="import-type-group">
            <div class="import-type-button rounded-lg mx-2 mb-4" @click="selectImportType('e-book')">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-book</v-icon>
                </div>
                <span>E-book</span>
            </div>
            
            <!--
            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-file-document</v-icon>
                </div>
                <span>PDF</span>
            </div>

            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-subtitles</v-icon>
                </div>
                <span>Subtitle</span>
                
            </div>
            -->
        </div>

        <!--
        <label class="font-weight-bold mt-8">Media</label>
        <div class="import-type-group">
            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-youtube</v-icon>
                </div>
                <span>Youtube</span>
            </div>

            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-movie</v-icon>
                </div>
                <span>Jellyfin</span>
            </div>

            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-play-circle</v-icon>
                </div>
                <span>mpv player</span>
            </div>
        </div>

        <label class="font-weight-bold mt-8">Manga</label>
        <div class="import-type-group">
            <div class="import-type-button rounded-lg mx-2 mb-4">
                <div class="import-type-button-icon-box">
                    <v-icon large>mdi-chat-processing</v-icon>
                </div>
                <span>Manga</span>
            </div>
        </div>
        -->
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
            }
        },
        props: {
        },
        mounted() {
        },
        methods: {
            selectImportType(type) {
                this.$emit('import-type-selected', type);
            }
        }
    }
</script>
