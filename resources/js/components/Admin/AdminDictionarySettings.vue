<template>
    <div id="admin-dictionary-settings" class="pb-16">
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
        
        <!-- Dictionaries list -->
        <div class="d-flex subheader mt-4 mb-4 px-2 ">
            Dictionaries
            <v-spacer/>
            <v-btn rounded dark color="primary" @click="importDialog = true;">
                <v-icon class="mr-1">mdi-database-plus</v-icon>
                <span id="import-button-text">Add dictionary</span>
                <span id="import-button-text-short">Import</span>
            </v-btn>
        </div>
        <v-card outlined class="rounded-lg pa-2 pb-0 mb-32">
            <v-card-title>
                <v-text-field
                    v-model="dictionaryTableFilter"
                    append-icon="mdi-magnify"
                    label="Search"
                    filled
                    dense
                    hide-details
                    single-line
                    rounded
                ></v-text-field>
            </v-card-title>

            <v-data-table
                class="ma-4 mb-0 no-hover"
                :headers="dictionaryTableHeaders"
                :items="dictionaries"
                :loading="loading"
                :search="dictionaryTableFilter"
            >

                <!-- Records -->
                <template v-slot:item.records="{ item }">
                    {{ item.records == '-' ? '-' : formatNumber(item.records) }}
                </template>

                <!-- Source language -->
                <template v-slot:item.source_language="{ item }">
                    <v-img class="mx-auto border" :src="'/images/flags/' + item.source_language + '.png'" max-width="43" height="28" /> 
                </template>

                <!-- Target language -->
                <template v-slot:item.target_language="{ item }">
                    <v-img class="mx-auto border" :src="'/images/flags/' + item.target_language + '.png'" max-width="43" height="28" /> 
                </template>

                <!-- Enabled -->
                <template v-slot:item.enabled="{ item }">
                    <div class="d-flex justify-center">
                        <v-switch
                            color="primary"
                            v-model="dictionaries[item.index].enabled" 
                            @change="saveDictionary(item.index)"
                        ></v-switch>
                    </div>
                </template>

                <!-- Actions -->
                <template v-slot:item.actions="{ item }">
                    <v-btn 
                        icon 
                        title="Edit"
                        @click="editDictionary(item.id)"
                    >
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn 
                        v-if="item.name !== 'JMDict'"
                        icon 
                        title="Delete"
                        color="error"
                        @click="deleteDictionary(item.id, item.name, item.database_table_name)"
                    >
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>
    </div>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                loading: true,
                dictionaries: [],
                importDialog: false,
                deleteDialog: {
                    active: false,
                    databaseTableName: '',
                    dictionaryName: '',
                    id: -1,
                },
                editDialog: {
                    active: false,
                    id: -1,
                },
                errorDialog: {
                    active: false
                },
                dictionaryTableFilter: '',
                dictionaryTableHeaders: [
                    {
                        text: 'Name',
                        value: 'name',
                        align: 'center',
                    },
                    {
                        text: 'Records',
                        value: 'records',
                        align: 'center',
                    },
                    {
                        text: 'Database',
                        value: 'database_table_name',
                        align: 'center',
                    },
                    {
                        text: 'Source',
                        value: 'source_language',
                        align: 'center',
                    },
                    {
                        text: 'Target',
                        value: 'target_language',
                        align: 'center',
                    },
                    {
                        text: 'Enabled',
                        value: 'enabled',
                        align: 'center',
                    },
                    {
                        text: 'Actions',
                        value: 'actions',
                        align: 'center',
                        width: '110px',
                        sortable: false,
                    },
                ]
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
            deleteDictionary(dictionaryId, dictionaryName, databaseTableName) {
                this.deleteDialog.active = true;
                this.deleteDialog.id = dictionaryId;
                this.deleteDialog.databaseTableName = databaseTableName;
                this.deleteDialog.dictionaryName = dictionaryName;
            },
            deleteDictionaryConfirm(databaseTableName) {
                axios.get('/dictionary/delete/' + this.deleteDialog.id).then((response) => {
                    this.deleteDialog.active = false;
                    this.loadDictionaries();

                    if (response.data !== 'success') {
                        this.errorDialog.active = true;
                    }
                });
            },
            loadDictionaries() {
                this.loading = true;
                axios.get('/dictionaries/get').then((response) => {
                    this.loading = false;
                    let data = response.data;

                    for (let dictionaryIndex = 0; dictionaryIndex < data.length; dictionaryIndex ++) {
                        data[dictionaryIndex].tempColor = data[dictionaryIndex].color;
                        data[dictionaryIndex].colorPicker = false;
                        data[dictionaryIndex].colorPickerMobile = false;
                        data[dictionaryIndex].expanded = false;
                        data[dictionaryIndex].enabled = (data[dictionaryIndex].enabled === 1);
                        data[dictionaryIndex].index = dictionaryIndex;
                    }

                    this.dictionaries = data;
                    console.log(this.dictionaries);
                });
            },
            formatNumber: formatNumber
        }
    }
</script>
