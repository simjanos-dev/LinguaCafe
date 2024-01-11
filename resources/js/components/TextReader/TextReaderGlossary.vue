<template>
    <v-dialog v-model="value" scrollable persistent max-width="820">
        <v-card 
            id="text-reader-glossary"
            outlined
            class="rounded-lg"
        >
            <v-card-title>Glossary</v-card-title>
            <v-card-text class="pt-6 px-0">
                <template v-for="(word, index) in glossary">
                    <div class="glossary-entry pa-4" :key="index">
                        <div class="glossary-title">
                            <!-- Glossary entry stage -->
                            <div class="stage" :stage="word.stage">
                                {{ Math.abs(word.stage) }}
                            </div>
                            
                            <!-- Glossary entry word-->
                            <div class="word" v-if="word.base_word == ''">
                                {{ word.word }} &nbsp;&nbsp; ({{ word.reading }})
                            </div>
                            <div class="word" v-if="word.base_word !== ''">
                                {{ word.base_word }} &nbsp;&nbsp; ({{ word.base_word_reading }})
                                <i class="fas fa-long-arrow-alt-right"></i> 
                                {{ word.word }} &nbsp;&nbsp; ({{ word.reading }})
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
