<template>
    <div>
        <v-form ref="importOptionsForm" v-model="isFormValid">
            <!-- Text processing method label -->
            <label class="font-weight-bold mt-2">
                Text processing method 
                <v-menu offset-y nudge-top="-12px">
                    <template v-slot:activator="{ on, attrs }">
                        <v-icon class="ml-1" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                    </template>
                    <v-card outlined class="rounded-lg pa-4" width="320px">
                        Simple processing method splits text into sentences and words very quickly, however 
                        does not provide additional information for them like dictionary form, gender or reading.<br><br>
                        Simple processing method is not available for every language.
                    </v-card>
                </v-menu>
            </label>

            <!-- Text processing method -->
            <v-radio-group
                v-model="processingMethod"
                @change="importOptionsChanged"
                class="mt-0"
            >
                <v-radio
                    value="simple"
                    :disabled="!simpleProcessingMethodEnabled"
                >
                    <template v-slot:label>
                        <div>Simple</div>
                    </template>
                </v-radio>
                <v-radio
                    value="detailed"
                >
                    <template v-slot:label>
                        <div>Detailed</div>
                    </template>
                </v-radio>
            </v-radio-group>

            <!-- E-book chapter order -->
            <label class="font-weight-bold mt-2" v-if="$props.type === 'e-book'">
                E-book chapter order
                <v-menu offset-y nudge-top="-12px">
                    <template v-slot:activator="{ on, attrs }">
                        <v-icon class="ml-1" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                    </template>
                    <v-card outlined class="rounded-lg pa-4" width="320px">
                        For some rare cases the chapter order can be incorrect after importing an e-book. If it 
                        happened to you, please delete the imported book, and import it again with the Spine option.
                    </v-card>
                </v-menu>
            </label>

            <v-radio-group
                v-if="$props.type === 'e-book'"
                v-model="eBookChapterSortMethod"
                @change="importOptionsChanged"
                class="mt-0"
            >
                <v-radio
                    value="default"
                >
                    <template v-slot:label>
                        <div>Default</div>
                    </template>
                </v-radio>
                <v-radio
                    value="spine"
                >
                    <template v-slot:label>
                        <div>Spine</div>
                    </template>
                </v-radio>
            </v-radio-group>

            <!-- Text processing method label -->
            <label class="font-weight-bold mt-2">Maximum characters per chapter</label>
            <v-text-field 
                v-model="maximumCharactersPerChapter"
                ref="maximumCharactersPerChapterInput"
                filled
                dense
                rounded
                min="200"
                max="20000"
                type="Number"
                @keyup="importOptionsChanged"
                @click="importOptionsChanged"
                :rules="[rules.maximumCharactersPerChapter]"
            ></v-text-field>

            <v-alert dark border="left" color="warning" type="error" v-if="maximumCharactersPerChapter > defaultMaximumCharactersPerChapter">
                Using larger chapter sizes can lead to performance issues. The default settings are highly recommended!
            </v-alert>
        </v-form>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                simpleProcessingMethodEnabled: this.$props.language == 'norwegian' || this.$props.language == 'german',
                processingMethod: 'detailed',
                eBookChapterSortMethod: 'default',
                isFormValid: false,
                maximumCharactersPerChapter: (this.$props.language == 'chinese' || this.$props.language == 'japanese') ? 1500 : 3000,
                defaultMaximumCharactersPerChapter: (this.$props.language == 'chinese' || this.$props.language == 'japanese') ? 1500 : 3000,

                rules: {
                    maximumCharactersPerChapter: value => {
                        if (value < 300) {
                            return 'It has to be at least 300 characters.';
                        }

                        if (value > 15000) {
                            return 'It cannot be more than 15000 characters.';
                        }

                        return true;
                    },
                }
            }
        },
        props: {
            language: String,
            type: String,
        },
        mounted() {
            this.importOptionsChanged();
            this.$refs.maximumCharactersPerChapterInput.focus();
        },
        methods: {
            importOptionsChanged() {
                var valid = this.$refs.importOptionsForm.validate();
                this.$emit('import-options-changed', {
                    textProcessingMethod: this.processingMethod,
                    maximumCharactersPerChapter: this.maximumCharactersPerChapter,
                    eBookChapterSortMethod: this.eBookChapterSortMethod,
                    isValid: valid
                });
            }
        }
    }
</script>
