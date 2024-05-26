<template>
    <div id="admin-font-type-settings">
        <!-- Edit font type dialog -->
        <admin-edit-font-type-dialog 
            v-if="editFontTypeDialog.active"
            v-model="editFontTypeDialog.active"
            :id="editFontTypeDialog.id"
            :supported-languages="supportedlanguages"
            :default="editFontTypeDialog.default"
            :_languages="editFontTypeDialog.languages"
            :_name="editFontTypeDialog.name"
            @fonts-changed="loadFonts"
        />

        <!-- Delete font type dialog -->
        <admin-delete-font-type-dialog 
            v-if="deleteFontTypeDialog.active"
            v-model="deleteFontTypeDialog.active"
            :id="deleteFontTypeDialog.id"
            @fonts-changed="loadFonts"
        />

        <!-- Title subheader -->
        <div class="d-flex subheader mt-4 mb-4 px-2 ">
            Fonts

            <v-spacer />
            <v-btn rounded depressed color="primary" :disabled="!supportedlanguages.length" @click="uploadFont">
                <v-icon class="mr-1">mdi-upload</v-icon>
                Upload font
            </v-btn>
        </div>

        <!-- Font list -->
        <v-data-table
            class="ma-4 mb-0 no-hover border"
            :headers="[
                {
                    text: 'Font',
                    value: 'name',
                },
                {
                    text: 'Actions',
                    value: 'actions',
                }
            ]"
            :items="fonts"
            :loading="loading"
        >

            <!-- Actions -->
            <template v-slot:item.actions="{ item }">
                <!-- Edit button -->
                <v-btn 
                    rounded 
                    depressed 
                    icon
                    @click="editFont(item.index)"
                >
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>

                <!-- Delete button -->
                <v-btn 
                    v-if="!item.default"
                    rounded 
                    depressed 
                    icon
                    color="error"
                    @click="deleteFont(item.index)"
                >
                    <v-icon>mdi-delete</v-icon>
                </v-btn>
            </template>
        </v-data-table>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                editFontTypeDialog: {
                    active: false,
                    id: -1,
                    name: '',
                    languages: [],
                    default: false,
                },
                deleteFontTypeDialog: {
                    active: false,
                    id: -1,
                },
                supportedlanguages: [],
                loading: false,
                fonts: [],
            }
        },
        props: {
        },
        mounted() {
            this.loadFonts();
        },
        methods: {
            editFont(index) {
                this.editFontTypeDialog.active = true;
                this.editFontTypeDialog.id = this.fonts[index].id;
                this.editFontTypeDialog.name = this.fonts[index].name;
                this.editFontTypeDialog.languages = JSON.parse(this.fonts[index].languages);
                this.editFontTypeDialog.default = this.fonts[index].default === 1;
            },
            uploadFont(index) {
                this.editFontTypeDialog.active = true;
                this.editFontTypeDialog.id = -1;
                this.editFontTypeDialog.name = '';
                this.editFontTypeDialog.languages = [];
                this.editFontTypeDialog.default = false;
            },
            deleteFont(index) {
                this.deleteFontTypeDialog.active = true;
                this.deleteFontTypeDialog.id = this.fonts[index].id;
            },
            loadFonts() {
                this.loading = true;
                this.fonts = [];
                axios.get('/fonts/get').then((response) => {
                    this.fonts = response.data;

                    this.fonts.forEach((value, index) => {
                        value.index = index;
                    });
                    
                    console.log('fonts', this.fonts);
                    this.loading = false;
                });

                axios.get('/config/get/linguacafe.languages.supported_languages').then((response) => {
                    this.supportedlanguages  = response.data;
                });
            }
        }
    }
</script>
