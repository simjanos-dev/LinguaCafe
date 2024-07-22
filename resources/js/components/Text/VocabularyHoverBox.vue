<template>
    <v-card 
        outlined 
        id="vocab-hover-box" 
        :class="{
            'd-flex': true,
            'flex-wrap': true,
            'rounded-lg': true,
            'pa-3': true,
            'arrow-top': arrowPosition == 'top',
        }"
        :style="{
            'position': 'absolute',
            'left': positionLeft + 'px',
            'top': positionTop + 'px',
        }"
    >
        <div class="header d-flex justify-space-between">
            <!-- Reading -->
            <div v-if="reading.length">
                {{ reading }}
            </div>

            <!-- Stage -->
            <div class="stage rounded-pill px-4" v-if="stage !== null" :stage="stage">
                {{ stage * -1 }}
            </div>
        </div>

        <!-- Translations -->
        <ul class="mb-0 pl-0">
            <!-- User translations -->
            <li v-for="(translation, translationIndex) in userTranslationList" :key="'user-' + translationIndex">
                <v-icon small>mdi-account-edit</v-icon> {{ translation }}
            </li>

            <!-- Dictionary translations loading -->
            <template v-if="dictionaryTranslation === 'loading'">
                <li>
                    <v-progress-circular indeterminate class="mx-1" size="10" width="2" color="primary"></v-progress-circular> searching
                </li>
            </template>

            <!-- Dictionary translations -->
            <template v-if="!['loading', 'dictionary-search-disabled'].includes(dictionaryTranslation) && dictionaryTranslationList.length">
                <li v-for="(translation, translationIndex) in dictionaryTranslationList" :key="'dictionary-' + translationIndex">
                    <v-icon small>mdi-list-box</v-icon> {{ translation }}
                </li>
            </template>

            <!-- No dictionary search result -->
            <template v-if="!['loading', 'deepl-disabled'].includes(deeplTranslation) && !dictionaryTranslationList.length">
                <li>
                    <v-icon small>mdi-list-box</v-icon> No dictionary results
                </li>
            </template>
    
            <!-- Deepl translations -->
            <template v-if="!['loading', 'deepl-disabled'].includes(deeplTranslation) && deeplTranslation.length">
                <li key="deepl-translation" v-for="(translation, index) in deeplTranslationList" :key="'deepl-' + index">
                    <v-icon small>mdi-translate</v-icon> {{ translation }}
                </li>
            </template>

            <!-- Deepl translations loading -->
            <template v-if="deeplTranslation === 'loading'">
                <li>
                    <v-progress-circular indeterminate class="mx-1" size="10" width="2" color="#92B9E2"></v-progress-circular> searching
                </li>
            </template>
        </ul>
    </v-card>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        data: function() {
            return {
                userTranslationList: [],
                dictionaryTranslationList: [],
                deeplTranslationList: [],
            }
        },
        computed: mapState({
            arrowPosition: state => state.hoverVocabularyBox.arrowPosition,
            positionLeft: state => state.hoverVocabularyBox.positionLeft,
            positionTop: state => state.hoverVocabularyBox.positionTop,
            reading: state => state.hoverVocabularyBox.reading,
            stage: state => state.hoverVocabularyBox.stage,
            dictionaryTranslation: state => state.hoverVocabularyBox.dictionaryTranslation,
            deeplTranslation: state => state.hoverVocabularyBox.deeplTranslation,
        }),
        props: {
        },
        mounted() {
            this.translationList = []; 
            this.dictionaryList = []; 

            if (this.$store.state.hoverVocabularyBox.userTranslation.length) {
                this.userTranslationList = this.$store.state.hoverVocabularyBox.userTranslation.split(';');
            }

            if (this.$store.state.hoverVocabularyBox.dictionaryTranslation.length) {
                this.dictionaryTranslationList = this.$store.state.hoverVocabularyBox.dictionaryTranslation.split(';');
            }

            if (this.$store.state.hoverVocabularyBox.deeplTranslation.length) {
                this.deeplTranslationList = this.$store.state.hoverVocabularyBox.deeplTranslation.split(';');
            }

            
        },
        methods: {
        }
    }
</script>
