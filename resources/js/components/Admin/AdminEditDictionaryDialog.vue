<template>
    <v-dialog v-model="value" persistent max-width="700px" height="400px">
        <v-card class="rounded-lg" :loading="loading || saveResult === 'saving'">
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
                <template v-if="saveResult !== 'success'">
                    <!-- Name -->
                    <label class="font-weight-bold">Dictionary name</label>
                    <v-text-field 
                        v-model="dictionary.name"
                        filled
                        dense
                        rounded
                        :disabled="dictionary.database_table_name === 'API' || dictionary.name === 'JMDict'"
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
                        v-model="dictionary.source_language"
                        :items="supportedSourceLanguages"
                        item-value="name"
                        placeholder="Language"
                        dense
                        filled
                        rounded
                        :disabled="dictionary.database_table_name === 'API' || dictionary.name === 'JMDict'"
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
                    <template v-if="dictionary.database_table_name === 'API' && dictionary.name.includes('DeepL')">
                        <label class="font-weight-bold">
                            Target language

                            <!-- Target language info box -->
                            <v-menu offset-y nudge-top="-12px">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-icon class="ml-1" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                                </template>
                                <v-card outlined class="rounded-lg pa-4" width="320px">
                                    The language that the dictionary translates to. For example if it's a German -> English 
                                    dictionary, you should select English as the target language. Target language has no function, 
                                    it's just a visual help to arrange your dictionaries.
                                </v-card>
                            </v-menu>
                        </label>
                        
                        <v-select
                            v-model="dictionary.target_language"
                            :items="supportedDeeplTargetLanguages"
                            item-value="name"
                            placeholder="Language"
                            dense
                            filled
                            rounded
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
                    </template>

                    <!-- Target DeepL language -->
                    <template v-else>
                        <label class="font-weight-bold">
                            Target language

                            <!-- Target language info box -->
                            <v-menu offset-y nudge-top="-12px">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-icon class="ml-1" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                                </template>
                                <v-card outlined class="rounded-lg pa-4" width="320px">
                                    The language that the dictionary translates to. For example if it's a German -> English 
                                    dictionary, you should select English as the target language. Target language has no function, 
                                    it's just a visual help to arrange your dictionaries.
                                </v-card>
                            </v-menu>
                        </label>

                        <v-select
                            v-model="dictionary.target_language"
                            :items="supportedTargetLanguages"
                            item-value="name"
                            placeholder="Language"
                            dense
                            filled
                            rounded
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
                    </template>

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
                    
                    <!-- Enabled -->
                    <label class="font-weight-bold mt-6">Enabled</label>
                    <v-switch
                        color="primary"
                        class="mt-0"
                        v-model="dictionary.enabled" 
                    ></v-switch>
                </template>

                <!-- Success message -->
                <v-alert
                    v-if="saveResult === 'success'"
                    class="rounded-lg"
                    color="success"
                    type="success"
                    border="left"
                    dark
                >
                    Dictionary saved successfully.
                </v-alert>

            </v-card-text>

            <!-- Action buttons -->
            <v-card-actions>
                <v-spacer></v-spacer>

                <!-- Buttons before successfull save -->
                <template v-if="saveResult !== 'success'">
                    <v-btn rounded text @click="close">Cancel</v-btn>
                    <v-btn 
                        rounded
                        depressed 
                        color="primary" 
                        :loading="saveResult === 'saving'" 
                        :disabled="saveResult === 'saving'" 
                        @click="save"
                    >Save</v-btn>
                </template>

                <!-- Buttons after successfull save -->
                <template v-if="saveResult === 'success'">
                    <v-btn rounded text @click="close">Close</v-btn>
                </template>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
            dictionaryId: Number
        },
        emits: ['input'],
        data: function() {
            return {
                loading: true,
                saveResult: '',
                colorPicker: false,
                supportedSourceLanguages: [],
                supportedTargetLanguages: [],
                supportedDeeplTargetLanguages: [],
                dictionary: null,
            };
        },
        mounted: function() {
            axios.all([
                axios.get('/config/get/linguacafe.languages.supported_languages'),
                axios.get('/config/get/linguacafe.languages.supported_target_languages'),
                axios.get('/config/get/linguacafe.languages.deepl_supported_target_languages'),
                axios.get('/dictionaries/get/' + this.$props.dictionaryId),
            ]).then(axios.spread((response1, response2, response3, response4) => {
                this.loading = false;
                this.dictionary = response4.data;

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

                // add supported deepl target languages
                for (let languageIndex = 0; languageIndex < response3.data.length; languageIndex++) {
                    this.supportedDeeplTargetLanguages.push({
                        name: response3.data[languageIndex].toLowerCase(),
                        selected: false
                    });
                }
            }));
        },
        methods: {
            save() {
                this.saveResult = 'saving';
                axios.post('/dictionary/update', this.dictionary).then((response) => {
                    if (response.data === 'success') {
                        this.saveResult = 'success';
                        this.$emit('dictionary-saved');
                        setTimeout(this.close, 1000);
                    } else {
                        this.saveResult = 'error';
                    }
                });
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
