<template>
    <v-dialog v-model="value" persistent width="700px">
        <v-card 
            id="vocabulary-edit-dialog" 
            :class="{
                    'rounded-lg': true, 
                    'phrase': $props.itemType == 'Phrase'
            }" 
            :loading="loading || saving"
        >
            <!-- Title bar -->
            <v-card-title>
                <span class="text-h5">{{ $props.itemType }} edit</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <!-- Edit -->
            <v-card-text class="d-flex flex-wrap" v-if="item">

                <!-- Chip informations -->
                <div id="vocabulary-edit-chips" class="pb-4">
                    <v-chip 
                        v-if="item.added_to_srs"
                        dark
                        :small="$vuetify.breakpoint.smAndDown"
                        class="ma-1 pr-4" 
                        color="primary"
                        title="First time added to srs."
                    > 
                        Added on {{ item.added_to_srs }}<v-icon :small="$vuetify.breakpoint.smAndDown" right class="ml-1">mdi-cards</v-icon>
                    </v-chip>

                    <v-chip 
                        v-if="!item.next_review || item.stage >= 0 || !item.added_to_srs"
                        dark
                        :small="$vuetify.breakpoint.smAndDown"
                        class="ma-1" 
                        color="primary" 
                        title="This word or phrase was in review and has been learned."
                    >
                        Finished review
                        <v-icon :small="$vuetify.breakpoint.smAndDown" right class="ml-1">mdi-timer-off-outline</v-icon>
                    </v-chip>

                    <v-chip 
                        v-else
                        dark
                        :small="$vuetify.breakpoint.smAndDown"
                        class="ma-1 pr-3" 
                        color="primary" 
                        title="Next review."
                    >
                        Due on {{ item.next_review }}<v-icon :small="$vuetify.breakpoint.smAndDown" right class="ml-1">mdi-timer-outline</v-icon>
                    </v-chip>
                    
                    <v-chip 
                        v-if="$props.itemType == 'Word'"
                        dark
                        :small="$vuetify.breakpoint.smAndDown"
                        class="ma-1 pr-3" 
                        color="primary"
                        title="Number of lookups for this word or phrase."
                    >
                        {{ item.lookup_count }} lookups<v-icon :small="$vuetify.breakpoint.smAndDown" right class="ml-1">mdi-magnify</v-icon>
                    </v-chip>
                    
                    <v-chip 
                        v-if="$props.itemType == 'Word'"
                        dark
                        :small="$vuetify.breakpoint.smAndDown"
                        class="ma-1 pr-4" 
                        color="primary"
                        title="Number of times this word or phrase has been read."
                    >
                        {{ item.read_count }} reads<v-icon :small="$vuetify.breakpoint.smAndDown" right class="ml-1">mdi-book-open</v-icon>
                    </v-chip>
                    
                </div>

                <!-- Lemma -->
                <div class="vocabulary-edit-side" v-if="$props.itemType == 'Word'">
                    <v-text-field 
                        v-model="item.base_word"
                        @keyup="changed"
                        filled
                        dense
                        rounded
                        label="Lemma"
                    ></v-text-field>

                    <v-text-field 
                        v-model="item.base_word_reading"
                        @keyup="changed"
                        filled
                        dense
                        rounded
                        label="Lemma reading"
                    ></v-text-field>
                </div>

                <!-- Arrows -->
                <div id="vocabulary-edit-middle" v-if="$props.itemType == 'Word'">
                    <div class="arrow pt-2 text-center"><v-icon large>mdi-arrow-right</v-icon></div>
                    <div class="arrow pt-2 text-center"><v-icon large>mdi-arrow-right</v-icon></div>
                </div>

                <!-- Word -->
                <div class="vocabulary-edit-side">
                    <v-text-field 
                        v-if="$props.itemType == 'Word'"
                        v-model="item.word"
                        @keyup="changed"
                        filled
                        dense
                        rounded
                        disabled
                        label="Word"
                    ></v-text-field>
                    <v-text-field 
                        v-else
                        v-model="item.words"
                        @keyup="changed"
                        filled
                        dense
                        rounded
                        disabled
                        label="Phrase"
                    ></v-text-field>

                    <v-text-field 
                        v-model="item.reading"
                        @keyup="changed"
                        filled
                        dense
                        rounded
                        label="Reading"
                    ></v-text-field>
                </div>

                <!-- Translation -->
                <v-textarea
                    v-if="item" 
                    v-model="item.translation"
                    @keyup="changed"
                    filled
                    dense
                    rounded
                    no-resize
                    label="Translation"
                ></v-textarea>

                <!-- Stage -->
                <div id="vocabulary-edit-stage-buttons">
                    <v-btn :value="-7" :class="{'v-btn--active': item.stage == -7}" @click="setStage(-7)">7</v-btn>
                    <v-btn :value="-6" :class="{'v-btn--active': item.stage == -6}" @click="setStage(-6)">6</v-btn>
                    <v-btn :value="-5" :class="{'v-btn--active': item.stage == -5}" @click="setStage(-5)">5</v-btn>
                    <v-btn :value="-4" :class="{'v-btn--active': item.stage == -4}" @click="setStage(-4)">4</v-btn>
                    <v-btn :value="-3" :class="{'v-btn--active': item.stage == -3}" @click="setStage(-3)">3</v-btn>
                    <v-btn :value="-2" :class="{'v-btn--active': item.stage == -2}" @click="setStage(-2)">2</v-btn>
                    <v-btn :value="-1" :class="{'v-btn--active': item.stage == -1}" @click="setStage(-1)">1</v-btn>
                    <v-btn 
                        :value="0" 
                        :class="{'v-btn--active': item.stage == 0}"
                        @click="setStage(0)" 
                    >
                        <v-icon>mdi-check</v-icon>
                    </v-btn>
                    <v-btn 
                        :value="1" 
                        :class="{'v-btn--active': item.stage == 1}" 
                        @click="setStage(1)" 
                        v-if="$props.itemType == 'Word'"
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </div>
            </v-card-text>
            
            <!-- Action bar -->
            <v-card-actions v-if="!loading">
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>
                <v-btn 
                    rounded 
                    depressed 
                    color="primary" 
                    :disabled="saved || saving" 
                    @click="save"
                >Save</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
            itemId: {
                type: Number,
                default: -1
            },
            itemType: {
                type: String,
                default: 'Word'
            }
        },
        emits: ['input'],
        data: function() {
            return {
                saved: true,
                saving: false,
                loading: true,
                item: null,
            };
        },
        mounted: function() {
            axios.get('/vocabulary/' + this.$props.itemType.toLowerCase() + '/get/' + this.$props.itemId).then((response) => {
                this.loading = false;
                this.item = response.data;
                
                if (this.$props.itemType == 'Phrase') {
                    this.item.words = JSON.parse(this.item.words);
                }
            });
        },
        methods: {
            setStage: function(newStage) {
                if (!this.item) {
                    return;
                }

                this.item.stage = newStage;
                this.changed();
            },
            save: function() {
                this.saving = true;
                if (this.$props.itemType == 'Word') {
                    this.saveWord();
                } else {
                    this.savePhrase();
                }
            }, 
            saveWord: function() {
                var saveData = {
                    id: this.item.id,
                    translation: this.item.translation,
                    reading: this.item.reading,
                    base_word: this.item.base_word,
                    base_word_reading: this.item.base_word_reading,
                    stage: this.item.stage,
                };

                axios.post('/vocabulary/word/save', saveData).then(() => {
                    this.saved = true;
                    this.saving = false;
                    this.close();
                });
            },
            savePhrase: function(withStage = false, exampleSentenceChanged = false) {                
                var saveData = {
                    id: this.item.id,
                    reading: this.item.reading,
                    translation: this.item.translation,
                    stage: this.item.stage
                };

                axios.post('/vocabulary/phrase/save', saveData).then(() => {
                    this.saved = true;
                    this.saving = false;
                    this.close();
                });
            },
            changed: function() {
                this.saved = false;
            },
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
