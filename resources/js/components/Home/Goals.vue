<template>
    <div>
        <div class="subheader subheader-margin-top">Daily goals</div>
        <div id="goals">
            <goal 
                v-for="(goal, index) in goals" :key="index"
                :type="goal.type"
                :goal-quantity="goal.quantity"
                :todays-achieved-quantity="goal.todaysQuantity"
                :color="goalColors[goal.type]"
                >
            </goal>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                goals: [],

                goalColors: {
                    'review': this.$vuetify.theme.currentTheme.dailyGoalReview,
                    'read_words': this.$vuetify.theme.currentTheme.dailyGoalReading,
                    'learn_words': this.$vuetify.theme.currentTheme.dailyGoalNew,
                    'reviews_due': '#ffc68c',
                },
            }
        },
        props: {
        },
        mounted() {
            axios.post('/goals/get').then((response) => {
                this.goals = response.data;
            });
        },
        methods: {
        }
    }
</script>
