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
            <v-btn icon @click="close"> 
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-card-title>

        <!-- Card text -->
        <v-card-text>
            <!-- Import overview information -->
            <template v-if="!importing && importResult === null">
                Importing a large dictionary can take several minutes. 
                <v-simple-table class="border no-hover mx-auto mt-2">
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
                            <td>Language:</td>
                            <td>
                                <v-img 
                                    class="border" 
                                    :src="'/images/flags/' + dictionary.language" 
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
            </template>

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
        </v-card-text>
        <v-card-actions>
            <v-spacer />

            <!-- Buttons before import -->
            <template v-if="!importing && importResult === null">
                <v-btn 
                    rounded 
                    text 
                    @click="backToDictionaries"
                >Back</v-btn>
                <v-btn
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
    const config =  require('./../../config');
    import {formatNumber} from './../../helper.js';
    export default {
        props: {
            dictionary: Object
        },
        data: function() {
            return {
                importing: false,
                importResult: null,
                importedRecords: 0,
                importingProgressPercentage: 0,
            };
        },
        mounted: function() {
        },
        methods: {
            // requests how many records have been imported
            updateImportProgress() {
                axios.get('/dictionaries/get-record-count/' + this.$props.dictionary.databaseName).then((response) => {
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
                this.importing = true;
                this.importedRecords = 0;
                
                // first improt progress request
                setTimeout(() => {
                    this.updateImportProgress();
                }, this.dictionary.firstUpdateInterval);

                axios.post('/dictionaries/import', {
                    'dictionaryLanguage': this.$props.dictionary.language,
                    'dictionaryName': this.$props.dictionary.name,
                    'dictionaryDatabaseName': this.$props.dictionary.databaseName,
                    'dictionaryExpectedRecordCount': this.$props.dictionary.expectedRecordCount,
                    'dictionaryFileName': this.$props.dictionary.fileName
                }).then((response) => {
                    this.importing = false;
                    this.importResult = response.data;
                });
            },
            backToDictionaries() {
                this.$emit('back-to-dictionaries');
            },
            close() {
                this.$emit('import-finished');
                this.$emit('close');
            },
            formatNumber: formatNumber
        }
    }
</script>
