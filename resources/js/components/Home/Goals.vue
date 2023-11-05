<template>
    <div>
        <edit-goal-dialog
            v-if="goalEditing.active"
            v-model="goalEditing.active"
            :_id="goalEditing.id"
            :_name="goalEditing.name"
            :_goalQuantity="goalEditing.goalQuantity"
            :_achievedQuantity="goalEditing.achievedQuantity"
            @save="loadGoals"
        />

        <div class="subheader subheader-margin-top">Daily goals</div>
        <div id="goals">
            <goal 
                v-for="(goal, index) in goals" :key="index"
                :id="goal.id"
                :name="goal.name"
                :goal-quantity="goal.quantity"
                :todays-achieved-quantity="goal.todaysQuantity"
                :percentage="Math.round((goal.todaysQuantity / goal.quantity) * 100)"
                color="primary"
                @edit="editGoal"
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
                goalEditing: {
                    active: false,
                    id: -1,
                    type: '',
                    goalQuantity: -1,
                    achievedQuantity: -1,
                }
            }
        },
        props: {
        },
        mounted() {
            this.loadGoals();
        },
        methods: {
            loadGoals() {
                axios.post('/goals/get').then((response) => {
                    this.goals = response.data;
                    this.$emit('goal-quantity-change');
                });
            },
            editGoal(goal) {
                this.goalEditing.active = true;
                this.goalEditing.id = goal.id;
                this.goalEditing.name = goal.name;
                this.goalEditing.goalQuantity = goal.goalQuantity;
                this.goalEditing.achievedQuantity = goal.achievedQuantity;
            }
        }
    }
</script>
