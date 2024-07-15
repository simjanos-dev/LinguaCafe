<template>
    <v-card 
        outlined 
        id="vocab-hover-box" 
        :class="{
            'd-flex': true,
            'flex-wrap': true,
            'rounded-lg': true,
            'pa-3': true,
            'arrow-top': $props.arrowPosition == 'top',
        }"
        :style="{
            'position': 'absolute',
            'left': $props.positionLeft + 'px',
            'top': $props.positionTop + 'px',
        }"
    >
        <div class="header d-flex justify-space-between">
            <!-- Reading -->
            <div v-if="$props.reading.length">
                {{ $props.reading }}
            </div>

            <!-- Stage -->
            <div class="stage rounded-pill px-4" v-if="$props.stage !== null" :stage="$props.stage">
                {{ $props.stage * -1 }}
            </div>
        </div>

        <!-- Translations -->
        <ul class="mb-0 pl-0">
            <!-- User translations -->
            <li v-for="(translation, translationIndex) in userTranslationList" :key="'user-' + translationIndex">
                <v-icon small>mdi-account-edit</v-icon> {{ translation }}
            </li>

            <!-- Dictionary translations loading -->
            <template v-if="$props.dictionaryTranslation === 'loading'">
                <li>
                    <v-progress-circular indeterminate class="mx-1" size="10" width="2" color="primary"></v-progress-circular> searching
                </li>
            </template>

            <!-- Dictionary translations -->
            <template v-if="!['loading', 'dictionary-search-disabled'].includes($props.dictionaryTranslation) && dictionaryTranslationList.length">
                <li v-for="(translation, translationIndex) in dictionaryTranslationList" :key="'dictionary-' + translationIndex">
                    <v-icon small>mdi-list-box</v-icon> {{ translation }}
                </li>
            </template>

            <!-- No dictionary search result -->
            <template v-if="!['loading', 'deepl-disabled'].includes($props.deeplTranslation) && !dictionaryTranslationList.length">
                <li>
                    <v-icon small>mdi-list-box</v-icon> No dictionary results
                </li>
            </template>
    
            <!-- Deepl translations -->
            <template v-if="!['loading', 'deepl-disabled'].includes($props.deeplTranslation) && $props.deeplTranslation.length">
                <li key="deepl-translation" v-for="(translation, index) in deeplTranslationList" :key="'deepl-' + index">
                    <v-icon small>mdi-translate</v-icon> {{ translation }}
                </li>
            </template>

            <!-- Deepl translations loading -->
            <template v-if="$props.deeplTranslation === 'loading'">
                <li>
                    <v-progress-circular indeterminate class="mx-1" size="10" width="2" color="#92B9E2"></v-progress-circular> searching
                </li>
            </template>
        </ul>
    </v-card>
</template>

<script>
    export default {
        data: function() {
            return {
                userTranslationList: [],
                dictionaryTranslationList: [],
                deeplTranslationList: [],
            }
        },
        props: {
            userTranslation: String,
            dictionaryTranslation: String,
            deeplTranslation: String,
            positionLeft: Number,
            positionTop: Number,
            arrowPosition: String,
            reading: String,
            stage: Number,
        },
        mounted() {
            this.translationList = []; 
            this.dictionaryList = []; 

            if (this.$props.userTranslation.length) {
                this.userTranslationList = this.$props.userTranslation.split(';');
            }

            if (this.$props.dictionaryTranslation.length) {
                this.dictionaryTranslationList = this.$props.dictionaryTranslation.split(';');
            }

            if (this.$props.deeplTranslation.length) {
                this.deeplTranslationList = this.$props.deeplTranslation.split(';');
            }

            
        },
        methods: {
        }
    }
</script>
