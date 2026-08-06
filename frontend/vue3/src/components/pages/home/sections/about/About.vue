<script setup lang="ts">
import { ref } from 'vue'

const linguaCafeLinks = ref([
    {
        label: 'Attributions',
        icon: 'i-lucide-copyright',
        to: '/attributions',
    },
    {
        label: 'Overview',
        icon: 'i-lucide-layout-template',
        to: 'https://simjanos-dev.github.io/LinguaCafeHome',
    },
])

const contactLinks = ref([
    {
        label: 'Discord chat',
        icon: 'i-lucide-message-circle',
        to: 'https://discord.gg/wZYZYrdaeP',
    },
    {
        label: 'Github',
        icon: 'i-lucide-github',
        to: 'https://github.com/simjanos-dev/LinguaCafe',
    },
])

const updateHistoryLinks = ref([
    {
        label: 'Update history',
        icon: 'i-lucide-history',
        to: '/updates/history',
    },
])
</script>

<template>
    <div>
        <UPageGrid>
            <UPageCard variant="soft">
                <template #title
                    ><div class="flex items-center">
                        <UIcon
                            class="text-primary mr-2 size-5"
                            name="i-lucide-cup-soda"
                        />LinguaCafe
                    </div></template
                >
                <template #description>
                    You can find more information about LinguaCafe on these links.
                    <UPageLinks class="mt-4" :links="linguaCafeLinks" />
                </template>
            </UPageCard>
            <UPageCard variant="soft">
                <template #title
                    ><div class="flex items-center">
                        <UIcon class="text-primary mr-2 size-5" name="i-lucide-contact" />Contact
                    </div></template
                >
                <template #description>
                    You can contact the developer of LinguaCafe on these platforms.
                    <UPageLinks class="mt-4" :links="contactLinks" />
                </template>
            </UPageCard>
            <UPageCard variant="soft">
                <template #title
                    ><div class="flex items-center">
                        <UIcon class="text-primary mr-2 size-5" name="i-lucide-history" />Version
                    </div></template
                >
                <template #description>
                    The current LinguaCafe version is v0.15-beta.
                    <UPageLinks class="mt-4" :links="updateHistoryLinks" />
                </template>
            </UPageCard>
        </UPageGrid>
    </div>
</template>
