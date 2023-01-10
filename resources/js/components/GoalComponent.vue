<template>
    <v-card outlined class="goal d-flex flex-column rounded-lg mr-3 mb-3">
        <v-card-title>
            {{ titles[type] }}
            <v-spacer></v-spacer>
            <v-btn icon><v-icon>mdi-pencil</v-icon></v-btn>
        </v-card-title>
        <v-card-text class="d-flex flex-column align-center">
            <v-progress-circular
                :size="180"
                :width="20"
                :value="percentage"
                :rotate="270"
                :color="color"
                class="mb-5"
            >{{ todaysAchievedQuantity }} / {{ goalQuantity }}</v-progress-circular>
            
            <div v-if="type == 'read_words'">
                Read {{ goalQuantity }} words from any imported text, YouTube or Netflix subtitles.
            </div>

            <div v-if="type == 'review'">
                Review {{ goalQuantity }} flashcards which are due today. 
            </div>

            <div v-if="type == 'learn_words'">
                Highlight and save {{ goalQuantity }} new words for review.
            </div>
        </v-card-text>
        <v-spacer></v-spacer>
        <v-card-actions>
            <v-spacer></v-spacer> 
            <v-btn plain to="/review/false/-1/-1" v-if="type == 'review'">Start review</v-btn>
            <v-btn plain to="/books" v-if="type == 'read_words' || type == 'learn_words'">Library</v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
    export default {
        data: function() {
            return {
                titles: {
                    'review': 'Review',
                    'read_words': 'Reading',
                    'learn_words': 'New words',
                },
                percentage: 0,
            }
        },
        props: {
            type: String,
            goalQuantity: Number,
            todaysAchievedQuantity: Number,
            color: String
        },
        mounted() {
            setTimeout(() => {
                this.percentage = Math.round((this.todaysAchievedQuantity / this.goalQuantity) * 100);
            }, 100);
        },
        methods: {
        }
    }
</script>
