<template>
    <v-card class="rounded-lg" :loading="loading || createResult === 'saving'">
        <!-- Title -->
        <v-card-title>
            <v-icon class="mr-2">mdi-file-edit</v-icon>
            <span class="text-h5">Edit dictionary</span>
            <v-spacer></v-spacer>
            <v-btn icon @click="close">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-card-title>

        <!-- Content -->
        <v-card-text class="pt-4 pb-6" v-if="dictionary">

            <!-- Forms -->
            <template v-if="createResult !== 'success'">
                <!-- Host -->
                <label class="font-weight-bold">API host</label>
                <v-text-field 
                    v-model="dictionary.api_host"
                    filled
                    dense
                    rounded
                    placeholder="API host"
                ></v-text-field>
            

                <!-- Name -->
                <label class="font-weight-bold">Dictionary name</label>
                <v-text-field 
                    v-model="dictionary.name"
                    filled
                    dense
                    rounded
                    placeholder="Dictionary name"
                    maxlength="16"
                    :rules="rules.dictionaryName"
                ></v-text-field>

                <!-- Source language -->
                <label class="font-weight-bold">
                    Source language
                    
                    <!-- Source language info box -->
                    <v-menu offset-y nudge-top="-12px">
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon class="ml-1" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                        </template>
                        <v-card outlined class="rounded-lg pa-4" width="320px">
                            The language that you are learning.
                        </v-card>
                    </v-menu>
                </label>

                <v-select
                    v-model="dictionary.sourceLanguage"
                    :items="supportedLanguages"
                    item-value="name"
                    item-name="name"
                    placeholder="Language"
                    dense
                    filled
                    rounded
                    :error-messages=" dictionary.languagesValid ? [] : ['The source and target language cannot be the same!']"
                    @change="validateForm();"
                >
                    <template v-slot:selection="{ item, index }">
                        <img class="mr-2 border" :src="'/images/flags/' + item.name + '.png'" width="40" height="26">
                        <span class="text-capitalize">{{ item.name }}</span>
                    </template>
                    <template v-slot:item="{ item }">
                        <img class="mr-2 border" :src="'/images/flags/' + item.name + '.png'" width="40" height="26">
                        <span class="text-capitalize">{{ item.name }}</span>
                    </template>
                </v-select>

                <!-- Target language -->
                <label class="font-weight-bold">
                    Target language

                    <!-- Target language info box -->
                    <v-menu offset-y nudge-top="-12px">
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon class="ml-1" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                        </template>
                        <v-card outlined class="rounded-lg pa-4" width="320px">
                            The language that the custom API translates to.
                        </v-card>
                    </v-menu>
                </label>

                <v-select
                    v-model="dictionary.targetLanguage"
                    :items="supportedLanguages"
                    item-value="name"
                    item-name="name"
                    placeholder="Language"
                    dense
                    filled
                    rounded
                    :error-messages=" dictionary.languagesValid ? [] : ['The source and target language cannot be the same!']"
                    @change="validateForm();"
                >
                    <template v-slot:selection="{ item, index }">
                        <img class="mr-2 border" :src="'/images/flags/' + item.name + '.png'" width="40" height="26">
                        <span class="text-capitalize">{{ item.name }}</span>
                    </template>
                    <template v-slot:item="{ item }">
                        <img class="mr-2 border" :src="'/images/flags/' + item.name + '.png'" width="40" height="26">
                        <span class="text-capitalize">{{ item.name }}</span>
                    </template>
                </v-select>

                <!-- Display color -->
                <label class="font-weight-bold">Display color</label>
                <v-menu
                    v-model="colorPicker"
                    width="290px"
                    offset-y
                    nudge-top="-10px"
                    right
                    :close-on-content-click="false"
                >
                    <template v-slot:activator="{ on, attrs }">
                        <v-card
                            class="border"
                            outlined
                            :color="dictionary.color"
                            width="64px"
                            height="32px"
                            @click="colorPicker = !colorPicker;"
                        ></v-card>
                    </template>
                    <v-color-picker hide-inputs v-model="dictionary.color" />
                </v-menu>
            </template>

            <!-- Success message -->
            <v-alert
                v-if="createResult === 'success'"
                class="rounded-lg mt-2"
                color="success"
                type="success"
                border="left"
            >
                Custom API dictionary has been created successfully.
            </v-alert>

            <!-- Error message -->
            <v-alert
                v-if="createResult === 'error'"
                class="rounded-lg mt-2"
                color="error"
                type="error"
                border="left"
            >
                An error has occurred while creating the custom API dictionary.
            </v-alert>

        </v-card-text>

        <!-- Action buttons -->
        <v-card-actions>
            <v-spacer></v-spacer>

            <!-- Buttons before successfull save -->
            <template v-if="createResult !== 'success'">
                <v-btn rounded text @click="close">Cancel</v-btn>
                <v-btn 
                    rounded
                    depressed 
                    color="primary" 
                    :loading="createResult === 'saving'" 
                    :disabled="createResult === 'saving' || !dictionary.languagesValid || loading"
                    @click="createCustomApiDictionary"
                >Create</v-btn>
            </template>

            <!-- Buttons after successfull save -->
            <template v-if="createResult === 'success'">
                <v-btn rounded text @click="close">Close</v-btn>
            </template>
        </v-card-actions>
    </v-card>
</template>

<script>
import { support } from 'jquery';

    export default {
        props: {
            language: String,
        },
        data: function() {
            return {
                loading: true,
                createResult: '',
                colorPicker: false,
                supportedLanguages: [],
                dictionary: {
                    sourceLanguage: this.$props.language,
                    targetLanguage: 'english',
                    color: '#a3a3a3',
                    name: 'Custom API',
                    api_host: 'http://host.docker.internal:1234',
                    nameValidated: false,
                    languagesValid: true,
                },
                rules: {
                    dictionaryName: [
                        value => {
                            if (!value.length) {
                                this.dictionary.nameValidated = false;
                                return 'You must type in a dictionary name!';
                            }

                            if (value.toLowerCase().includes('deepl')) {
                                this.dictionary.nameValidated = false;
                                return 'Cannot contain the word "deepl".';
                            }

                            if (value.toLowerCase() === 'jmdict') {
                                this.dictionary.nameValidated = false;
                                return 'Cannot be named jmdict.';
                            }

                            this.dictionary.nameValidated = true;
                            return true;
                        }
                    ],
                }
            };
        },
        mounted: function() {
            axios.get('/config/get/linguacafe.languages.supported_target_languages').then((response) => {
                this.loading = false;
                
                // add supported source languages
                response.data.forEach((languageName) => {
                    this.supportedLanguages.push({
                        name: languageName.toLowerCase(),
                    });
                });

                this.validateForm();
            });
        },
        methods: {
            validateForm() {
                this.dictionary.languagesValid = (this.dictionary.sourceLanguage !== this.dictionary.targetLanguage);
            },
            createCustomApiDictionary() {
                this.createResult = 'saving';
                axios.post('/dictionaries/create-custom-api', this.dictionary).then((response) => {
                    if (response.status === 200) {
                        this.createResult = 'success';
                        this.$emit('import-finished');
                    } else {
                        this.createResult = 'error';
                    }
                }).catch((error) => {
                    this.createResult = 'error';
                });
            },
            close() {
                this.$emit('close');
            }
        }
    }
</script>
