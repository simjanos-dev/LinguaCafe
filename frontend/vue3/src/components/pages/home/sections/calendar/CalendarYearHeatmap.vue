<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useMoment } from '@composables/useMoment'

import type { CalendarSelectableStatEnum } from '@lctypes/calendar/Calendar'
import type { CalendarWeek } from '@lctypes/calendar/CalendarWeek'
import type { CalendarMonth } from '@lctypes/calendar/CalendarMonth'
import type { Moment } from 'moment'

type Props = {
    month: Moment
    selectedGoal: CalendarSelectableStatEnum
    mostDueReviews: number
}

const { month, selectedGoal, mostDueReviews } = defineProps<Props>()

const moment = useMoment()
const monthsOfYear = ref<CalendarMonth[]>([])

const shouldPushMonth = (date: Moment, calendarYear: Moment): boolean => {
    if (!isFirstMondayOfMonth(date)) {
        return false
    }

    if (isFirstMonthOfCalendarYear(date, calendarYear)) {
        return false
    }

    return true
}

const isFirstMonthOfCalendarYear = (date: Moment, calendarYear: Moment): boolean => {
    return date.year() === calendarYear.year() && date.month() === 0
}

const isFirstMondayOfMonth = (date: Moment): boolean => {
    return date.day() === 1 && date.date() <= 7
}

const setMonthDays = () => {
    monthsOfYear.value = []
    let weeksOfMonth: CalendarWeek[] = []

    const yearStart = month.clone().startOf('year')
    const calendarStart = month.clone().startOf('year').startOf('week')
    const calendarEnd = month.clone().endOf('year').endOf('week').add(1, 'day')

    let currentDate = calendarStart.clone()

    while (currentDate.isBefore(calendarEnd)) {
        if (shouldPushMonth(currentDate, yearStart)) {
            monthsOfYear.value.push({
                month: currentDate.clone().subtract(1, 'month').format('MMM'),
                weeks: weeksOfMonth,
            })

            weeksOfMonth = []
        }

        if (weeksOfMonth.length === 0 || currentDate.day() === 1) {
            weeksOfMonth.push({
                week: currentDate.week(),
                days: [],
            })
        }

        let weekIndex = weeksOfMonth.length - 1
        weeksOfMonth[weekIndex]?.days.push({
            day: currentDate.format('D'),
            date: currentDate.format('YYYY-MM-DD'),
            outsideYear: currentDate.year() !== yearStart.year(),
            outsideMonth:
                currentDate.year() !== yearStart.year() || currentDate.month() !== month.month(),
        })

        currentDate.add(1, 'days')
    }
}

onMounted(() => {
    setMonthDays()
})
</script>

<template>
    <UCard variant="soft" class="w-full h-[220px]">
        <template #default>
            <div class="w-full flex flex-wrap justify-center">
                <div class="flex flex-col flex-wrap">
                    <div
                        class="m-[1px] w-[17px] h-[20px] text-xs md:text-sm flex justify-center items-center select-none rounded-xs"
                    ></div>
                    <div
                        class="m-[1px] pr-2 h-[17px] text-xs md:text-sm flex justify-end items-center rounded-xs"
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
                    class="h-full flex flex-wrap rounded-md"
                    v-for="(month, monthIndex) in monthsOfYear"
                    :key="monthIndex"
                >
                    <div class="">
                        <span class="block w-full">
                            {{ month.month }}
                        </span>
                        <div class="w-full flex justify-start">
                            <div
                                class="flex flex-col flex-wrap"
                                v-for="(week, weekIndex) in month.weeks"
                                :key="weekIndex"
                            >
                                <CalendarHeatmapDay
                                    v-for="(day, dayIndex) in week.days"
                                    :key="dayIndex"
                                    :day="day"
                                    :most-due-reviews="mostDueReviews"
                                    :selected-goal="selectedGoal"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </UCard>
</template>
