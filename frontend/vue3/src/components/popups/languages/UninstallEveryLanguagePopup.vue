<script setup lang="ts">
import { ref, computed, watch } from 'vue'

import type { FormError } from '@nuxt/ui'
import LanguageService from '@services/languages/LanguageService'

const languageService = new LanguageService()
// modal dialog
type Props = {
    modelValue: boolean
}

const { modelValue } = defineProps<Props>()
const modalOpened = computed({
    get: () => modelValue,
    set: value => emit('update:modelValue', value),
})

const emit = defineEmits(['update:modelValue', 'uninstalled'])

// reset form values on opening dialog
watch(modalOpened, value => {
    if (!value) {
        return
    }

    resetForm()
})

const resetForm = () => {
    finished.value = false
    uninstallLanguageErrors.value = []
}

// form
const finished = ref<boolean>(false)
const loading = ref<boolean>(false)
const uninstallLanguageErrors = ref<FormError[]>([])

const uninstallLanguages = async function () {
    loading.value = true
    const uninstallResult = await languageService.uninstallLanguages()
    loading.value = false

    if (uninstallResult.ok) {
        emit('uninstalled')
        finished.value = true
    }
    if (!uninstallResult.ok && uninstallResult.errorMessages) {
        uninstallLanguageErrors.value = uninstallResult.errorMessages
    }
}
</script>

<template>
    <UModal
        class="w-full max-w-lg"
        variant=""
        title="Unistall languages"
        v-model:open="modalOpened"
        :close="!loading"
        :dismissible="false"
    >
        <template #body>
            <div class="p-4" v-if="!loading && !finished">
                Do you want to uninstall every installed language?
            </div>

            <!-- Saving -->
            <div v-if="loading" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">
                    Uninstalling languages. It can take a while...
                </div>
                <UIcon name="i-lucide-loader-circle" class="size-12 animate-spin text-primary" />
            </div>

            <!-- Updated -->
            <div v-if="finished" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">Successful unistallation</div>
                <UIcon name="i-lucide-circle-check" class="size-12 text-success" />
            </div>

            <FormResponseErrorAlert
                v-if="!finished && !loading"
                class="mt-4"
                :title="'Error'"
                :error-messages="uninstallLanguageErrors"
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
                    @click="uninstallLanguages"
                />
            </div>
        </template>
    </UModal>
</template>
