<script setup lang="ts">
import type { TabsItem } from '@nuxt/ui'

const tabs = [
    {
        label: 'Users',
        icon: 'i-lucide-users',
        slot: 'users',
    },
    {
        label: 'Languages',
        icon: 'i-lucide-languages',
        slot: 'languages',
    },
    {
        label: 'Dictionaries',
        icon: 'i-lucide-book-a',
        slot: 'dictionaries',
    },
    {
        label: 'Fonts',
        icon: 'i-lucide-type-outline',
        slot: 'fonts',
    },
    {
        label: 'Api',
        icon: 'i-lucide-waypoints',
        slot: 'api',
    },
    {
        label: 'Reviews',
        icon: 'i-lucide-rotate-cw-fading-clock',
        slot: 'reviews',
    },
    {
        label: 'Backup',
        icon: 'i-lucide-database-backup',
        slot: 'backup',
    },
] satisfies TabsItem[]
</script>

<template>
    <ContentSpacer class="w-full">
        <!-- Tabs -->
        <UTabs :items="tabs" size="lg">
            <!-- <template #account>
                <Account />
            </template> -->
        </UTabs>
    </ContentSpacer>
</template>
