<script setup lang="ts">
import attributions from '@config/Attributions'
import ContentSpacer from '@components/custom/ContentSpacer.vue'
</script>

<template>
    <ContentSpacer class="w-full">
        <PageSectionTitle title="Attributions" />
        LinguaCafe uses many public resources. I am very thankful for these projects and for all the
        people who were working on them. They helped me greatly to create LinguaCafe.
        <UCard
            v-for="(attribution, attributionIndex) in attributions"
            :key="attributionIndex"
            class="mt-8"
        >
            <template #header>
                <div class="text-xl">{{ attribution.name }}</div>
                <div class="text-sm">License: {{ attribution.license }}</div>
                <div class="text-sm mt-2" v-if="attribution.description">
                    {{ attribution.description }}
                </div>

                <div class="w-full flex flex-wrap mt-4">
                    <div
                        v-for="(link, linkIndex) in attribution.links"
                        :key="linkIndex"
                        class="w-full"
                    >
                        <ULink :to="link.url">
                            <div class="flex items-center mb-2">
                                <UIcon
                                    class="mr-1 inline-block size-5"
                                    name="i-lucide-external-link"
                                />
                                {{ link.text }}
                            </div>
                        </ULink>
                    </div>
                </div>
            </template>
        </UCard>
    </ContentSpacer>
</template>
