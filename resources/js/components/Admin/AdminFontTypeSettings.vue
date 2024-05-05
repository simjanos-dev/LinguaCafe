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
        <v-simple-table class="no-hover border rounded-lg" v-if="fonts.length">
            <thead>
                <tr>
                    <th class="text-center" colspan="2">Font</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(font, fontIndex) in fonts" :key="fontIndex">
                    <!-- Font -->
                    <td>
                        {{ font.name }} {{ font.default ? '(default)' : '' }}
                    </td>

                    <!-- Actions -->
                    <td class="text-center">
                        <!-- Edit button -->
                        <v-btn 
                            rounded 
                            depressed 
                            icon
                            @click="editFont(fontIndex)"
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>

                        <!-- Delete button -->
                        <v-btn 
                            v-if="!font.default"
                            rounded 
                            depressed 
                            icon
                            color="error"
                            @click=";"
                        >
                            <v-icon>mdi-delete</v-icon>
                        </v-btn>
                    </td>
                </tr>
            </tbody>
        </v-simple-table>
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
            loadFonts() {
                this.loading = true;
                this.fonts = [];
                axios.get('/fonts/get').then((response) => {
                    this.fonts = response.data;
                    this.loading = false;
                });

                axios.get('/config/get/linguacafe.languages.supported_languages').then((response) => {
                    this.supportedlanguages  = response.data;
                });
            }
        }
    }
</script>
