<template>
    <div>
        <!-- Youtube url input -->
        <label class="font-weight-bold">Youtube video url</label>
        <v-text-field
            v-model="url"
            filled
            dense
            rounded
            persistent-hint
            hint="Example: https://www.youtube.com/watch?v=aaaAaa1_A1A"
            placeholder="Youtube url"
            prepend-icon="mdi-youtube"
            @change="urlChanged"
            @keydown="handleKeydown"
        ></v-text-field>

        <!-- Subtitle list-->
        <div id="youtube-subtitles" class="mt-2" v-if="selectedSubtitle === -1 && (subtitlesLoaded || loading)">
            <!-- Subtitle list label -->
            <label class="font-weight-bold mt-2">Retrieved subtitles</label>

            <!-- Subtitles loading -->
            <div class="d-flex justify-center">
                <v-progress-circular
                    v-if="loading"
                    indeterminate
                    color="primary"
                ></v-progress-circular>
            </div>

            <!-- Url error -->
            <v-alert
                v-if="!loading && subtitles === null"
                border="left"
                type="error"
            >
                Could not retreive a list of subtitles. Please make sure that the url provided is correct, and your internet connection is stable.
            </v-alert>

            <!-- Subtitle list -->
            <div id="youtube-subtitles" class="mt-2" v-if="!loading && subtitles !== null">
                <div 
                    v-for="(subtitle, subtitleIndex) in subtitles" 
                    :key="subtitleIndex"
                    class="d-flex youtube-subtitle regular-list-height rounded-pill mb-2 pl-10"
                    @click="selectSubtitle(subtitleIndex)"
                >
                    <v-img 
                        :src="'/images/flags/' + subtitle.languageLowerCase.replace(' (auto-generated)', '') + '.png'"
                        class="border mr-4"
                        max-width="43" 
                        height="28"
                    />
                    {{ subtitle.language }}
                </div>

                <div 
                    v-if="!subtitles.length && !error"
                    class="d-flex youtube-subtitle regular-list-height rounded-pill my-2 pl-10"
                >
                    No subtitles found
                </div>
            </div>
        </div>

        <!-- Request error -->
        <v-alert
            v-if="error"
            class="mt-4"
            border="left"
            type="error"
        >
            An error has occurred while retrieving youtube subtitles.
        </v-alert>
        
        <!-- Selected subtitle text -->
        <div class="d-flex w-full mt-2" v-if="selectedSubtitle !== -1">
            <label 
                id="selected-youtube-subtitle-label"
                class="font-weight-bold" 
                v-if="selectedSubtitle !== -1"
            >
                Selected subtitle
            </label>
            
                <v-spacer />

            <v-btn
                v-if="$vuetify.breakpoint.smAndUp"
                rounded
                depressed
                color="primary"
                @click="unselectSubtitle"
            >Select another subtitle</v-btn>
        </div>
        <div 
            v-if="selectedSubtitle !== -1"
            id="selected-youtube-subtitle"
            class="rounded-xl pa-6 mt-2"
            v-html="subtitles[selectedSubtitle].displayText"
        ></div>
        <v-btn
            v-if="selectedSubtitle !== -1 && $vuetify.breakpoint.xsOnly"
            rounded
            depressed
            color="primary"
            class="mt-2"
            @click="unselectSubtitle"
        >Select another subtitle</v-btn>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                loading: false,
                subtitles: null,
                subtitlesLoaded: false,
                error: false,
                selectedSubtitle: -1,
                url: ''
            }
        },
        props: {
            language: String,
        },
        mounted() {
        },
        methods: {
            unselectSubtitle() {
                this.selectedSubtitle = -1;

                this.$emit('text-selected', {
                    text: '',
                    isImportSourceValid: false
                });
            },
            selectSubtitle(subtitleIndex) {
                this.selectedSubtitle = subtitleIndex;
                
                this.$emit('text-selected', {
                    text: this.subtitles[subtitleIndex].text,
                    isImportSourceValid: true
                });
            },
            handleKeydown(event) {
                if (event.which === 13) {
                    this.urlChanged();
                }
            },
            urlChanged() {
                this.selectedSubtitle = -1;
                this.loading = true;
                this.error = false;

                axios.post('/youtube/get-subtitle-list', {url: this.url}).then((response) => {
                    this.subtitles = response.data;

                    if (this.subtitles !== null) {
                        for (let i = 0; i < this.subtitles.length; i++) {
                            this.subtitles[i].displayText = this.subtitles[i].text.replaceAll("\n", "<br>");
                        }
                    }
                    
                    this.subtitlesLoaded = true;
                    this.loading = false;
                }).catch((error) => {
                    this.error = true;
                    this.loading = false;
                });
            }
        }
    }
</script>
