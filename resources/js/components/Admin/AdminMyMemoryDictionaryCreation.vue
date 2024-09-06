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
                <!-- Name -->
                <label class="font-weight-bold">Dictionary name</label>
                <v-text-field 
                    v-model="dictionary.name"
                    filled
                    dense
                    rounded
                    disabled
                    placeholder="Dictionary name"
                    maxlength="16"
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
                    :error-messages=" isFormValid ? [] : ['The source and target language cannot be the same!']"
                    @change="updateDictionaryName(); validateForm();"
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
                            The language that MyMemory translates to.
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
                    :error-messages=" isFormValid ? [] : ['The source and target language cannot be the same!']"
                    @change="updateDictionaryName(); validateForm();"
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
                MyMemory dictionary has been created successfully.
            </v-alert>

            <!-- Error message -->
            <v-alert
                v-if="createResult === 'error'"
                class="rounded-lg mt-2"
                color="error"
                type="error"
                border="left"
            >
                An error has occurred while creating the MyMemory dictionary.
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
                    :disabled="createResult === 'saving' || !isFormValid || loading"
                    @click="createMyMemoryDictionary"
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
                isFormValid: true,
                dictionary: {
                    sourceLanguage: 'english',
                    targetLanguage: 'english',
                    color: '#0055B7',
                    name: '',
                },
            };
        },
        mounted: function() {
            axios.get('/config/get/linguacafe.languages.my_memory_supported_target_languages').then((response) => {
                this.loading = false;

                // add supported source languages
                Object.keys(response.data).forEach((languageName) => {
                    this.supportedLanguages.push({
                        name: languageName.toLowerCase(),
                        selected: false
                    });
                });

                this.updateDictionaryName();
                this.validateForm();
            });
        },
        methods: {
            validateForm() {
                this.isFormValid = (this.dictionary.sourceLanguage !== this.dictionary.targetLanguage);
            },
            updateDictionaryName() {
                this.dictionary.name = 
                    'MyMemory ' + 
                    this.dictionary.sourceLanguage[0].toUpperCase() + 
                    this.dictionary.sourceLanguage[1].toUpperCase() + 
                    ' - ' + 
                    this.dictionary.targetLanguage[0].toUpperCase() + 
                    this.dictionary.targetLanguage[1].toUpperCase();
            },
            createMyMemoryDictionary() {
                this.createResult = 'saving';
                axios.post('/dictionaries/create-my-memory', this.dictionary).then((response) => {
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
