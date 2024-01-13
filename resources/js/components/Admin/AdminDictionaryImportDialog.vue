<template>
    <v-dialog v-model="value" scrollable persistent width="1000px">
        <!-- External dictionary import -->
        <template v-if="importType === 'external'">
            <admin-external-dictionary-import 
                :language="language"
                @import-finished="importFinished" 
                @back-to-dictionaries="backToDictionaries"
                @close="close" 
            ></admin-external-dictionary-import>
        </template>

        <!-- Supported dictionary import -->
        <template v-if="importType === 'supported'">
            <admin-supported-dictionary-import 
                :dictionary="selectedDictionary"
                @import-finished="importFinished" 
                @back-to-dictionaries="backToDictionaries" 
                @close="close" 
            ></admin-supported-dictionary-import>
        </template>

        <!-- Dictionary selection list -->
        <v-card 
            v-if="importType === 'none'"
            id="supported-dictionary-import-dialog" 
            class="rounded-lg"
            :loading="dictionariesLoading"
            min-height="600px"
        >
            <!-- Title bar -->
            <v-card-title>
                <span class="text-h5">Dictionary import</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close"> 
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <!-- Dictionary list -->
            <v-card-text>
                <!-- Import info text -->
                <template v-if="dictionariesFound.length">
                    These dictionaries have been found in the LinguaCafe storage directory. 
                    You can select one to start the import process, or you can upload and 
                    import a dictionary from an external .csv file.
                </template>

                <!-- Scanned dictionary list -->
                <div id="supported-dictionaries" class="mt-4" v-if="!dictionariesLoading">
                    <!-- Upload external .csv import -->
                    <div class="dictionary d-flex flex-wrap justify-space-between rounded-xl pa-4">
                        <div class="language"></div>
                        <div class="name">
                            Upload a dictionary from a .csv dictionary file
                        </div>
                        <div class="import-button">
                            <v-btn 
                                rounded 
                                depressed 
                                color="primary"
                                width="120px"
                                @click="selectDictionary(-1)"
                            ><v-icon class="mr-1">mdi-file-upload</v-icon>Upload</v-btn>
                        </div>
                    </div>

                    <!-- Scanned dictionary files -->
                    <template v-if="dictionariesFound.length">
                        <div 
                            class="dictionary d-flex flex-wrap rounded-xl mt-4 pa-4"
                            v-for="(dictionary, dictionaryIndex) in dictionariesFound"
                            :key="dictionaryIndex"
                            >
                            
                            <div class="language d-flex align-center">
                                <v-img 
                                    class="border" 
                                    :src="'/images/flags/' + dictionary.language" 
                                    max-width="43" 
                                    height="28"
                                ></v-img> 
                            </div>

                            <div class="name">
                                {{ dictionary.name }}
                            </div>

                            
                            <div class="import-button">
                                <v-btn 
                                    rounded 
                                    depressed 
                                    color="primary"
                                    width="120px"
                                    @click="selectDictionary(dictionaryIndex)"
                                ><v-icon class="mr-1">mdi-database-import</v-icon>Import</v-btn>
                            </div>
                        </div>
                    </template>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from 'axios';

    const config =  require('./../../config');
    export default {
        props: {
            value : Boolean,
            language: String,
        },
        emits: ['input'],
        data: function() {
            return {
                dictionariesLoading: false,
                dictionariesFound: [],
                selectedDictionary: null,
                importType: 'none'
            };
        },
        mounted: function() {
            this.loadDictionaries();
        },
        methods: {
            loadDictionaries() {
                this.dictionariesLoading = true;
                axios.get('/dictionaries/scan').then((response) => {
                    this.dictionariesFound = response.data;
                    this.dictionariesLoading = false;
                });
            },
            selectDictionary(dictionaryIndex) {
                if (dictionaryIndex == -1) {
                    this.selectedDictionary = null;
                    this.importType = 'external';
                } else {
                    this.selectedDictionary = this.dictionariesFound[dictionaryIndex];
                    this.importType = 'supported';
                }
            },
            backToDictionaries() {
                this.importType = 'none';
                this.selectedDictionary = null;
            },
            close() {
                this.$emit('input', false);
            },
            importFinished() {
                this.$emit('import-finished');
            }
        }
    }
</script>
