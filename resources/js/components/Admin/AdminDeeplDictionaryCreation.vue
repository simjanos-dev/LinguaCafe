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
                    :items="supportedSourceLanguages"
                    item-value="name"
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
                            The language that DeepL translates to.
                        </v-card>
                    </v-menu>
                </label>

                <v-select
                    v-model="dictionary.targetLanguage"
                    :items="supportedTargetLanguages"
                    item-value="name"
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
                DeepL dictionary has been created successfully.
            </v-alert>

            <!-- Error message -->
            <v-alert
                v-if="createResult === 'error'"
                class="rounded-lg mt-2"
                color="error"
                type="error"
                border="left"
            >
                An error has occurred while creating the DeepL dictionary.
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
                    :disabled="createResult === 'saving' || !isFormValid" 
                    @click="createDeeplDictionary"
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
    export default {
        props: {
            language: String,
        },
        data: function() {
            return {
                loading: true,
                createResult: '',
                colorPicker: false,
                supportedSourceLanguages: [],
                supportedTargetLanguages: [],
                isFormValid: true,
                dictionary: {
                    sourceLanguage: this.$props.language,
                    targetLanguage: 'english',
                    color: '#97BAE0',
                    name: '',
                },
            };
        },
        mounted: function() {
            axios.all([
                axios.get('/config/get/linguacafe.languages.supported_languages'),
                axios.get('/config/get/linguacafe.languages.deepl_supported_target_languages')
            ]).then(axios.spread((response1, response2) => {
                this.loading = false;

                // add supported source languages
                for (let languageIndex = 0; languageIndex < response1.data.length; languageIndex++) {
                    this.supportedSourceLanguages.push({
                        name: response1.data[languageIndex].toLowerCase(),
                        selected: false
                    });
                }

                // add supported target languages
                for (let languageIndex = 0; languageIndex < response2.data.length; languageIndex++) {
                    this.supportedTargetLanguages.push({
                        name: response2.data[languageIndex].toLowerCase(),
                        selected: false
                    });
                }

                this.updateDictionaryName();
            }));
        },
        methods: {
            validateForm() {
                this.isFormValid = (this.dictionary.sourceLanguage !== this.dictionary.targetLanguage);
            },
            updateDictionaryName() {
                this.dictionary.name = 
                    'DeepL ' + 
                    this.dictionary.sourceLanguage[0].toUpperCase() + 
                    this.dictionary.sourceLanguage[1].toUpperCase() + 
                    ' - ' + 
                    this.dictionary.targetLanguage[0].toUpperCase() + 
                    this.dictionary.targetLanguage[1].toUpperCase();
            },
            createDeeplDictionary() {
                this.createResult = 'saving';
                axios.post('/dictionaries/create-deepl', this.dictionary).then((response) => {
                    if (response.status === 200) {
                        this.createResult = 'success';
                        this.$emit('import-finished');
                        setTimeout(this.close, 1000);
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
