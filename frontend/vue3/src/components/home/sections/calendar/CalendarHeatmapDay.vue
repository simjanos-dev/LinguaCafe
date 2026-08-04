<script setup lang="ts">
import { ref } from 'vue'
import { isGoalType } from '@lctypes/goals/Goal'
import Store from '@src/store/Store'

import type { CalendarDay } from '@lctypes/calendar/CalendarDay'
import { CalendarSelectableStatEnum } from '@lctypes/calendar/Calendar'
import { formatGoalType } from '@src/helpers/GoalHelper'

type Props = {
    selectedGoal: string
    day: CalendarDay
    mostDueReviews: number
}

const { selectedGoal, day, mostDueReviews } = defineProps<Props>()

const modalOpened = ref<boolean>(false)
const openModal = () => {
    if (day.outsideYear) {
        return
    }

    modalOpened.value = true
}

const getAchievedQuantity = (day: CalendarDay): number | null => {
    if (isGoalType(selectedGoal)) {
        return (
            Store.calendar?.goals[selectedGoal]?.goalAchievements[day.date]?.achieved_quantity ??
            null
        )
    }

    if (selectedGoal === CalendarSelectableStatEnum.ReviewsDue) {
        return Store.calendar?.reviews[day.date]?.quantity ?? null
    }

    return null
}

const getGoalQuantity = (day: CalendarDay): number | null => {
    if (isGoalType(selectedGoal)) {
        return (
            Store.calendar?.goals[selectedGoal]?.goalAchievements[day.date]?.goal_quantity ?? null
        )
    }

    if (selectedGoal === CalendarSelectableStatEnum.ReviewsDue) {
        return mostDueReviews
    }

    return null
}

const getBgColor = (day: CalendarDay): string => {
    let achievedQuantity = getAchievedQuantity(day)

    if (!achievedQuantity) {
        return `bg-elevated`
    }

    return 'bg-primary'
}

const getOpacityStyle = (day: CalendarDay): string => {
    let achievedQuantity = getAchievedQuantity(day)
    let goalQuantity = getGoalQuantity(day)

    if (day.outsideYear) {
        return 'opacity: 0%'
    }

    if (achievedQuantity === 0 || achievedQuantity === null) {
        return 'opacity: 100%'
    }

    let percentage = '0%'
    if (achievedQuantity !== null && goalQuantity) {
        percentage = ((achievedQuantity / goalQuantity) * 100).toFixed(2) + '%'
    }

    return `opacity: ${percentage};`
}

const getDayTooltip = (day: CalendarDay): string => {
    let tooltipText = day.date
    let achievedQuantity = getAchievedQuantity(day)

    tooltipText += '<br>'
    tooltipText += achievedQuantity ? String(achievedQuantity) : 'no'

    tooltipText += ' ' + formatGoalType(selectedGoal)

    return tooltipText
}
</script>

<template>
    <div>
        <CalendarEditPopup v-model="modalOpened" :day="day" />
        <UTooltip
            arrow
            :content="{ side: 'top', sideOffset: 4 }"
            :disabled="day.outsideYear"
            :delay-duration="0"
            :ui="{
                content: 'h-full',
            }"
        >
            <template #content><div v-html="getDayTooltip(day)"></div> </template>

            <div
                :class="[
                    'm-[1px] w-[17px] h-[17px]  overflow-hidden rounded-[3px] select-none',
                    day.outsideYear ? '' : 'bg-elevated hover:bg-success',
                ]"
                @click="openModal"
            >
                <div
                    :class="['m-0 p-0 w-[17px] h-[17px] hover:!opacity-0', getBgColor(day)]"
                    :style="getOpacityStyle(day)"
                    @click="openModal"
                >
                    &nbsp;
                </div>
            </div>
        </UTooltip>
    </div>
</template>
