<script setup lang="ts">
import { ref, computed } from 'vue'
import CalendarService from '@services/calendar/CalendarService'
import EditGoalPopup from '@components/popups/goals/EditGoalPopup.vue'
import Store from '@src/store/Store'
import { GoalType } from '@lctypes/goals/Goal'
import moment from 'moment'

import type { Goal } from '@lctypes/goals/Goal'

const loading = ref<boolean>(true)
const editedGoal = ref<null | Goal>(null)
const showEditGoalPopup = ref<boolean>(false)

const calendarService = new CalendarService()

const openEditGoalPopup = function (goalType: GoalType) {
    if (!Store.calendar?.goals[goalType]) {
        return
    }

    editedGoal.value = Store.calendar.goals[goalType]
    showEditGoalPopup.value = true
}

const goals = computed(() => {
    let today = moment().format('YYYY-MM-DD')
    let todaysGoals = Object.values(GoalType).map((goalType: GoalType) => {
        let achievedQuantity =
            Store.calendar?.goals[goalType]?.goalAchievements[today]?.achieved_quantity ?? null

        let correctedQuantity = achievedQuantity

        let goalQuantity =
            Store.calendar?.goals[goalType]?.goalAchievements[today]?.goal_quantity ?? null

        if (achievedQuantity !== null && goalQuantity !== null && achievedQuantity > goalQuantity) {
            correctedQuantity = goalQuantity
        }

        return [
            goalType,
            Store.calendar?.goals[goalType]?.goalAchievements[today]
                ? {
                      goalType: goalType,
                      achievedQuantity: achievedQuantity,
                      goalQuantity: goalQuantity,
                      correctedQuantity: correctedQuantity,
                  }
                : null,
        ]
    })

    loading.value = false
    return Object.fromEntries(todaysGoals)
})

const goalsUpdated = () => {
    calendarService.loadCalendarData()
}
</script>

<template>
    <div>
        <EditGoalPopup
            v-if="showEditGoalPopup && editedGoal"
            v-model="showEditGoalPopup"
            :goal="editedGoal"
            @updated="goalsUpdated"
        />

        <template v-for="(goalType, goalIndex) in GoalType" :key="goalIndex">
            <div v-if="goals[goalType]">
                <div class="flex items-center bg-elevated/50 rounded-lg p-4 my-4">
                    <div class="w-full shrink">
                        <div class="flex justify-between text-sm text-tuned mb-0.5">
                            <template v-if="loading">
                                <USkeleton class="h-4 w-32" />
                                <div class="flex">
                                    <USkeleton class="h-4 w-16 mr-1" /> /
                                    <USkeleton class="h-4 w-16 ml-1" />
                                </div>
                            </template>

                            <template v-else>
                                <div>{{ goalType }}</div>
                                <div>
                                    {{ goals[goalType].achievedQuantity }} /
                                    {{ goals[goalType].goalQuantity }}
                                </div>
                            </template>
                        </div>
                        <UProgress
                            v-if="goals[goalType]"
                            :model-value="goals[goalType].correctedQuantity"
                            class="mb-4"
                            :max="goals[goalType].goalQuantity"
                            :color="
                                goals[goalType].achievedQuantity >= goals[goalType].goalQuantity
                                    ? 'success'
                                    : 'primary'
                            "
                            size="lg"
                        />
                    </div>

                    <UButton
                        class="ml-1"
                        :icon="loading ? '' : 'i-lucide-pen'"
                        size="md"
                        color="primary"
                        variant="ghost"
                        loading-icon="i-lucide-loader-circle"
                        :loading="loading"
                        @click="openEditGoalPopup(goalType)"
                    />
                </div>
            </div>
        </template>
    </div>
</template>
