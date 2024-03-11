<template>
    <div class="d-flex flex-column align-stretch">
        <!-- Website url input -->
        <label class="font-weight-bold">Website url</label>
        <v-text-field
            v-model="url"
            filled
            dense
            rounded
            placeholder="Website url"
            prepend-icon="mdi-web"
            ref="url"
            :rules="[rules.url]"
            @change="urlChanged"
        ></v-text-field>

        <!-- Website text -->
        <label class="font-weight-bold" v-if="text.length || loading">Website text</label>
        <div 
            v-if="text.length"
            id="website-text"
            class="rounded-xl pa-6"
            v-html="displayedText"
        ></div>

        <!-- Website text loading -->
        <div class="d-flex justify-center">
            <v-progress-circular
                v-if="loading"
                indeterminate
                color="primary"
            ></v-progress-circular>
        </div>

        <!-- Website request error -->
        <v-alert
            v-if="!loading && websiteRequestStatus === 'error'"
            border="left"
            type="error"
        >
            An error has occurred while requesting the website content.
        </v-alert>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                loading: false,
                websiteRequestStatus: '',
                text: '',
                displayedText: '',
                url: '',
                rules: {
                    url: (value) => {
                        let pattern = /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/;
                        if (!value.match(pattern)) {
                            return 'Invalid url.';
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
            this.textChanged();
        },
        methods: {
            urlChanged() {
                if (this.$refs.url.validate()) {
                    this.loading = true;
                    this.text = '';
                    this.websiteRequestStatus = '';

                    axios.post('/website/get-text', {
                        url: this.url,
                    }).then((response) => {
                        this.text = response.data;
                        this.displayedText = this.text.replace(/(?:\r\n|\r|\n)/g, '<br>');
                        this.textChanged();
                        this.loading = false;
                        this.websiteRequestStatus = 'success';
                    }).catch((error) => {
                        this.loading = false;
                        this.websiteRequestStatus = 'error';
                    });;
                } else {
                    this.text = '';
                    this.displayedText = '',
                    this.textChanged();
                }
            },
            textChanged() {
                if (!this.$refs.url.validate()) {
                    this.$emit('text-selected', {
                        text: '',
                        isImportSourceValid: false
                    });

                    return;
                }

                this.$emit('text-selected', {
                    text: this.text,
                    isImportSourceValid: true
                });
            }
        }
    }
</script>
