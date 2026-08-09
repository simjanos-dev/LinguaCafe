<script setup lang="ts">
import { ref, computed, watch } from 'vue'

import type { FormError } from '@nuxt/ui'
import type { Language } from '@lctypes/Language'
import LanguageService from '@services/languages/LanguageService'
import { toUpperCase } from '@src/helpers/StringHelper'

const languageService = new LanguageService()

// modal dialog
type Props = {
    modelValue: boolean
    language: Language
}

const { modelValue, language } = defineProps<Props>()
const modalOpened = computed({
    get: () => modelValue,
    set: value => emit('update:modelValue', value),
})

const emit = defineEmits(['update:modelValue', 'installed'])

// reset form values on opening dialog
watch(modalOpened, value => {
    if (!value) {
        return
    }

    resetForm()
})

watch(
    () => language,
    value => {
        if (!value) {
            return
        }

        resetForm()
    }
)

const resetForm = () => {
    finished.value = false
    installLanguageErrors.value = []
}

// form
const finished = ref<boolean>(false)
const loading = ref<boolean>(false)
const installLanguageErrors = ref<FormError[]>([])

const installLanguage = async function () {
    loading.value = true
    const installResult = await languageService.installLanguage(language)
    loading.value = false

    if (installResult.ok) {
        emit('installed')
        finished.value = true
    }
    if (!installResult.ok && installResult.errorMessages) {
        installLanguageErrors.value = installResult.errorMessages
    }
}
</script>

<template>
    <UModal
        class="w-full max-w-lg"
        variant=""
        title="Install language"
        v-model:open="modalOpened"
        :close="!loading"
        :dismissible="false"
    >
        <template #body>
            <div class="p-4" v-if="!loading && !finished">
                Do you want to install {{ toUpperCase(language.name, true) }} language? It will
                require internet connection, and can take several minutes.
            </div>

            <!-- Saving -->
            <div v-if="loading" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">
                    Installing {{ toUpperCase(language.name, true) }}. It can take a while...
                </div>
                <UIcon name="i-lucide-loader-circle" class="size-12 animate-spin text-primary" />
            </div>

            <!-- Updated -->
            <div v-if="finished" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">Successful installation</div>
                <UIcon name="i-lucide-circle-check" class="size-12 text-success" />
            </div>

            <FormResponseErrorAlert
                v-if="!finished && !loading"
                class="mt-4"
                :title="'Error'"
                :error-messages="installLanguageErrors"
            />
        </template>
        <template #footer>
            <div class="w-full mt-4 flex justify-end">
                <UButton
                    class="justify-center font-normal mr-2"
                    :label="finished ? 'Close' : 'Cancel'"
                    type="button"
                    variant="link"
                    color="neutral"
                    @click="modalOpened = false"
                    :disabled="loading"
                />

                <UButton
                    v-if="!finished"
                    class="justify-center font-normal"
                    label="Yes"
                    form="edit-goal-form"
                    loading-icon="i-lucide-loader-circle"
                    :disabled="loading"
                    @click="installLanguage"
                />
            </div>
        </template>
    </UModal>
</template>
