<script setup lang="ts">
import type { TabsItem } from '@nuxt/ui'

const tabs = [
    {
        label: 'Account',
        icon: 'i-lucide-user',
        slot: 'account',
    },
    {
        label: 'Theme',
        icon: 'i-lucide-palette',
        slot: 'password',
        disabled: true,
    },
    {
        disabled: true,
    },
    {
        disabled: true,
    },
] satisfies TabsItem[]
</script>

<template>
    <ContentSpacer class="w-full">
        <!-- Tabs -->
        <UTabs :items="tabs" size="lg">
            <template #account>
                <Account />
            </template>
        </UTabs>
    </ContentSpacer>
</template>
