<template>
    <div id="word-image-search" class="d-block" @mouseup.stop.prevent="">
        <!-- Current image -->
        <div v-if="currentStep == 'selecting-method'">
            <v-img
                    v-if="$store.state.vocabularyBox.image"
                    ref="currentImage"
                    :src="'/images/' + imageTypeUrlSlug + '/get/' + $store.state.vocabularyBox.id + '?fileName=' + $store.state.vocabularyBox.image"
                    width="100%"
                    max-width="600px"
                    :aspect-ratio="16/9"
                    contain
                    class="rounded-lg mx-auto mb-2"
                />

            <div
                v-else 
                id="no-image-box"
                class="rounded-xl text-center mb-2"
                :class="[
                    this.$store.state.vocabularyBox.image ? 'pa-0' : 'py-2'
                ]"
            >
                <v-icon class="mr-1">mdi-image-remove</v-icon> No assigned image
            </div>

            <div class="d-flex">
                <v-btn 
                    v-if="this.$store.state.vocabularyBox.image"
                    rounded 
                    depressed 
                    small
                    color="error"
                    class="mr-1"
                    :loading="loading"
                    :disabled="loading"
                    @click="deleteImage"
                >
                    <v-icon>mdi-delete</v-icon>
                    Delete
                </v-btn>
                <v-spacer/>
                <v-btn 
                    rounded 
                    depressed 
                    small
                    color="primary"
                    class="mr-1"
                    :disabled="loading"
                    @click="currentStep = 'uploading'"
                >
                    <v-icon>mdi-upload</v-icon>
                    Upload
                </v-btn>
                <v-btn 
                    rounded 
                    depressed 
                    small
                    color="primary"
                    :disabled="loading"
                    @click="searchImages"
                >
                    <v-icon>mdi-magnify</v-icon>
                    Search
                </v-btn>
            </div>
        </div>

        <!-- Image upload -->
        <div 
            v-if="currentStep === 'uploading'"
            class="d-block" 
        >
            <v-form>
                <v-file-input
                    ref="imageFile"
                    v-model="imageFile"
                    filled
                    dense
                    rounded
                    clearable
                    accept=".jpg,.png"
                    placeholder="Select an image file"
                    prepend-icon="mdi-file"
                    :disabled="loading"
                    :rules="[rules.imageFileRule]"
                    @change="validateImage"
                ></v-file-input>
            </v-form>

            <div class="d-flex justify-end">
                <v-btn 
                    rounded 
                    text
                    small
                    @click="cancelUploading"
                    :disabled="loading"
                >
                    Cancel
                </v-btn>
                <v-btn 
                    rounded 
                    depressed 
                    small
                    color="primary"
                    class="ml-2"
                    @click="uploadImage"
                    :loading="loading"
                    :disabled="!imageFileValid || loading"
                >
                    <v-icon>mdi-upload</v-icon>
                    Upload
                </v-btn>
            </div>
        </div>

        <!-- Image search -->
        <div 
            v-if="currentStep === 'searching'"
            class="d-block w-full" 
        >
            <!-- Search term -->
            <div class="d-flex">
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
                    @change="searchImages"
                ></v-text-field>
            </div>
            
            
            <!-- Loading indicator -->
            <div class="d-flex justify-center mt-8" v-if="loading">
                <v-progress-circular
                    :size="32"
                    color="primary"
                    indeterminate
                />
            </div>

            <!-- Image search error -->
            <div class="d-flex flex-wrap justify-end my-1" v-if="searchError">
                <v-alert
                    dense
                    outlined
                    type="error"
                    class="rounded-lg mb-0"
                >
                    Image search sometimes returns with an empty page due to web scraping protection. Please try again.
                </v-alert>
                
                <v-btn 
                    rounded 
                    depressed 
                    small
                    color="primary"
                    class="my-2 ml-2"
                    @click="searchImages"
                >
                    Try again
                </v-btn>
            </div>
            
            <!-- Images -->
            <div
                class="images-box px-2"
                :style="{height: height}"
                v-if="!loading && images.length"
            >
                <v-img
                    v-for="(image, imageIndex) in images"
                    :key="imageIndex"
                    :src="image.small"
                    width="100%"
                    :aspect-ratio="16/9"
                    contain
                    class="rounded-lg my-4"
                    style="box-sizing: border-box;"
                    @click="setImageFromUrl(image)"
                />
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            height: {
                type: String,
                default: '100%'
            }
        },
        computed: {
            imageTypeUrlSlug(){
                if (this.$store.state.vocabularyBox.type === 'word') {
                    return 'word-image'
                }

                return 'phrase-image'
            }
        },
        watch: {
        },
        data: function() {
            return {
                currentImageKey: 0,
                images: [],
                loading: false,
                currentStep: 'selecting-method',
                searchError: false,
                searchTerm: '',
                imageFile: null,
                imageFileValid: false,

                rules: {
                    imageFileRule: (value) => {
                        if (value === null || value === undefined) {
                            return 'You must select a file.';
                        }
                        
                        let extension = value.name.split('.');
                        extension = extension[extension.length - 1];
                        if (!['jpg', 'png', 'webp'].includes(extension)) {
                            return 'The selected file must an image file (.jpg, .png, .webp).';
                        }

                        return true;
                    },
                }
            };
        },
        mounted: function() {
            this.setSearchTerm()
        }, 
        methods: {
            setImageFromUrl(image) {
                const targetId = this.$store.state.vocabularyBox.id

                this.loading = true
                axios.post(`/images/${this.imageTypeUrlSlug}/set-from-url/${targetId}`, {
                    url: image.original,
                }).then((response) => {
                    this.$emit('imageChanged', response.data.data.image)
                    this.currentStep = 'selecting-method';
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                })
            },
            validateImage() {
                this.imageFileValid = this.$refs.imageFile.validate();
                console.log('validateImage', this.$refs.imageFile.validate())
            },
            uploadImage() {
                if (!this.imageFileValid) {
                    return
                }

                let formData = new FormData();
                formData.append("imageFile", this.imageFile);

                this.loading = true;

                const targetId = this.$store.state.vocabularyBox.id

                axios.post(`/images/${this.imageTypeUrlSlug}/upload/${targetId}`, formData).then((response) => {
                    this.$emit('imageChanged', response.data.data.image)
                    this.currentStep = 'selecting-method';
                    this.resetImageFile()
                    this.loading = false;
                }).catch((error) => {
                    this.resetImageFile()
                    this.loading = false;
                });
            },
            resetImageFile() {
                this.imageFile = null
                this.imageFileValid = false
            },
            cancelUploading() {
                this.resetImageFile()
                this.currentStep = 'selecting-method';
                this.imageFile = null;
            },
            deleteImage() {
                this.loading = true

                const targetId = this.$store.state.vocabularyBox.id

                axios.delete(`/images/${this.imageTypeUrlSlug}/delete/${targetId}`).then((response) => {
                    this.$emit('imageChanged', null)
                    this.currentStep = 'selecting-method';
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    this.searchError = true
                })
            },
            searchImages() {
                if (this.loading || !this.searchTerm.length) {
                    return
                }
                
                this.images = []
                this.currentStep = 'searching'
                this.loading = true
                this.searchError = false
                
                axios.get(`/images/search/bing/${this.searchTerm}`).then((response) => {
                    this.loading = false
                    this.images = response.data.data;
                }).catch((error) => {
                    this.loading = false
                    this.searchError = true
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