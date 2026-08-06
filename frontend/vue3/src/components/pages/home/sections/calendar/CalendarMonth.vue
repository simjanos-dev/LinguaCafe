<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useMoment } from '@composables/useMoment'

import type { Moment } from 'moment'
import type { CalendarSelectableStatEnum } from '@lctypes/calendar/Calendar'
import type { CalendarWeek } from '@lctypes/calendar/CalendarWeek'

type Props = {
    month: Moment
    selectedGoal: CalendarSelectableStatEnum
    mostDueReviews: number
}

const { month, selectedGoal } = defineProps<Props>()
const weeksOfMonth = ref<CalendarWeek[]>([])
const moment = useMoment()

const setMonthDays = () => {
    weeksOfMonth.value = []

    const yearStart = month.clone().startOf('month')
    const calendarStart = month.clone().startOf('month').startOf('week')
    const calendarEnd = month.clone().endOf('month').endOf('week')

    let currentDate = calendarStart.clone()

    while (currentDate.isBefore(calendarEnd)) {
        if (weeksOfMonth.value.length === 0 || currentDate.day() === 1) {
            weeksOfMonth.value.push({
                week: currentDate.week(),
                days: [],
            })
        }

        let weekIndex = weeksOfMonth.value.length - 1
        weeksOfMonth.value[weekIndex]?.days.push({
            day: currentDate.format('D'),
            date: currentDate.format('YYYY-MM-DD'),
            outsideYear: currentDate.year() !== yearStart.year(),
            outsideMonth:
                currentDate.year() !== yearStart.year() || currentDate.month() !== month.month(),
        })

        currentDate.add(1, 'days')
    }
}

watch(
    () => month,
    () => {
        setMonthDays()
    }
)

onMounted(() => {
    setMonthDays()
})
</script>

<template>
    <div class="w-full bg-elevated/50 rounded-lg p-4">
        <div class="w-full flex flex-wrap">
            <div class="w-full font-bold text-xl">
                {{ month.format('Y MMMM') }}
            </div>

            <div class="w-full flex flex-wrap justify-evenly">
                <div
                    class="flex justify-center items-center mx-1 flex-1 md:h-20 select-none rounded-sm"
                    v-for="day in 7"
                >
                    {{
                        moment()
                            .startOf('week')
                            .add(day - 1, 'days')
                            .format('dd')
                    }}
                </div>
            </div>
            <div
                class="w-full flex flex-wrap justify-evenly my-1"
                v-for="(week, weekIndex) in weeksOfMonth"
                :key="weekIndex"
            >
                <CalendarDay
                    v-for="(day, dayIndex) in week.days"
                    :key="dayIndex"
                    :day="day"
                    :selected-goal="selectedGoal"
                    :most-due-reviews="mostDueReviews"
                />
            </div>
        </div>
    </div>
</template>
