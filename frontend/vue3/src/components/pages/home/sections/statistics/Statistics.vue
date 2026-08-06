<script setup lang="ts">
import { ref, onBeforeMount } from 'vue'

import type { Statistics } from '@lctypes/statistics/Statistics'
import StatisticsService from '@services/statistics/StatisticsService'

const statisticsService = new StatisticsService()

const statistics = ref<Statistics | null>()

const loadStatistics = async function () {
    const response = await statisticsService.getStatistics()

    if (response.ok && response.data) {
        statistics.value = response.data
    }

    console.log('statistics loaded', response)
}

onBeforeMount(() => {
    loadStatistics()
})
</script>

<template>
    <div class="flex flex-wrap">
        <div
            v-for="(statistic, statisticIndex) in statistics"
            :key="statisticIndex"
            class="flex items-center mr-3 mb-3 px-6 h-24 p-0 rounded-lg bg-elevated/50"
        >
            <UIcon class="size-8 text-primary" :name="statistic.icon" />
            <div class="flex flex-wrap w-48">
                <span class="w-full text-right text-3xl text-primary">{{ statistic.value }}</span>
                <span class="w-full text-right text-sm">{{ statistic.name }}</span>
            </div>
        </div>
    </div>
</template>
