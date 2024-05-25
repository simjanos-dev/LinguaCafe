<template>
    <v-card 
        id="supported-dictionary-import-dialog" 
        class="rounded-lg"
        min-height="600px"
        :loading="importing"
    >
        <!-- Title bar -->
        <v-card-title>
            <span class="text-h5">Dictionary import</span>
            <v-spacer></v-spacer>
            <v-btn icon @click="close" :disabled="importing"> 
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-card-title>

        <!-- Card text -->
        <v-card-text>

            <v-stepper id="import-stepper" v-model="stepperPage" elevation="0" class="pb-0" min-height="620px">
                <!-- Stepper header -->
                <v-stepper-header>
                    <v-stepper-step :complete="stepperPage > 1" step="1" :color="stepperPage > 1 ? 'success' : 'primary'">
                        Upload dictionary file
                    </v-stepper-step>
                    <v-divider/>
                    
                    <v-stepper-step :complete="stepperPage > 2" step="2" :color="stepperPage > 2 ? 'success' : 'primary'">
                        Overview
                    </v-stepper-step>
                    <v-divider/>

                    <v-stepper-step :complete="stepperPage > 3" step="3" :color="stepperPage > 3 ? 'success' : 'primary'">
                        Importing
                    </v-stepper-step>
                </v-stepper-header>

                <v-stepper-items>
                    <!-- Upload file -->
                    <v-stepper-content step="1">
                        <v-file-input
                            v-model="dictionaryFile"
                            filled
                            dense
                            rounded
                            clearable
                            placeholder="Dictionary file"
                            prepend-icon="mdi-file"
                            @change="testDictionaryFile"
                        ></v-file-input>

                        <v-alert
                            v-if="dictionaryFileTest === 'error'"
                            class="mt-2"
                            type="error"
                            color="error"
                            border="left"
                        >
                            The selected file is not a supported dictionary file. Please upload a file from the sources listed in the user manual.
                        </v-alert>
                    </v-stepper-content>

                    <!-- Import overview information -->
                    <v-stepper-content step="2">
                        Importing a large dictionary can take several minutes. 
                        <v-simple-table class="border no-hover mx-auto mt-2" v-if="dictionary !== null">
                            <tbody>
                                <tr>
                                    <td>Dictionary name:</td>
                                    <td>{{ dictionary.name }}</td>
                                </tr>
                                <tr>
                                    <td>Database table name:</td>
                                    <td>{{ dictionary.databaseName }}</td>
                                </tr>
                                <tr>
                                    <td>Records:</td>
                                    <td>{{ formatNumber(dictionary.expectedRecordCount) }}</td>
                                </tr>
                                <tr>
                                    <td>Source language:</td>
                                    <td>
                                        <v-img 
                                            class="border" 
                                            :src="'/images/flags/' + dictionary.source_language + '.png'" 
                                            max-width="43" 
                                            height="28"
                                        ></v-img> 
                                    </td>
                                </tr>
                                <tr>
                                    <td>Target language:</td>
                                    <td>
                                        <v-img 
                                            class="border" 
                                            :src="'/images/flags/' + dictionary.target_language + '.png'" 
                                            max-width="43" 
                                            height="28"
                                        ></v-img> 
                                    </td>
                                </tr>
                                <tr>
                                    <td>Color:</td>
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
                                    <td>Filename:</td>
                                    <td>
                                        {{ dictionary.fileName }}
                                    </td>
                                </tr>
                            </tbody>
                        </v-simple-table>
                    </v-stepper-content>

                    <!-- Import step -->
                    <v-stepper-content step="3">
                        <!-- Import progress -->
                        <template v-if="importing">
                            The dictionary is being imported. This might take a while.
                            <v-progress-linear
                                color="primary"
                                height="36"
                                :value="this.importedRecords / this.dictionary.expectedRecordCount * 100"
                                class="rounded-pill mt-2"
                            >
                                <strong>{{ importingProgressPercentage }}%</strong>
                            </v-progress-linear>
                        </template>

                        <!-- Import result -->
                        <template v-if="!importing && importResult !== null">
                            <!-- Success message -->
                            <v-alert
                                v-if="importResult == 'success'"
                                class="rounded-lg mt-2"
                                color="success"
                                type="success"
                                border="left"
                                dark
                            >
                                The import process has finished successfully. {{ dictionary.name }} has been imported.
                            </v-alert>

                            <!-- error message -->
                            <v-alert
                                v-if="importResult !== 'success'"
                                class="rounded-lg mt-2"
                                color="error"
                                type="error"
                                border="left"
                                dark
                            >
                                    The import process has failed. Please make sure your dictionary files are the correct ones. If the problem 
                                    still persist, please create a <a href="https://github.com/simjanos-dev/LinguaCafe">GitHub</a> Issue.
                            </v-alert>
                        </template>
                    </v-stepper-content>
                </v-stepper-items>
            </v-stepper>
        </v-card-text>
        <v-card-actions>
            <v-spacer />

            <!-- Buttons before import -->
            <template v-if="!importing && importResult === null">
                <v-btn 
                    rounded 
                    text 
                    @click="back"
                >Back</v-btn>
                <v-btn
                    v-if="stepperPage == 2 && importResult === null && dictionaryFile !== null  && dictionaryFile !== undefined"
                    rounded
                    depressed
                    color="primary"
                    @click="startImport"
                >Import</v-btn>
            </template>

            <!-- Buttons after import complete -->
            <template v-if="importResult !== null">
                <v-btn 
                    rounded 
                    text 
                    @click="close"
                >Close</v-btn>
            </template>
        </v-card-actions>
    </v-card>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                dictionary: null,
                dictionaryFile: null,
                dictionaryFileTest: '',
                stepperPage: 1,
                importing: false,
                importResult: null,
                importedRecords: 0,
                importingProgressPercentage: 0,
            };
        },
        mounted: function() {
        },
        methods: {
            testDictionaryFile() {
                if (this.dictionaryFile === null) {
                    this.dictionary = null;
                    this.dictionaryFileTest = '';
                    return;
                }
                
                let formData = new FormData();
                formData.append("dictionaryFile", this.dictionaryFile);

                this.dictionaryFileTest = 'loading';
                axios.post('/dictionaries/get-supported-dictionary-file-information', formData).then((response) => {
                    if (response.data === null) {
                        this.dictionary = null;
                        this.dictionaryFileTest = 'error';
                    } else {
                        this.dictionary = response.data;
                        this.stepperPage = 2;
                        this.dictionaryFileTest = '';
                    }
                }).catch((error) => {
                    this.dictionaryFileTest = 'error';
                });
            },
            // requests how many records have been imported
            updateImportProgress() {
                if (this.dictionaryFile === null) {
                    return;
                }

                axios.get('/dictionaries/get-record-count/' + this.dictionary.databaseName).then((response) => {
                    // update imported recod count
                    this.importedRecords = parseInt(response.data);

                    // update percentage
                    this.importingProgressPercentage = parseInt(this.importedRecords / this.dictionary.expectedRecordCount * 100);
                    if (this.importingProgressPercentage > 100) {
                        this.importingProgressPercentage = 100;
                    }
                    
                    // if the import hasn't finished, call this function again
                    if (this.importing) {
                        setTimeout(() => {
                            this.updateImportProgress();
                        }, this.dictionary.updateInterval);
                    }
                });
            },
            startImport() {
                if (this.dictionary === null) {
                    return;
                }

                this.importing = true;
                this.importedRecords = 0;
                this.stepperPage = 3;
                
                // first improt progress request
                setTimeout(() => {
                    this.updateImportProgress();
                }, this.dictionary.firstUpdateInterval);

                axios.post('/dictionaries/import', {
                    'dictionarySourceLanguage': this.dictionary.source_language,
                    'dictionaryTargetLanguage': this.dictionary.target_language,
                    'dictionaryName': this.dictionary.name,
                    'dictionaryDatabaseName': this.dictionary.databaseName,
                    'dictionaryExpectedRecordCount': this.dictionary.expectedRecordCount,
                    'dictionaryFileName': this.dictionary.fileName
                }).then((response) => {
                    this.importing = false;
                    this.importResult = response.data;
                });
            },
            back() {
                if (this.stepperPage === 1) {
                    this.$emit('back-to-dictionaries');
                } else {
                    this.stepperPage --;

                    if (this.stepperPage === 1) {
                        this.dictionary = null;
                        this.dictionaryFile = null;
                    }
                }
            },
            close() {
                this.$emit('import-finished');
                this.$emit('close');
            },
            formatNumber: formatNumber
        }
    }
</script>
