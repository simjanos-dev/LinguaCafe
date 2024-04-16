<template>
    <div id="admin-dictionary-settings">
        <!-- Dialogs -->
        <admin-dictionary-import-dialog 
            v-if="importDialog"
            v-model="importDialog" 
            :language="$props.language" 
            @import-finished="loadDictionaries"
        />

        <admin-delete-dictionary-dialog
            v-if="deleteDialog.active"
            v-model="deleteDialog.active" 
            :dictionary-name="deleteDialog.dictionaryName" 
            :database-table-name="deleteDialog.databaseTableName" 
            @confirm="deleteDictionaryConfirm" 
        />

        <admin-edit-dictionary-dialog
            v-if="editDialog.active"
            v-model="editDialog.active" 
            :dictionary-id="editDialog.id" 
            @dictionary-saved="loadDictionaries"
        />

        <error-dialog
            v-if="errorDialog.active"
            v-model="errorDialog.active" 
            content="An error has occurred while deleting the dictionary."
        />
        
        <!-- Dictionaries -->
        <div class="d-flex subheader mt-4 mb-4 px-2 ">
            Dictionaries
            <v-spacer/>
            <v-btn rounded dark color="primary" @click="importDialog = true;">
                <v-icon class="mr-1">mdi-database-import</v-icon>
                <span id="import-button-text">Import dictionary</span>
                <span id="import-button-text-short">Import</span>
            </v-btn>
        </div>
        <v-simple-table id="dictionaries" class="no-hover border rounded-lg">
            <thead>
                <tr>
                    <th class="text-center">Name</th>
                    <th class="text-center">Records</th>
                    <th class="text-center">Database name</th>
                    <th class="text-center">Source language</th>
                    <th class="text-center">Target language</th>
                    <th class="text-center">Enabled</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(dictionary, index) in dictionaries" :key="index">
                    <!-- Name -->
                    <td class="text-center">{{ dictionary.name }}</td>
                    
                    <!-- Records -->
                    <td class="text-center">{{ dictionary.records == '-' ? '-' : formatNumber(dictionary.records) }}</td>
                    
                    <!-- Database table name -->
                    <td class="text-center">{{ dictionary.database_table_name }}</td>

                    <!-- Source language -->
                    <td>
                        <v-img class="mx-auto border" :src="'/images/flags/' + dictionary.source_language + '.png'" max-width="43" height="28" /> 
                    </td>

                    <!-- Target language -->
                    <td>
                        <v-img class="mx-auto border" :src="'/images/flags/' + dictionary.target_language + '.png'" max-width="43" height="28" /> 
                    </td>

                    <!-- Enabled/disabled -->
                    <td>
                        <div class="d-flex justify-center">
                            <v-switch
                                color="primary"
                                v-model="dictionaries[index].enabled" 
                                @change="saveDictionary(index)"
                            ></v-switch>
                        </div>
                    </td>

                    <!-- Actions -->
                    <td class="text-center">
                        <v-btn 
                            icon 
                            title="Edit"
                            @click="editDictionary(dictionary.id)"
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                        <v-btn 
                            v-if="dictionary.imported === '1'"
                            icon 
                            title="Delete"
                            color="error"
                            @click="deleteDictionary(dictionary.name, dictionary.database_table_name)"
                        >
                            <v-icon>mdi-delete</v-icon>
                        </v-btn>
                    </td>
                </tr>
            </tbody>
        </v-simple-table>


        <!-- Dictionaries mobile -->
        <div id="dictionaries-mobile" class="div border rounded-lg">
            <v-card
                v-for="(dictionary, index) in dictionaries" :key="index"
                :class="{'expansion-card': true, 'open': dictionary.expanded}"
                elevation="0"
            >
                <v-card-title class="expansion-card-title py-3 px-0" @click="toggleExpansion(index)">
                    <v-img class="mx-4 border" :src="'/images/flags/' + dictionary.source_language + '.png'" max-width="43" height="28" />
                    {{ dictionary.name }}
                    <v-spacer />
                    <v-icon class="mr-4">{{ dictionary.expanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                </v-card-title>
                <v-card-text class="expansion-card-content">
                    <v-simple-table class="no-lines no-hover my-2">
                        <tbody>
                            <!-- Name -->
                            <tr>
                                <td>Name:</td>
                                <td class="text-center">{{ dictionary.name }}</td>
                            </tr>
                            
                            <!-- Color -->
                            <tr>
                                <td>Color:</td>
                                <td>
                                    <v-menu
                                        v-model="dictionary.colorPickerMobile"
                                        width="290px"
                                        offset-y
                                        nudge-top="-10px"
                                        right
                                        :close-on-content-click="false"
                                        class="bg-white"
                                    >

                                        <template v-slot:activator="{ on, attrs }">
                                            <v-card
                                                class="border mx-auto"
                                                outlined
                                                :color="dictionary.color"
                                                width="48px"
                                                height="26px"
                                                @click="dictionary.colorPickerMobile = true;"
                                            ></v-card>
                                        </template>

                                        <v-color-picker hide-inputs v-model="dictionary.tempColor" />
                                        <div class="bg-white w-full d-flex">
                                            <v-spacer />
                                            <v-btn
                                                rounded
                                                small
                                                text
                                                class="mt-2"
                                                @click="dictionary.colorPickerMobile = false;"
                                            >Cancel</v-btn>
                                            <v-btn
                                                rounded
                                                small
                                                class="ma-2"
                                                color="primary"
                                                @click="saveColor(index)"
                                            >Save</v-btn>
                                        </div>
                                    </v-menu>
                                </td>
                            </tr>
                            
                            <!-- Records -->
                            <tr>
                                <td>Records</td>
                                <td class="text-center">{{ dictionary.records == '-' ? '-' : formatNumber(dictionary.records) }}</td>
                            </tr>
                            
                            <!-- Database table name -->
                            <tr>
                                <td>Database table name</td>
                                <td class="text-center">{{ dictionary.database_table_name }}</td>
                            </tr>
                            
                            <!-- Source language -->
                            <tr>
                                <td>Source language</td>
                                <td><v-img class="mx-auto border" :src="'/images/flags/' + dictionary.source_language + '.png'" max-width="43" height="28" /> </td>
                            </tr>

                            <!-- Target language -->
                            <tr>
                                <td>Target language</td>
                                <td><v-img class="mx-auto border" :src="'/images/flags/' + dictionary.target_language + '.png'" max-width="43" height="28" /> </td>
                            </tr>

                            <!-- Enabled/disabled -->
                            <tr>
                                <td>Enabled</td>
                                <td>
                                    <div class="d-flex justify-center">
                                        <v-switch
                                            color="primary"
                                            v-model="dictionaries[index].enabled" 
                                            @change="saveDictionary(index)"
                                        ></v-switch>
                                    </div>
                                </td>
                            </tr>

                            <!-- Actions -->
                            <tr>
                                <td>Actions</td>
                                <td class="text-center">
                                    <v-menu offset-y class="rounded-lg">
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-btn rounded depressed v-bind="attrs" v-on="on">
                                                Actions
                                                <v-icon v-if="attrs['aria-expanded'] === 'true'">mdi-chevron-up</v-icon>
                                                <v-icon v-if="attrs['aria-expanded'] !== 'true'">mdi-chevron-down</v-icon>
                                            </v-btn>
                                        </template>
                                        <v-btn class="menu-button" tile color="white" @click="editDictionary(dictionary.id)">Edit</v-btn>
                                        <v-btn
                                            class="menu-button"
                                            tile
                                            color="white"
                                            @click="deleteDictionary(dictionary.name, dictionary.database_table_name)" 
                                            v-if="dictionary.imported === '1'"
                                        >
                                            Delete
                                        </v-btn>
                                    </v-menu>
                                </td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                </v-card-text>
            </v-card>
        </div>
    </div>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                dictionaries: [],
                importDialog: false,
                deleteDialog: {
                    active: false,
                    databaseTableName: '',
                    dictionaryName: ''
                },
                editDialog: {
                    active: false,
                    id: -1,
                },
                errorDialog: {
                    active: false
                }
            }
        },
        props: {
            language: String
        },
        mounted() {
            this.loadDictionaries();
        },
        methods: {
            toggleExpansion: function(dictionaryIndex) {
                if (this.dictionaries[dictionaryIndex].expanded) {
                    this.dictionaries[dictionaryIndex].expanded = false;
                } else {
                    for (let dictionaryLoopIndex = 0; dictionaryLoopIndex < this.dictionaries.length; dictionaryLoopIndex ++) {
                        this.dictionaries[dictionaryLoopIndex].expanded = (dictionaryIndex == dictionaryLoopIndex);
                    }
                }
            },
            saveColor(dictionaryIndex) {
                this.dictionaries[dictionaryIndex].color = this.dictionaries[dictionaryIndex].tempColor;
                this.dictionaries[dictionaryIndex].colorPicker = false;
                this.dictionaries[dictionaryIndex].colorPickerMobile = false;
                this.saveDictionary(dictionaryIndex);
            },
            saveDictionary(dictionaryIndex) {
                axios.post('/dictionary/update', {
                    id: this.dictionaries[dictionaryIndex].id,
                    color: this.dictionaries[dictionaryIndex].color,
                    enabled: this.dictionaries[dictionaryIndex].enabled,
                }).then((response) => {
                });

                this.dictionaries[dictionaryIndex].colorPicker = false;
                this.dictionaries[dictionaryIndex].colorPickerMobile = false;
            },
            editDictionary(id) {
                this.editDialog.active = true;
                this.editDialog.id = id;
            },
            deleteDictionary(dictionaryName, databaseTableName) {
                this.deleteDialog.active = true;
                this.deleteDialog.databaseTableName = databaseTableName;
                this.deleteDialog.dictionaryName = dictionaryName;
            },
            deleteDictionaryConfirm(databaseTableName) {
                axios.get('/dictionary/delete/' + this.deleteDialog.databaseTableName).then((response) => {
                    this.deleteDialog.active = false;
                    this.loadDictionaries();

                    if (response.data !== 'success') {
                        this.errorDialog.active = true;
                    }
                });
            },
            loadDictionaries() {
                axios.get('/dictionaries/get').then((response) => {
                    let data = response.data;

                    for (let dictionaryIndex = 0; dictionaryIndex < data.length; dictionaryIndex ++) {
                        data[dictionaryIndex].tempColor = data[dictionaryIndex].color;
                        data[dictionaryIndex].colorPicker = false;
                        data[dictionaryIndex].colorPickerMobile = false;
                        data[dictionaryIndex].expanded = false;
                    }

                    this.dictionaries = data;
                });
            },
            formatNumber: formatNumber
        }
    }
</script>
