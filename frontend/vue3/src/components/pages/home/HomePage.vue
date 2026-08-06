<script setup lang="ts">
import { computed } from 'vue'
import PageSectionTitle from '@components/custom/PageSectionTitle.vue'
import ContentSpacer from '@components/custom/ContentSpacer.vue'
import Store from '@src/store/Store'

import About from '@components/pages/home/sections/about/About.vue'
import Calendar from '@components/pages/home/sections/calendar/Calendar.vue'
import DailyGoals from '@components/pages/home/sections/goals/DailyGoals.vue'
import PasswordChange from '@components/pages/home/sections/password/PasswordChange.vue'
import Statistics from '@components/pages/home/sections/statistics/Statistics.vue'

type HomePageSection = {
    title?: string
    component: string
    visible: boolean
    params?: Record<string, any>
}

const homePageSections = computed<HomePageSection[]>(() => [
    {
        title: 'Password change',
        component: 'HomePagePasswordChange',
        visible: !Store.user?.password_changed,
    },
    {
        component: 'HomePageCalendar',
        visible: true,
        params: {
            isHeatmap: false,
        },
    },
    {
        component: 'HomePageCalendar',
        visible: true,
        params: {
            isHeatmap: true,
        },
    },
    {
        title: 'Daily goals',
        component: 'HomePageDailyGoals',
        visible: true,
    },
    {
        title: 'Statistics',
        component: 'HomePageStatistics',
        visible: true,
    },
    {
        title: 'About',
        component: 'HomePageAbout',
        visible: true,
    },
])
</script>

<template>
    <ContentSpacer class="user-select">
        <template v-for="(homePageSection, index) in homePageSections" :key="index">
            <template v-if="homePageSection.visible">
                <PageSectionTitle
                    v-if="homePageSection.title"
                    :title="homePageSection.title"
                    :class="[index > 0 ? 'mt-8' : '']"
                />

                <About v-if="homePageSection.component === 'HomePageAbout'" />
                <Calendar
                    v-if="
                        homePageSection.component === 'HomePageCalendar' &&
                        homePageSection.params?.isHeatmap &&
                        Store.window.widthWithoutSidebar >= 1200
                    "
                    :is-heatmap="true"
                />
                <Calendar
                    v-if="
                        homePageSection.component === 'HomePageCalendar' &&
                        !homePageSection.params?.isHeatmap
                    "
                    :is-heatmap="false"
                />
                <DailyGoals v-if="homePageSection.component === 'HomePageDailyGoals'" />
                <Statistics v-if="homePageSection.component === 'HomePageStatistics'" />
                <PasswordChange v-if="homePageSection.component === 'HomePagePasswordChange'" />
            </template>
        </template>
    </ContentSpacer>
</template>
