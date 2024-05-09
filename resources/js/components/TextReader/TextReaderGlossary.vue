<template>
    <v-dialog v-model="value" scrollable persistent max-width="820">
        <v-card 
            id="text-reader-glossary"
            outlined
            class="rounded-lg"
        >
            <v-card-title>
                <span class="text-h5">Glossary</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-6 px-0">
                <template v-for="(word, index) in glossary">
                    <div class="glossary-entry pa-4" :key="index">
                        <div class="glossary-title">
                            <!-- Glossary entry stage -->
                            <div class="stage" :stage="word.stage">
                                {{ Math.abs(word.stage) }}
                            </div>
                            
                            <!-- Glossary entry word-->
                            <div class="word default-font" v-if="word.base_word == ''">
                                {{ word.word }} <template v-if="word.reading.length">({{ word.reading }})</template>
                            </div>
                            <div class="word default-font" v-if="word.base_word !== ''">
                                {{ word.base_word }} <template v-if="word.base_word_reading.length">({{ word.base_word_reading }})</template>
                                <i class="fas fa-long-arrow-alt-right mx-2"></i> 
                                {{ word.word }} <template v-if="word.reading.length">({{ word.reading }})</template>
                            </div>
                        </div>

                        <!-- Glossary entry translation-->
                        <div class="translation" v-if="word.translation.length">
                            <ul>
                                <li v-for="(translation, index) in word.translation.split(';')">{{ translation }}</li>
                            </ul>
                        </div>
                    </div>
                </template>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded color="primary" @click="close">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {    
        emits: ['input'],   
        data: function() {
            return {
            }
        },
        props: {
            value : Boolean,
            glossary: Array
        },
        mounted() {
        },
        methods: {
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
