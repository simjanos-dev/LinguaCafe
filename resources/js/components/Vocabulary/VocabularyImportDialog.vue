<template>
    <v-dialog v-model="value" persistent scrollable width="800px">
        <v-card 
            id="vocabulary-export-dialog" 
            class="rounded-lg"
            :loading="loading"
            min-height="400px"
        >

            <!-- Title bar -->
            <v-card-title>
                <span class="text-h5" v-if="importResult === null">Vocabulary import</span>
                <span class="text-h5" v-if="importResult !== null">Import results</span>
                
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <!-- Import card content-->
            <v-card-text>
                <template v-if="importResult === null || importResult.error">
                    <!-- Import information -->
                    <v-alert dark border="left" type="info" color="primary" v-if="!loading">
                        Please read the user manual before importing.
                    </v-alert>

                    <!-- Csv file -->
                    <label class="font-weight-bold">Subtitle file</label>
                    <v-file-input
                        v-model="importFile"
                        filled
                        dense
                        rounded
                        persistent-hint
                        hint="Accepted format: .csv"
                        ref="importFile"
                        accept=".csv"
                        placeholder="Import file"
                        prepend-icon="mdi-file-delimited"
                        class="mb-4"
                        :disabled="loading"
                        :rules="[rules.importFileRule]"
                    ></v-file-input>

                    <!-- Delimiter -->
                    <label class="font-weight-bold">Delimiter</label>
                    <v-text-field 
                        v-model="delimiter"
                        filled
                        dense
                        rounded
                        hide-details
                        max-length="1"
                    ></v-text-field>

                    <!-- Skip first row -->
                    <v-switch
                        v-model="skipHeader"
                        hide-details
                        class="my-1 mt-6"
                        color="primary"
                        label="Skip first row"
                        :disabled="loading"
                    ></v-switch>

                    <!-- Only update -->
                    <v-switch
                        v-model="onlyUpdate"
                        hide-details
                        class="my-2"
                        color="primary"
                        label="Only update"
                        :disabled="loading"
                    ></v-switch>

                    <!-- Import information -->
                    <v-alert dark class="mt-4" border="left" type="error" color="error" v-if="!loading && importResult !== null && importResult.error">
                        An error has occured while importing. Please make sure that your file is in the correct format.
                    </v-alert>
                </template>

                <!-- Importing message -->
                <v-alert class="mt-4" dark border="left" type="info" color="primary" v-if="loading">
                    Importing your selected file. This might take take a while...
                </v-alert>

                <!-- Import success -->
                <template v-if="importResult !== null && !importResult.error">              
                    <v-simple-table class="no-hover border rounded-lg mt-4">
                        <tbody>
                            <tr v-if="importResult.createdWords">
                                <th>Created words:</th>
                                <th>{{ importResult.createdWords }}</th>
                            </tr>
                            <tr v-if="importResult.updatedWords">
                                <th>Updated words:</th>
                                <th>{{ importResult.updatedWords }}</th>
                            </tr>
                            <tr v-if="importResult.rejectedWords">
                                <th>Rejected words:</th>
                                <th>{{ importResult.rejectedWords }}</th>
                            </tr>
                        </tbody>
                    </v-simple-table>
                </template>
            </v-card-text>

            <!-- Action buttons -->
            <v-card-actions>
                <v-spacer></v-spacer>

                <!-- Import and cancel buttons -->
                <template v-if="importResult === null || importResult.error">
                    <v-btn rounded text @click="close">Cancel</v-btn>
                    <v-btn 
                        rounded 
                        depressed
                        color="primary"
                        :disabled="!importFileValid"
                        @click="importFromCsv"
                    >Import</v-btn>
                </template>

                <!-- Close button -->
                <template v-if="importResult !== null && !importResult.error">
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
        },
        emits: ['input'],
        data: function() {
            return {
                importFile: null,
                importFileValid: false,
                importResult: null,
                loading: false,
                skipHeader: false,
                onlyUpdate: true,
                delimiter: '|',
                rules: {
                    importFileRule: (value) => {
                        if (value === null || value === undefined) {
                            this.importFileValid = false;
                            return 'You must select a file.';
                        }
                        
                        let extension = value.name.split('.');
                        extension = extension[extension.length - 1];
                        
                        if (extension !== 'csv') {
                            this.importFileValid = false;
                            return 'The selected file must a .csv file.';
                        }

                        this.importFileValid = true;
                        return true;
                    }
                }
            };
        },
        mounted: function() {
        },
        methods: {
            importFromCsv() {
                // validate                
                if (!this.$refs.importFile.validate()) {
                    return;
                }

                // create form data
                var formData = new FormData();
                formData.set('importFile', this.importFile);
                formData.append("skipHeader", this.skipHeader);
                formData.append("onlyUpdate", this.onlyUpdate);
                formData.append("delimiter", this.delimiter);

                this.loading = true;
                this.importResult = null;
                axios.post('/vocabulary/import-from-csv', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then((response) => {
                    this.loading = false;
                    this.importFile = null;
                    this.importResult = {
                        createdWords: response.data.createdWords,
                        updatedWords: response.data.updatedWords,
                        rejectedWords: response.data.rejectedWords,
                        error: false
                    };
                    
                }).catch((error) => {
                    this.loading = false;
                    this.importFile = null;
                    this.importResult = {
                        createdWords: 0,
                        updatedWords: 0,
                        rejectedWords: 0,
                        error: true
                    };
                });
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
