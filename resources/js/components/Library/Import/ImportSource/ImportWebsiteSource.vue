<template>
    <div class="d-flex flex-column align-stretch">
        <!-- Website url input -->
        <label class="font-weight-bold">Website url</label>
        <div class="d-flex flex-wrap flex-md-nowrap">
            <v-text-field
                class="website-url-input"
                v-model="url"
                filled
                dense
                rounded
                placeholder="Website url"
                prepend-icon="mdi-web"
                ref="url"
                :rules="[rules.url]"
                @keyup.enter="retrieveWebsiteTExt"
            ></v-text-field>
            <v-btn
                id="retrieve-website-button"
                depressed
                rounded
                color="primary"
                @click="retrieveWebsiteTExt"
            >
                Retrieve
            </v-btn>
        </div>

        <!-- Website text -->
        <v-textarea
            v-if="websiteRequestStatus == 'success'"
            id="website-import-input"
            v-model="text"
            filled
            dense
            rounded
            persistent-hint
            placeholder="Website text"
            maxlength="250000"
            counter="250000"
            no-resize
            @keyup="textChanged"
        ></v-textarea>

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
            retrieveWebsiteTExt() {
                if (this.$refs.url.validate()) {
                    this.loading = true;
                    this.text = '';
                    this.websiteRequestStatus = '';

                    axios.post('/website/get-text', {
                        url: this.url,
                    }).then((response) => {
                        this.text = response.data;
                        this.textChanged();
                        this.loading = false;
                        this.websiteRequestStatus = 'success';
                    }).catch((error) => {
                        this.loading = false;
                        this.websiteRequestStatus = 'error';
                    });;
                } else {
                    this.text = '';
                    this.textChanged();
                }
            },
            textChanged() {
                if (!this.$refs.url.validate() || !this.text.length) {
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
