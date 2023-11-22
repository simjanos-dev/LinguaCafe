<template>
    <div>
        <v-form ref="importFileForm" v-model="isFormValid">
            <v-alert dark border="left" type="info" color="primary" class="mb-8">
                Please make sure that your .epub file contains no DRM (digital rights management) protection. 
                Unfortunately we have no means to read DRM protected files.
            </v-alert>
            <label class="font-weight-bold">E-book file</label>
            <v-file-input
                v-model="ebookFile"
                filled
                dense
                rounded
                persistent-hint
                hint="Accepted format: .epub"
                ref="ebookFile"
                accept=".epub"
                placeholder="E-book file"
                prepend-icon="mdi-book"
                :rules="[rules.ebookFileRule]"
                @change="selectImportFile"
            ></v-file-input>
        </v-form>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                ebookFile: null,
                isFormValid: false,
                rules: {
                    ebookFileRule: (value) => {
                        if (value === null || value === undefined) {
                            return 'You must select a file.';
                        }
                        
                        let extension = value.name.split('.');
                        extension = extension[extension.length - 1];
                        if (extension !== 'epub') {
                            return 'The selected file must a .epub file.';
                        }

                        return true;
                    }
                }
            }
        },
        props: {
        },
        mounted() {
        },
        methods: {
            selectImportFile() {
                this.$emit('file-selected', {
                    importFile: this.ebookFile,
                    isImportFileValid: this.$refs.importFileForm.validate()
                });
            }
        }
    }
</script>
