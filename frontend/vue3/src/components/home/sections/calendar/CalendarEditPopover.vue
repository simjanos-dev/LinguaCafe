<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { GoalType } from '@lctypes/goals/Goal'
import { formatGoalType } from '@src/helpers/GoalHelper'
import GoalService from '@services/goals/GoalService'
import CalendarService from '@services/calendar/CalendarService'

import Store from '@src/store/Store'

import type { CalendarDay } from '@lctypes/calendar/CalendarDay'
import { toUpperCase } from '@src/helpers/StringHelper'
import type { FormError } from '@nuxt/ui'

const goalService = new GoalService()
const calendarService = new CalendarService()

const emit = defineEmits(['update:modelValue'])

type Props = {
    modelValue: boolean
    day: CalendarDay
}

const { modelValue, day } = defineProps<Props>()

const isOpen = computed({
    get: () => modelValue,
    set: (value: boolean) => emit('update:modelValue', value),
})

const goals = computed(() => {
    return Object.values(GoalType).map((goalType: GoalType) => {
        return {
            name: goalType,
            goalType: goalType,
            id: Store.calendar?.goals[goalType].goalAchievements[day.date]?.id ?? null,
            achievedQuantity:
                Store.calendar?.goals[goalType].goalAchievements[day.date]?.achieved_quantity ??
                null,
        }
    })
})

const saving = ref<boolean>(false)
const finished = ref<boolean>(false)
const updateErrors = ref<FormError[]>([])

const editingGoalIndex = ref<number | null>(null)
const editingGoalValue = ref<number | null>(null)

watch(isOpen, value => {
    if (value) {
        saving.value = false
        finished.value = false
        updateErrors.value = []
        editingGoalIndex.value = null
        editingGoalValue.value = null
    }
})

const editGoal = (goalIndex: number) => {
    editingGoalIndex.value = goalIndex
    editingGoalValue.value = goals.value[goalIndex]?.achievedQuantity ?? null
}

const updateGoal = async () => {
    if (editingGoalIndex.value === undefined || editingGoalIndex.value === null) {
        return
    }

    if (editingGoalValue.value === undefined || editingGoalValue.value === null) {
        return
    }

    let goalAchievementId = goals.value[editingGoalIndex.value]?.id
    let goalType = goals.value[editingGoalIndex.value]?.goalType

    if (!goalAchievementId || !goalType) {
        return
    }

    saving.value = true

    const achievementUpdateResponse = await goalService.updateGoalAchievement(
        goalAchievementId,
        goalType,
        day.date,
        editingGoalValue.value
    )

    if (achievementUpdateResponse.ok) {
        saving.value = false
        finished.value = true
        calendarService.loadCalendarData()
    }

    if (!achievementUpdateResponse.ok && achievementUpdateResponse.errorMessages) {
        saving.value = false
        updateErrors.value = achievementUpdateResponse.errorMessages
    }
}
</script>

<template>
    <UModal v-model:open="isOpen" title="Edit achieved goals">
        <template #body>
            <div class="p-4">
                <!-- Select goal for edit -->
                <template v-if="editingGoalIndex === null">
                    <div
                        v-for="(goal, goalIndex) in goals"
                        :key="goalIndex"
                        class="flex justify-between items-center rounded-xl hover:bg-elevated/50 h-10 px-4"
                    >
                        {{ toUpperCase(formatGoalType(goal.name) ?? '', true) }}:
                        {{ goal.achievedQuantity }}
                        <UButton
                            icon="i-lucide-pencil"
                            size="md"
                            color="primary"
                            variant="ghost"
                            @click="editGoal(goalIndex)"
                        />
                    </div>
                </template>

                <!-- Edit goal -->
                <div
                    v-if="editingGoalIndex !== null && !finished && !saving"
                    class="flex flex-wrap justify-center items-center"
                >
                    <div class="flex justify-center text-lg mb-2"></div>
                    <div class="flex flex-wrap justify-center items-center w-full">
                        <div class="flex w-full">
                            <UFormField
                                class="w-full"
                                :label="
                                    toUpperCase(
                                        formatGoalType(goals[editingGoalIndex]?.name ?? '') ?? '',
                                        true
                                    )
                                "
                                name="quantity"
                                required
                            >
                                <UInputNumber
                                    v-model="editingGoalValue"
                                    variant="subtle"
                                    class="w-full ml-2"
                                    size="lg"
                                    :min="0"
                                    :step="1"
                                    :disabled="saving"
                                />
                            </UFormField>
                        </div>
                        <div v-if="updateErrors?.length" class="w-full mt-2">
                            <FormResponseErrorAlert
                                class="mt-4"
                                :title="'Error'"
                                :error-messages="updateErrors"
                            />
                        </div>
                    </div>
                </div>

                <!-- Saving -->
                <div v-if="saving" class="flex flex-wrap justify-center">
                    <div class="w-full flex justify-center mb-4">Updating...</div>
                    <UIcon
                        name="i-lucide-loader-circle"
                        class="size-12 animate-spin text-primary"
                    />
                </div>

                <!-- Updated -->
                <div v-if="finished" class="flex flex-wrap justify-center">
                    <div class="w-full flex justify-center mb-4">Successful update</div>
                    <UIcon name="i-lucide-circle-check" class="size-12 text-success" />
                </div>
            </div>
        </template>

        <template #footer>
            <div class="w-full mt-4 flex justify-end">
                <UButton
                    v-if="editingGoalIndex !== null && !finished"
                    class="w-24 justify-center"
                    @click="updateGoal"
                    :disabled="saving"
                    >Update</UButton
                >
                <UButton
                    v-if="finished"
                    class="w-24 justify-center"
                    @click="emit('update:modelValue', false)"
                    >Close</UButton
                >
            </div>
        </template>
    </UModal>
</template>
