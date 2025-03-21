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
            <div v-if="reading.length" class="selected-font">
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
    
            <!-- Api translations -->
            <template v-if="apiTranslations.length && apiTranslations[0] !== 'loading' && apiTranslations[0] !== 'error'">
                <li key="api-translation" v-for="(translation, index) in apiTranslations" :key="'api-' + index">
                    <v-icon small>mdi-translate</v-icon> {{ translation }}
                </li>
            </template>

            <!-- Api translations loading -->
            <template v-if="apiTranslations.length && apiTranslations[0] === 'loading'">
                <li>
                    <v-progress-circular indeterminate class="mx-1" size="10" width="2" color="#92B9E2"></v-progress-circular> searching
                </li>
            </template>
        </ul>

        <!-- Image -->
        <div class="d-block w-100 my-2" v-if="image">
            <v-img
                :src="'/images/' + imageTypeUrlSlug + '/get/' + image + '?rid=' + Math.random()"
                width="100%"
                :aspect-ratio="16/9"
                contain
                class="rounded-lg"
            />
        </div>
    </v-card>
</template>

<script>
    import { mapState } from 'vuex';
    
    export default {
        data: function() {
            return {
                userTranslationList: [],
                dictionaryTranslationList: [],
            }
        },
        computed: {
           ...mapState({
                arrowPosition: state => state.hoverVocabularyBox.arrowPosition,
                positionLeft: state => state.hoverVocabularyBox.positionLeft,
                positionTop: state => state.hoverVocabularyBox.positionTop,
                reading: state => state.hoverVocabularyBox.reading,
                stage: state => state.hoverVocabularyBox.stage,
                dictionaryTranslation: state => state.hoverVocabularyBox.dictionaryTranslation,
                apiTranslations: state => state.hoverVocabularyBox.apiTranslations,
                image: state => state.hoverVocabularyBox.image,
                hoveredPhrase: state => state.hoverVocabularyBox.hoveredPhrase,
            }),
            imageTypeUrlSlug() {
                if (this.hoveredPhrase === -1) {
                    return 'word-image'
                }

                return 'phrase-image'
            }
        },
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

            console.log('hover mounted', this.image, this.hoveredPhrase)
        },
        methods: {
            
        }
    }
</script>
