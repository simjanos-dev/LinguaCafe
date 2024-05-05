<template>
    <v-dialog v-model="value" persistent max-width="800px" height="300px" scrollable>
        <v-card class="rounded-lg" :loading="saving">
            <!-- Card title -->
            <v-card-title>
                <!-- Upload font title -->
                <template v-if="$props.id === -1">
                    <v-icon class="mr-2">mdi-upload</v-icon>Upload font
                </template>

                <!-- Edit font title -->
                <template v-else>
                    <v-icon class="mr-2">mdi-pencil</v-icon>Edit font
                </template>

                <!-- Close button -->
                <v-spacer />
                    <v-btn icon @click="close">
                        <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <!-- Card content-->
            <v-card-text>
                <v-form ref="fontForm" v-if="saveResult !== 'success'">
                    <!-- Font file -->
                    <label class="font-weight-bold" v-if="$props.id === -1">Font file</label><br>
                    <v-file-input
                        v-if="$props.id === -1"
                        v-model="fontFile"
                        filled
                        dense
                        rounded
                        clearable
                        accept=".otf,.ttf,.woff,.woff2"
                        placeholder="Font file"
                        prepend-icon="mdi-file"
                        :disabled="saving"
                        :rules="[rules.fontFileRule]"
                        @change="validateForm"
                    ></v-file-input>

                    <!-- Font name -->
                    <label class="font-weight-bold">Name</label>
                    <v-text-field 
                        v-model="name"
                        filled
                        dense
                        rounded
                        placeholder="Font name"
                        :disabled="$props.default || saving"
                        :rules="[rules.fontName]"
                        @keyup="validateForm"
                    ></v-text-field>

                    <!-- Font languages -->
                    <label class="font-weight-bold d-flex">Languages</label>
                    <div class="d-flex flex-wrap">
                        <v-checkbox 
                            v-for="(language, index) in languages"
                            v-model="languages[index].enabled"
                            :key="index"
                            style="width: 50%;"
                            hide-details
                            dense
                            :label="language.name"
                            :disabled="$props.default || saving"
                        ></v-checkbox>
                    </div>
                </v-form>

                <!-- Success message -->
                <v-alert
                    v-if="!saving && saveResult == 'success'"
                    class="rounded-lg mb-2 w-100"
                    color="success"
                    type="success"
                    border="left"
                    dark
                >
                    Font type has been {{ $props.id === -1 ? 'uploaded' : 'saved'  }} successfully.
                </v-alert>
            </v-card-text>

            <!-- Card actions -->
            <v-card-actions class="flex-wrap">
                <!-- Error message -->
                <v-alert
                    v-if="!saving && saveResult == 'error'"
                    class="rounded-lg mb-2 w-100"
                    color="error"
                    type="error"
                    border="left"
                    dark
                >
                    An error has occured while {{ $props.id === -1 ? 'uploading' : 'saving'  }} the font type.
                </v-alert>

                <!-- Select all checkbox -->
                <v-checkbox 
                    v-if="saveResult !== 'success'"
                    class="font-weight-normal"
                    :label="$vuetify.breakpoint.smAndUp ? 'Select all' : 'All'"
                    hide-details
                    dense
                    @change="selectAllChanged"
                    :disabled="$props.default || saving"
                ></v-checkbox>

                <v-spacer></v-spacer>

                <!-- Cancel button -->
                <v-btn 
                    v-if="saveResult !== 'success'"
                    rounded 
                    text 
                    :disabled="saving"
                    @click="close" 
                >
                    Cancel
                </v-btn>
                
                <!-- Close button -->
                <v-btn rounded text @click="close" v-if="saveResult === 'success'">Close</v-btn>

                <!-- Upload button -->
                <v-btn 
                    v-if="$props.id === -1 && saveResult !== 'success'"
                    rounded 
                    depressed
                    color="primary" 
                    :loading="saving"
                    :disabled="$props.default || !isFormValid || saving"
                    @click="uploadFont"
                >
                    <v-icon class="mr-1">mdi-upload</v-icon>
                    Upload
                </v-btn>

                <!-- Save button -->
                <v-btn 
                    v-if="$props.id !== -1 && saveResult !== 'success'"
                    rounded 
                    depressed
                    color="primary" 
                    :loading="saving"
                    :disabled="$props.default || !isFormValid || saving"
                    @click="updateFont"
                >
                    Save
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
            id: Number,
            supportedLanguages: Array,
            default: Boolean,
            _name: String,
            _languages: Array,
        },
        emits: ['input'],
        data: function() {
            return {
                saving: false,
                saveResult: '',
                isFormValid: false,
                name: this.$props._name,
                selectAll: true,
                languages: [],
                fontFile: null,
                rules: {
                    fontFileRule: (value) => {
                        if (value === null || value === undefined) {
                            return 'You must select a file.';
                        }

                        if (value.name.length > 128) {
                            return 'File name must be below 128 characters.';
                        }
                        
                        let extension = value.name.split('.');
                        extension = extension[extension.length - 1];
                        if (!["otf", "ttf", "woff", "woff2"].includes(extension)) {
                            return 'The selected file must a font file(.otf, .ttf, .woff, .woff2).';
                        }

                        return true;
                    },
                    fontName: value => {
                        if (value.length < 2 || value.length > 128) {
                            return 'Font name must be between 2 and 128 characters.';
                        }
                        
                        return true;
                    },
                }
            };
        },
        mounted: function() {
            this.initLanguages();

            // validate form on open if it's an edit dialog and not upload
            if (this.$props.id !== -1) {
                this.validateForm();
            }
        },
        methods: {
            validateForm() {
                this.isFormValid = this.$refs.fontForm.validate();
            },
            selectAllChanged(value) {
                this.languages.forEach((language) => {
                    language.enabled = value;
                });

                this.validateForm();
            },
            initLanguages() {
                this.$props.supportedLanguages.forEach((value, index) => {
                    var enabled = this.$props._languages.includes(value);
                    this.languages.push({
                        name: value,
                        enabled: enabled,
                    });

                    if (!enabled) {
                        this.selectAll = false;
                    }
                });

                this.$forceUpdate();
            },
            uploadFont() {
                if (this.fontFile === null || this.fontFile === undefined) {
                    return;
                }

                // collect selected languages
                let formDataLanguages = [];
                this.languages.forEach((value) => {
                    if (value.enabled) {
                        formDataLanguages.push(value.name);
                    }
                });

                // create form data object
                let formData = new FormData();
                formData.append("name", this.name);
                formData.append("languages", JSON.stringify(formDataLanguages));
                formData.append("fontFile", this.fontFile);

                this.saving = true;
                axios.post('/fonts/upload', formData).then((response) => {
                    this.saving = false;
                    if (response.status === 200) {
                        this.saveResult = 'success';
                        this.$emit('fonts-changed');
                    }
                }).catch((error) => {
                    this.saving = false;
                    this.saveResult = 'error';
                });
            },
            updateFont() {
                // collect selected languages
                let formDataLanguages = [];
                this.languages.forEach((value) => {
                    if (value.enabled) {
                        formDataLanguages.push(value.name);
                    }
                });

                // create form data object
                let formData = new FormData();
                formData.append("id", this.$props.id);
                formData.append("name", this.name);
                formData.append("languages", JSON.stringify(formDataLanguages));

                this.saving = true;
                axios.post('/fonts/update', formData).then((response) => {
                    this.saving = false;
                    if (response.status === 200) {
                        this.saveResult = 'success';
                        this.$emit('fonts-changed');
                    }
                }).catch((error) => {
                    this.saving = false;
                    this.saveResult = 'error';
                });
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>

