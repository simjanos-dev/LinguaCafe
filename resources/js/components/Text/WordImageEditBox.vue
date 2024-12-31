<template>
    <div id="word-image-search" class="d-block w-full" @mouseup.stop.prevent="">
        <!-- Current image -->
        <div v-if="!searching">
            <template v-if="$props.currentImage === null">
                No image
            </template>
            <template v-else>
                {{ $props.currentImage }}
            </template>

            <div class="d-flex">
                <v-spacer/>
                <v-btn 
                    rounded 
                    depressed 
                    small
                    color="primary"
                    @click="searchImages"
                >Search image</v-btn>
            </div>
        </div>

        <!-- Image search -->
        <div 
            v-if="searching"
            class="d-block w-full" 
        >
            <div class="d-flex justify-end">
                <!-- Search engine -->
                <div id="search-engine-select-box">
                    <v-select
                        v-model="selectedSearchEngine"
                        :items="searchEngines"
                        item-text="name"
                        item-value="id"
                        class="mr-2"
                        dense
                        rounded
                        filled
                        hide-details
                        attach="#search-engine-select-box"
                        @change="searchImages"
                    >
                    </v-select>
                </div>

                <!-- Search options -->
                <div id="image-type-select-box">
                    <v-select
                        v-model="selectedImageType"
                        :items="imageTypes"
                        item-text="name"
                        item-value="id"
                        class="ml-2"
                        dense
                        rounded
                        filled
                        hide-details
                        attach="#image-type-select-box"
                        @change="searchImages"
                    ></v-select>
                </div>
            </div>

            <!-- Search term -->
            <v-text-field
                v-model="searchTerm"
                filled
                rounded
                dense
                hide-details
                class="my-2"
                prepend-inner-icon="mdi-magnify"
                placeholder="Search term"
                @keydown.stop=""
                @keydown.enter.stop="searchImages"
                
            ></v-text-field>
                <!-- @keyup.enter="makeRequest"
                @keyup="save" -->
            
            <!-- Loading indicator -->
            <div class="d-flex justify-center mt-8" v-if="loading">
                <v-progress-circular
                    :size="32"
                    color="primary"
                    indeterminate
                />
            </div>
            
            <!-- Images -->
            <div class="images-box px-2" :style="{height: height}">
                <v-img
                    v-for="(image, imageIndex) in images"
                    :key="imageIndex"
                    :src="image.small"
                    width="100%"
                    :aspect-ratio="16/9"
                    class="rounded-lg my-4"
                    style="box-sizing: border-box;"
                />
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            currentImage: {
                type: [String, null],
                default: null,
            },
            height: {
                type: String,
                default: '100%'
            }
        },
        computed: {
        },
        watch: {
        },
        data: function() {
            return {
                images: [],
                loading: false,
                searching: false,
                searchTerm: '',
                selectedSearchEngine: 0,
                searchEngines: [
                    {
                        id: 0,
                        name: 'Bing',
                        dataName: 'bing',
                    }
                ],
                selectedImageType: 0,
                imageTypes: [
                    {
                        id: 0,
                        name: 'All',
                        dataName: 'all',
                    },
                    {
                        id: 1,
                        name: 'Photographs',
                        dataName: 'photo-photo',
                    },
                    {
                        id: 2,
                        name: 'Clipart',
                        dataName: 'photo-clipart',
                    },
                    {
                        id: 3,
                        name: 'Line drawing',
                        dataName: 'photo-linedrawing',
                    }
                ],
            };
        },
        mounted: function() {
            this.setSearchTerm()
        },
        methods: {
            searchImages() {
                if (this.loading || !this.searchTerm.length) {
                    return
                }
                
                this.images = []
                this.searching = true
                this.loading = true

                const imageType = this.imageTypes[this.selectedImageType].dataName
                const searchEngine = this.searchEngines[this.selectedSearchEngine].dataName
                axios.get(`/images/search/${imageType}/${searchEngine}/${this.searchTerm}`).then((response) => {
                    this.loading = false
                    this.images = response.data.data;
                }).catch((error) => {
                    this.loading = false
                })
            },
            setSearchTerm() {
                this.searchTerm = ''

                if (this.$store.state.vocabularyBox.phrase.length) {
                    this.searchTerm = this.getPhraseString();
                    return
                }

                if (this.$store.state.vocabularyBox.baseWord.length) {
                    this.searchTerm = this.$store.state.vocabularyBox.baseWord;
                    return
                }

                if (this.$store.state.vocabularyBox.word.length) {
                    this.searchTerm = this.$store.state.vocabularyBox.word;
                    return
                }


            },
            getPhraseString() {
                let phraseString = ''

                this.$store.state.vocabularyBox.phrase.forEach((word) => {
                    phraseString += word.word

                    if (word.spaceAfter) {
                        phraseString += ' '
                    }
                })

                return phraseString
            }
        }
    }
</script>