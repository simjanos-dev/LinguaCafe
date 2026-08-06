<script setup lang="ts">
import { useRoute } from 'vue-router'
import { ref } from 'vue'
import updateNotes from '@config/UpdateNotes'

const route = useRoute()

const updateNote = ref(
    updateNotes.find(updateNote => {
        return updateNote.title === route.params.version
    })
)
</script>

<template>
    <ContentSpacer class="w-full">
        <PageSectionTitle :title="`Update notes - ${updateNote?.title} - ${updateNote?.date}`" />

        <div class="my-4 text-muted" v-html="updateNote?.description" />

        <div class="my-4 text-muted" v-if="updateNote?.newFeatures?.length">
            <span class="text-muted font-bold">New features:</span>
            <ul class="list-disc marker:text-primary">
                <li
                    v-for="(feature, featureIndex) in updateNote.newFeatures"
                    :key="featureIndex"
                    class="ml-8"
                >
                    {{ feature }}
                </li>
            </ul>
        </div>

        <div class="my-4 text-muted" v-if="updateNote?.bugFixes?.length">
            <span class="text-muted font-bold">Bug fixes:</span>
            <ul class="list-disc marker:text-primary">
                <li
                    v-for="(bugfix, bugfixIndex) in updateNote.bugFixes"
                    :key="bugfixIndex"
                    class="ml-8"
                >
                    {{ bugfix }}
                </li>
            </ul>
        </div>

        <div class="my-4 text-muted" v-if="updateNote?.otherChanges?.length">
            <span class="text-muted font-bold">Other changes:</span>
            <ul class="list-disc marker:text-primary">
                <li
                    v-for="(otherChange, otherChangeIndex) in updateNote.otherChanges"
                    :key="otherChangeIndex"
                    class="ml-8"
                >
                    {{ otherChange }}
                </li>
            </ul>
        </div>
    </ContentSpacer>
</template>
