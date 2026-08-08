<script setup lang="ts">
import { toUpperCase } from '@src/helpers/StringHelper'
import Store from '@src/store/Store'
import { onMounted, ref, reactive, computed } from 'vue'
import * as zod from 'zod'

import LanguageService from '@services/languages/LanguageService'
import type { FormError } from '@nuxt/ui'

const languageService = new LanguageService()

// form
const finished = ref<boolean>(false)
const formErrors = ref<FormError[]>([])
const formSchema = zod
    .object({
        confirmationText: zod.string('Confirmation is required'),
    })
    .refine(data => data.confirmationText == confirmMessage.value, {
        message: 'Incorrect confirmation text',
        path: ['confirmationText'],
    })

type Schema = zod.output<typeof formSchema>

const formState = reactive<Partial<Schema>>({
    confirmationText: undefined,
})

const languageString = computed(() => {
    return toUpperCase(Store.language?.name ?? '', true)
})

const confirmMessageLabel = computed(() => {
    return `Type "${confirmMessage.value}"`
})

const confirmMessage = computed(() => {
    let languageString = Store.language?.name ?? ''

    return `delete all my ${languageString} data`
})

const deleteLanguageData = async () => {
    formErrors.value = []
    const result = await languageService.deleteLanguageData(Store.language)

    if (!result.ok && result.errorMessages) {
        formErrors.value = result.errorMessages
    }

    if (result.ok) {
        finished.value = true
    }
}

onMounted(() => {})
</script>

<template>
    <div class="mt-4">
        <PageSectionTitle>
            <template #left>
                <div class="flex items-center justify-left">
                    <UIcon name="i-lucide-alert-triangle" class="size-6 text-error" />
                    <h1 class="text-pretty font-bold text-highlighted text-xl sm:text-xl ml-2">
                        Delete language data
                    </h1>
                </div>
            </template>
        </PageSectionTitle>

        <div
            class="flex flex-wrap items-center bg-elevated/50 rounded-lg p-4 my-4"
            v-if="!finished"
        >
            <span class="w-full">
                This action will delete <b>all</b> your data in {{ languageString }}. Your data in
                other languages will not be affected.
            </span>

            <span class="w-full mt-4 mb-1"> Data to be deleted: </span>

            <ul class="w-full list-disc">
                <li class="ml-8">Books</li>
                <li class="ml-8">Chapters</li>
                <li class="ml-8">Vocabulary</li>
                <li class="ml-8">Phrases</li>
                <li class="ml-8">Example sentences</li>
                <li class="ml-8">Achieved goal statistics</li>
            </ul>

            <UForm
                id="delete-language-form"
                class="mt-8 w-full"
                :schema="formSchema"
                :state="formState"
                :validate-on="['change', 'input']"
                @submit="deleteLanguageData()"
            >
                <template v-if="!finished">
                    <UFormField
                        name="confirmationText"
                        :label="confirmMessageLabel"
                        class="max-w-[300px]"
                        required
                    >
                        <UInput
                            v-model="formState.confirmationText"
                            size="lg"
                            variant="subtle"
                            class="w-full"
                            required
                            placeholder="Confirm deletion"
                        />
                    </UFormField>

                    <div class="w-full flex justify-end">
                        <UButton
                            class="justify-center font-normal mt-4"
                            label="Delete language data"
                            color="primary"
                            type="submit"
                            form="delete-language-form"
                            loading-icon="i-lucide-loader-circle"
                            loading-auto
                        />
                    </div>
                </template>

                <FormResponseErrorAlert
                    class="mt-4"
                    :title="'Error'"
                    :error-messages="formErrors"
                />
            </UForm>
        </div>

        <!-- Finished -->
        <div class="flex flex-wrap items-center bg-elevated/50 rounded-lg p-4 my-4" v-if="finished">
            <div v-if="finished" class="w-full flex flex-wrap justify-center my-8">
                <div class="w-full flex justify-center mb-4 text-lg">
                    Your {{ languageString }} data has been deleted successfully
                </div>
                <UIcon name="i-lucide-circle-check" class="size-12 text-success" />
            </div>
        </div>
    </div>
</template>
