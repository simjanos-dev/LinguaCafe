<template>
    <v-dialog v-model="value" persistent scrollable width="900px">
        <v-card 
            id="vocabulary-export-dialog" 
            class="rounded-lg"
        >
            <!-- Title bar -->
            <v-card-title>
                <span class="text-h5">Vocabulary export</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text>
                <!-- Field selection switches -->
                <label class="font-weight-bold mt-2">Select fields to export</label>
                <div class="d-flex flex-wrap">
                    <!-- Lemma switch -->
                    <v-switch
                        v-model="fields.lemma"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Lemma"
                        @change="fieldSwitchChange"
                    ></v-switch>

                    <!-- Word switch -->
                    <v-switch
                        v-model="fields.word"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Word"
                        @change="fieldSwitchChange"
                    ></v-switch>

                    <!-- Lemma reading switch -->
                    <v-switch
                        v-model="fields.lemmaReading"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Lemma reading"
                        @change="fieldSwitchChange"
                    ></v-switch>

                    <!-- Reading switch -->
                    <v-switch
                        v-model="fields.reading"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Reading"
                        @change="fieldSwitchChange"
                    ></v-switch>

                    <!-- Translation switch -->
                    <v-switch
                        v-model="fields.translation"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Translation"
                        @change="fieldSwitchChange"
                    ></v-switch>

                    <!-- Stage switch -->
                    <v-switch
                        v-model="fields.stage"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Level"
                        @change="fieldSwitchChange"
                    ></v-switch>

                    <!-- Added to srs switch -->
                    <v-switch
                        v-model="fields.addedToSrs"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Added to srs date"
                        @change="fieldSwitchChange"
                    ></v-switch>

                    <!-- Read count switch -->
                    <v-switch
                        v-model="fields.readCount"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Read count"
                        @change="fieldSwitchChange"
                    ></v-switch>

                    <!-- Lookup count switch -->
                    <v-switch
                        v-model="fields.lookupCount"
                        hide-details
                        class="vocabulary-export-switch my-1"
                        color="primary"
                        label="Lookup count"
                        @change="fieldSwitchChange"
                    ></v-switch>
                </div>

                <!-- Sample -->
                <label class="font-weight-bold mt-6">Sample preview</label>
                <v-simple-table fixed-header id="vocabulary-export-sample-table" class="border rounded-lg" height="260px">
                    <thead>
                        <tr>
                            <th v-if="fields.lemma">Lemma</th>
                            <th v-if="fields.word">Word</th>
                            <th v-if="fields.lemmaReading">Lemma reading</th>
                            <th v-if="fields.reading">Reading</th>
                            <th v-if="fields.translation">Translation</th>
                            <th v-if="fields.stage">Level</th>
                            <th v-if="fields.addedToSrs">Added to srs</th>
                            <th v-if="fields.readCount">Read count</th>
                            <th v-if="fields.lookupCount">Lookup count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(sampleWord, wordIndex) in $props.sampleWords.slice(0, 10)" :key="wordIndex">
                            <td class="default-font" v-if="fields.lemma">{{ sampleWord.base_word }}</td>
                            <td class="default-font" v-if="fields.word">
                                <!-- Word -->
                                <template v-if="sampleWord.type == 'word'">
                                    {{ sampleWord.word }}
                                </template>

                                <!-- Language with spaces -->
                                <template v-if="sampleWord.type == 'phrase' && languageSpace">
                                    {{ JSON.parse(sampleWord.word).join(' ') }}
                                </template>

                                <!-- Language without spaces -->
                                <template v-if="sampleWord.type == 'phrase' && !languageSpace">
                                    {{ JSON.parse(sampleWord.word).join('') }}
                                </template>
                            </td>
                            <td class="default-font" v-if="fields.lemmaReading">{{ sampleWord.base_word_reading }}</td>
                            <td class="default-font" v-if="fields.reading">{{ sampleWord.reading }}</td>
                            <td v-if="fields.translation">{{ sampleWord.translation }}</td>
                            <td v-if="fields.stage">{{ sampleWord.stage }}</td>
                            <td v-if="fields.addedToSrs">{{ sampleWord.added_to_srs }}</td>
                            <td v-if="fields.readCount">{{ sampleWord.read_count }}</td>
                            <td v-if="fields.lookupCount">{{ sampleWord.lookup_count }}</td>
                        </tr>
                    </tbody>
                </v-simple-table>

            </v-card-text>
            
            <!-- Action buttons -->
            <v-card-actions>
                <v-switch
                    v-model="fields.selectAll"
                    hide-details
                    class="select-all-switch vocabulary-export-switch my-1"
                    color="primary"
                    label="Select all"
                    @change="selectAll"
                ></v-switch>
                <v-switch
                    v-model="fields.selectAll"
                    hide-details
                    class="select-all-switch-small vocabulary-export-switch my-1"
                    color="primary"
                    label="All"
                    @change="selectAll"
                ></v-switch>
                
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>
                <v-btn 
                    rounded 
                    depressed
                    color="primary"
                    :disabled="!fields.any"
                    @click="exportToCsv"
                >Export</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
            language: String,
            languageSpace: Boolean,
            sampleWords: Array
        },
        emits: ['input'],
        data: function() {
            return {
                fields: {
                    selectAll: false,
                    lemma: false,
                    word: false,
                    lemmaReading: false,
                    reading: false,
                    translation: false,
                    stage: false,
                    addedToSrs: false,
                    readCount: false,
                    lookupCount: false
                },
                saving: false,
            };
        },
        mounted: function() {
            console.log(this.$props.sampleWords);
        },
        methods: {
            selectAll() {
                this.fields.lemma = this.fields.selectAll;
                this.fields.word = this.fields.selectAll;
                this.fields.lemmaReading = this.fields.selectAll;
                this.fields.reading = this.fields.selectAll;
                this.fields.translation = this.fields.selectAll;
                this.fields.stage = this.fields.selectAll;
                this.fields.addedToSrs = this.fields.selectAll;
                this.fields.readCount = this.fields.selectAll;
                this.fields.lookupCount = this.fields.selectAll;
                this.fieldSwitchChange();
            },
            fieldSwitchChange() {
                if (
                    this.fields.lemma ||
                    this.fields.word ||
                    this.fields.lemmaReading ||
                    this.fields.reading ||
                    this.fields.translation ||
                    this.fields.stage ||
                    this.fields.addedToSrs ||
                    this.fields.readCount ||
                    this.fields.lookupCount
                ) {
                    this.fields.any = true;
                } else {
                    this.fields.any = false;
                }
            },
            exportToCsv() {
                this.$emit('export-to-csv', {
                    lemma: {
                        export: this.fields.lemma,
                        headerName: 'Lemma',
                        searchObjectProperty: 'base_word'
                    },
                    word: {
                        export: this.fields.word,
                        headerName: 'Word',
                        searchObjectProperty: 'word'
                    },
                    lemmaReading: {
                        export: this.fields.lemmaReading,
                        headerName: 'Lemma reading',
                        searchObjectProperty: 'base_word_reading'
                    },
                    reading: {
                        export: this.fields.reading,
                        headerName: 'Reading',
                        searchObjectProperty: 'reading'
                    },
                    translation: {
                        export: this.fields.translation,
                        headerName: 'Translation',
                        searchObjectProperty: 'translation'
                    },
                    stage: {
                        export: this.fields.stage,
                        headerName: 'Stage',
                        searchObjectProperty: 'stage'
                    },
                    addedToSrs: {
                        export: this.fields.addedToSrs,
                        headerName: 'Added to srs',
                        searchObjectProperty: 'added_to_srs'
                    },
                    readCount: {
                        export: this.fields.readCount,
                        headerName: 'Read count',
                        searchObjectProperty: 'read_count'
                    },
                    lookupCount: {
                        export: this.fields.lookupCount,
                        headerName: 'Lookup count',
                        searchObjectProperty: 'lookup_count'
                    },
                });
                this.$emit('input', false);
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
