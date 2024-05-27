<template>
    <v-card 
        id="custom-dictionary-import-dialog" 
        class="rounded-lg"
        :loading="configFileLoading || fileTestLoading || importing"
    >
        <!-- Title bar -->
        <v-card-title>
            <span class="text-h5">Dictionary import</span>
            <v-spacer></v-spacer>
            <v-btn icon @click="close"> 
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-card-title>

        <!-- Card text -->
        <v-card-text>
            <v-stepper id="import-stepper" v-model="stepperPage" elevation="0" class="pb-0" min-height="620px">
                <!-- Stepper header -->
                <v-stepper-header>
                    <v-stepper-step :complete="stepperPage > 1" step="1">
                        Dictionary information
                        <small>Name, database table, language</small>
                    </v-stepper-step>
                    <v-divider/>
                    
                    <v-stepper-step :complete="stepperPage > 2" step="2">
                        Dictionary file
                        <small>Select the dictionary file</small>
                    </v-stepper-step>
                    <v-divider/>

                    <v-stepper-step :complete="stepperPage > 3" step="3">
                        Overview
                        <small>Confirm that all data is correct</small>
                    </v-stepper-step>

                    <v-stepper-step :complete="stepperPage > 4" step="4">
                        Finish
                    </v-stepper-step>
                </v-stepper-header>

                <v-stepper-items>
                    <!-- Step 1: dictionary data -->
                    <v-stepper-content step="1">
                        <div v-if="!configFileLoading">
                            <!-- Source language -->
                            <label class="font-weight-bold">Source language</label>
                            <v-select
                                v-model="dictionary.sourceLanguage"
                                :items="sourceLanguages"
                                item-value="name"
                                placeholder="Source language"
                                dense
                                filled
                                rounded
                                @change="updateDatabaseName"
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
                            <label class="font-weight-bold">Target language</label>
                            <v-select
                                v-model="dictionary.targetLanguage"
                                :items="targetLanguages"
                                item-value="name"
                                placeholder="Target language"
                                dense
                                filled
                                rounded
                                @change="updateDatabaseName"
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

                            <!-- Dictionary name -->
                            <label class="font-weight-bold">Dictionary name</label>
                            <v-text-field 
                                v-model="dictionary.name"
                                filled
                                dense
                                rounded
                                placeholder="Dictionary name"
                                :rules="rules.dictionaryName"
                                @keyup="updateDatabaseName"
                                @change="updateDatabaseName"
                                maxlength="16"
                            ></v-text-field>
                            
                            <!-- Database table name -->
                            <label class="font-weight-bold">Database table name</label>
                            <v-text-field 
                                v-model="dictionary.databaseName"
                                class="mb-3"
                                color="black"
                                filled
                                dense
                                rounded
                                persistent-hint
                                hint="Can only contain lowercase letters, number and underscore."
                                placeholder="database_name"
                                :prefix="dictionary.databasePrefix"
                                :rules="rules.databaseName"
                                maxlength="28"
                            ></v-text-field>

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
                        </div>
                    </v-stepper-content>

                    <!-- Step 2: dictionary file -->
                    <v-stepper-content step="2">
                        <v-alert
                            class="rounded-lg"
                            color="primary"
                            type="info"
                            border="left"
                            dark
                        >
                            You can import a custom dictionary from a .csv file. It has to have 2 columns, the first containing a word
                            and the second containing the translations. You can provide multiple translations for a single word by 
                            separating them with a semicolon ";" character. Example:<br><br>

                            Word|Translation<br>
                            å gjøre|to do;to make<br>
                            å bygge|to build;to construct<br>
                            å elske|to love
                        </v-alert>
                        
                        <label class="font-weight-bold">Header</label>
                        <v-switch
                            v-model="dictionary.csvSkipHeader"
                            class="mt-0"
                            color="primary"
                            label="Skip first line"
                            @change="fileInputChange"
                        ></v-switch>

                        <label class="font-weight-bold">Delimiter character</label>
                        <v-text-field 
                            v-model="dictionary.csvDelimiter"
                            filled
                            dense
                            rounded
                            placeholder="Delimiter character"
                            @change="fileInputChange"
                            maxlength="1"
                            :rules="rules.csvDelimiter"
                        ></v-text-field>

                        <label class="font-weight-bold">Dictionary file</label>
                        <v-file-input
                            v-model="dictionary.file"
                            filled
                            dense
                            rounded
                            placeholder="Select a file"
                            accept=".csv"
                            prepend-icon="mdi-file-delimited"
                            @change="fileInputChange"
                        ></v-file-input>
                        <v-alert
                            v-if="!fileTestLoading && fileTestError"
                            class="rounded-lg"
                            color="error"
                            type="error"
                            border="left"
                            dark
                        >
                            There has been an error reading the file. Please make sure it follows the correct format and try again.
                        </v-alert>
                        <v-alert
                            v-if="dictionary.file && !fileTestLoading && !fileTestError"
                            class="rounded-lg"
                            color="success"
                            type="success"
                            border="left"
                            dark
                        >
                            The file has been tested without any errors. It contains {{ fileRecordCount }} records.
                        </v-alert>
                    </v-stepper-content>
                    
                    <!-- Step 3: overview -->
                    <v-stepper-content step="3">
                        <label class="font-weight-bold">Overview</label>
                        <v-simple-table class="border no-hover rounded-lg" v-if="dictionary.file">
                            <tbody>
                                <tr>
                                    <td class="font-weight-bold">Source language:</td>
                                    <td>
                                        <img 
                                            :src="'/images/flags/' + dictionary.sourceLanguage.toLowerCase() + '.png'" 
                                            class="mr-2 border" 
                                            width="40" 
                                            height="26"
                                        />
                                        {{ dictionary.sourceLanguage }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Target language:</td>
                                    <td>
                                        <img 
                                            :src="'/images/flags/' + dictionary.targetLanguage.toLowerCase() + '.png'" 
                                            class="mr-2 border" 
                                            width="40" 
                                            height="26"
                                        />
                                        {{ dictionary.targetLanguage }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Dictionary name:</td>
                                    <td>{{ dictionary.name }} ({{ dictionary.databasePrefix + dictionary.databaseName }})</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Color:</td>
                                    <td>
                                        <v-card
                                            class="border"
                                            outlined
                                            :color="dictionary.color"
                                            width="48px"
                                            height="26px"
                                        ></v-card>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">File:</td>
                                    <td>{{ dictionary.file.name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Skip csv header:</td>
                                    <td>{{ dictionary.csvSkipHeader ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Csv delimiter character:</td>
                                    <td>{{ dictionary.csvDelimiter }}</td>
                                </tr>
                            </tbody>
                        </v-simple-table>

                        <label class="font-weight-bold mt-4">Sample</label>
                        <v-simple-table dense class="no-hover border rounded-lg">
                            <thead>
                                <tr>
                                    <th class="text-center">Word</th>
                                    <th class="text-center">Translation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(sample, index) in fileTestSample" :key="index">
                                    <td class="text-center">{{ sample.word }}</td>
                                    <td class="text-center">{{ sample.translation }}</td>
                                </tr>
                            </tbody>
                        </v-simple-table>
                    </v-stepper-content>

                    <!-- Step 4: finish -->
                    <v-stepper-content step="4">
                        <div v-if="importResult == 'success'">
                            <v-alert
                                class="rounded-lg"
                                color="success"
                                type="success"
                                border="left"
                                dark
                            >
                                Dictionary has been successfully imported.
                            </v-alert>
                        </div>

                        <div v-if="importResult !== 'success'">
                            <v-alert
                                class="rounded-lg"
                                color="error"
                                type="error"
                                border="left"
                                dark
                            >
                                An error has occurred while importing the dictionary.<br><br>
                                {{ importResult }}
                            </v-alert>
                        </div>
                        
                    </v-stepper-content>

                </v-stepper-items>
            </v-stepper>
        </v-card-text>
        
        <!-- Action bar -->
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn 
                v-if="stepperPage == 1"
                rounded 
                text 
                @click="backToDictionaries"
            >Back</v-btn>

            <v-btn
                v-if="(stepperPage > 1 && stepperPage < 4) || (stepperPage == 4 && importResult !== 'success')"
                rounded 
                text 
                @click="stepperPage --;"
            >
                Back
            </v-btn>
            <v-btn
                v-if="stepperPage == 4 && importResult == 'success'"
                rounded 
                text 
                @click="close"
            >
                Close
            </v-btn>
            <v-btn
                v-if="stepperPage < 3"
                rounded
                depressed
                color="primary"
                :disabled="(stepperPage == 1 && (!this.dictionary.nameValidated || !this.dictionary.databaseValidated)) ||
                    (stepperPage == 2 && (fileTestLoading || fileTestError || !this.dictionary.file))"
                :loading="stepperPage == 2 && fileTestLoading"
                @click="stepperPage ++;"
            >
                Continue
            </v-btn>

            <v-btn
                v-if="stepperPage == 3"
                rounded
                depressed
                color="primary"
                @click="importDictionary"
                :loading="importing"
                :disabled="importing"
            >
                Import
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
    export default {
        props: {
            language: String
        },
        data: function() {
            return {
                databaseNameLanguageCodes: null,
                configFileLoading: true,
                stepperPage: 1,
                importing: false,
                importResult: '',
                fileTestLoading: false,
                fileTestError: false,
                fileTestSample: [],
                fileRecordCount: -1,
                colorPicker: false,
                dictionary: {
                    name: '',
                    databaseName: '',
                    databasePrefix: 'dict_jp_',
                    color: '#B59686',
                    sourceLanguage: this.$props.language,
                    targetLanguage: 'english',
                    file: null,
                    csvDelimiter: '|',
                    csvSkipHeader: false,


                    nameValidated: false,
                    databaseValidated: false,
                },

                sourceLanguages: [],
                targetLanguages: [],

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
                    databaseName: [
                        value => {
                            if (!value.length) {
                                this.dictionary.databaseValidated = false;
                                return 'You must type in a database name!';
                            }

                            let regex = /^[a-z0-9_]+$/;
                            if (!regex.test(value)) {
                                this.dictionary.databaseValidated = false;
                                return 'Can only contain lowercase letters, numbers and underscore!';
                            }

                            this.dictionary.databaseValidated = true;
                            return true;
                        }
                    ],
                    csvDelimiter: [
                        value => {
                            if (!value.length) {
                                return 'You must choose a delimiter character.';
                            }

                            if (value == ';') {
                                return 'You cannot use ; character as a delimiter, because it is used to separate multiple translations.';
                            }

                            return true;
                        }
                    ],
                }
            };
        },
        mounted: function() {
            axios.all([
                axios.get('/config/get/linguacafe.languages.supported_languages'),
                axios.get('/config/get/linguacafe.languages.supported_target_languages'),
                axios.get('/config/get/linguacafe.languages.database_name_language_codes')
            ]).then(axios.spread((response1, response2, response3) => {
                this.configFileLoading = false;

                // add supported source languages
                for (let languageIndex = 0; languageIndex < response1.data.length; languageIndex++) {
                    this.sourceLanguages.push({
                        name: response1.data[languageIndex].toLowerCase(),
                        selected: false
                    });
                }

                // add supported target languages
                for (let languageIndex = 0; languageIndex < response2.data.length; languageIndex++) {
                    this.targetLanguages.push({
                        name: response2.data[languageIndex].toLowerCase(),
                        selected: false
                    });
                }

                // update database name
                this.databaseNameLanguageCodes = response3.data;
                this.updateDatabaseName();
            }));
        },
        methods: {
            updateDatabaseName() {
                this.dictionary.databasePrefix = 'dict_' + this.databaseNameLanguageCodes[this.dictionary.sourceLanguage] + '_';
                this.dictionary.databaseName = this.dictionary.name.split(' ').join('_').toLowerCase().replace(/[^a-z0-9_]/g, '');

                // remove underscores from the start of the text
                while (this.dictionary.databaseName[0] == '_') {
                    this.dictionary.databaseName = this.dictionary.databaseName.slice(1);
                }

                // remove underscores from the end of the text
                while (this.dictionary.databaseName[this.dictionary.databaseName.length - 1] == '_') {
                    this.dictionary.databaseName = this.dictionary.databaseName.slice(0, -1);
                }
                
            },
            fileInputChange() {
                if (!this.dictionary.csvDelimiter.length || this.dictionary.csvDelimiter == ';') {
                    this.fileTestError = false;
                    return;
                }
                
                if (this.dictionary.file === null || this.dictionary.file === undefined) {
                    this.dictionary.file = null;
                    this.fileTestError = false;
                    return;
                }
                
                this.fileTestSample = [];
                this.fileTestLoading = true;

                let formData = new FormData();
                formData.append("dictionary", this.dictionary.file);
                formData.append("delimiter", this.dictionary.csvDelimiter);
                formData.append("skipHeader", this.dictionary.csvSkipHeader);

                axios.post('/dictionaries/test-csv-file', formData).then((response) => {
                    this.fileTestError = response.data.status !== 'success';
                    this.fileTestLoading = false;

                    if (this.fileTestError) {
                        this.dictionary.file = null;
                    } else {
                        this.fileTestSample = response.data.sample;
                        this.fileRecordCount = response.data.recordCount;
                    }
                });
            },
            importDictionary() {
                this.importing = true;
                let formData = new FormData();
                formData.append("dictionary", this.dictionary.file);
                formData.append("delimiter", this.dictionary.csvDelimiter);
                formData.append("skipHeader", this.dictionary.csvSkipHeader);
                formData.append("dictionaryName", this.dictionary.name);
                formData.append("databaseName", this.dictionary.databasePrefix + this.dictionary.databaseName);
                formData.append("sourceLanguage", this.dictionary.sourceLanguage.toLowerCase());
                formData.append("targetLanguage", this.dictionary.targetLanguage.toLowerCase());
                formData.append("color", this.dictionary.color);

                axios.post('/dictionaries/import-csv-file', formData).then((response) => {
                    this.importing = false;
                    this.stepperPage ++;
                    this.importResult = response.data;
                });
            },
            backToDictionaries() {
                this.$emit('back-to-dictionaries');
            },
            close() {
                if (this.stepperPage == 4) {
                    this.$emit('import-finished');
                }

                this.$emit('close');
            }
        }
    }
</script>
