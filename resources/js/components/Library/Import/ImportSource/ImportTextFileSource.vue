<template>
    <div class="d-flex flex-column align-stretch">
        <!-- Text file -->
        <label class="font-weight-bold">Text file</label>
        <v-file-input
            v-model="textFile"
            filled
            dense
            rounded
            persistent-hint
            hint="Accepted format: .txt"
            ref="textFile"
            accept=".txt"
            placeholder="Text file"
            prepend-icon="mdi-book"
            :rules="[rules.textFileRule]"
            @change="textFileSelected"
        ></v-file-input>

    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                text: '',
                textFile: null,
                isFormValid: false,
                rules: {
                    textFileRule: (value) => {
                        if (value === null || value === undefined) {
                            return 'You must select a file.';
                        }
                        
                        let extension = value.name.split('.');
                        extension = extension[extension.length - 1];
                        if (extension !== 'txt') {
                            return 'The selected file must a .txt file.';
                        }

                        return true;
                    }
                }
            }
        },
        props: {
            language: String,
        },
        mounted() {
        },
        methods: {
            textFileSelected() {
                // validate
                this.text = '';
                if (!this.$refs.textFile.validate()) {
                    // disable continue button in import dialog
                    this.$emit('text-selected', {
                        text: '',
                        isImportSourceValid: false
                    });

                    return;
                }

                // read file
                var reader = new FileReader();
                reader.readAsText(this.textFile);
                reader.onload = () => {
                    this.text = reader.result;

                    this.$emit('text-selected', {
                        text: this.text,
                        isImportSourceValid: true
                    });
                };
            }
        }
    }
</script>
