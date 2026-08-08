<script setup lang="ts">
import { ref, computed, reactive, watch } from 'vue'
import * as zod from 'zod'
import UserService from '@services/users/UserService'

import type { FormError } from '@nuxt/ui'

const userService = new UserService()

// modal dialog
type Props = {
    modelValue: boolean
}

const { modelValue } = defineProps<Props>()
const modalOpened = computed({
    get: () => modelValue,
    set: value => emit('update:modelValue', value),
})

const emit = defineEmits(['update:modelValue'])

watch(
    () => modelValue,
    value => {
        if (!value) {
            loading.value = false
            finished.value = false
            formErrors.value = []

            formState.password = undefined
            formState.passwordConfirmation = undefined
        }
    }
)

// form
const loading = ref<boolean>(false)
const finished = ref<boolean>(false)
const formErrors = ref<FormError[]>([])
const showPassword = ref<boolean>(false)
const formSchema = zod
    .object({
        password: zod
            .string('Password is required')
            .min(8, 'Must be between 8 and 32 characters.')
            .max(32, 'Must be between 8 and 32 characters.'),
        passwordConfirmation: zod.string('Password is required'),
    })
    .refine(data => data.password === data.passwordConfirmation, {
        message: 'Passwords do not match',
        path: ['passwordConfirmation'],
    })

type Schema = zod.output<typeof formSchema>

const formState = reactive<Partial<Schema>>({
    password: undefined,
    passwordConfirmation: undefined,
})

const changePassword = async function () {
    formErrors.value = []
    loading.value = true
    finished.value = false
    const result = await userService.updatePassword(
        formState.password,
        formState.passwordConfirmation
    )

    loading.value = false

    if (!result.ok && result.errorMessages) {
        formErrors.value = result.errorMessages
        finished.value = false
    }

    if (result.ok) {
        finished.value = true
    }
}
</script>

<template>
    <UModal
        class="w-96"
        variant=""
        title="Password change"
        v-model:open="modalOpened"
        :dismissible="false"
    >
        <template #body>
            <div class="p-4" v-if="!loading && !finished">
                <UForm
                    id="password-change-form"
                    :schema="formSchema"
                    :state="formState"
                    :validate-on="['change', 'input']"
                    @submit="changePassword"
                >
                    <UFormField label="Password" name="password" required>
                        <UInput
                            v-model="formState.password"
                            size="lg"
                            variant="subtle"
                            class="w-full"
                            required
                            :type="showPassword ? 'text' : 'password'"
                        >
                            <template #trailing>
                                <UTooltip :text="showPassword ? 'Hide password' : 'Show password'">
                                    <UButton
                                        color="neutral"
                                        variant="link"
                                        size="sm"
                                        :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                        :aria-label="
                                            showPassword ? 'Hide password' : 'Show password'
                                        "
                                        :aria-pressed="showPassword"
                                        aria-controls="password"
                                        @click="showPassword = !showPassword"
                                    />
                                </UTooltip>
                            </template>
                        </UInput>
                    </UFormField>

                    <UFormField
                        class="mt-4"
                        label="Password confirmation"
                        name="passwordConfirmation"
                        required
                    >
                        <UInput
                            v-model="formState.passwordConfirmation"
                            size="lg"
                            variant="subtle"
                            class="w-full"
                            required
                            :type="showPassword ? 'text' : 'password'"
                        >
                            <template #trailing>
                                <UTooltip :text="showPassword ? 'Hide password' : 'Show password'">
                                    <UButton
                                        color="neutral"
                                        variant="link"
                                        size="sm"
                                        :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                        :aria-label="
                                            showPassword ? 'Hide password' : 'Show password'
                                        "
                                        :aria-pressed="showPassword"
                                        aria-controls="password"
                                        @click="showPassword = !showPassword"
                                    />
                                </UTooltip>
                            </template>
                        </UInput>
                    </UFormField>
                </UForm>
            </div>

            <!-- Saving -->
            <div v-if="loading" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">Updating...</div>
                <UIcon name="i-lucide-loader-circle" class="size-12 animate-spin text-primary" />
            </div>

            <!-- Updated -->
            <div v-if="finished" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">Successful password change</div>
                <UIcon name="i-lucide-circle-check" class="size-12 text-success" />
            </div>

            <FormResponseErrorAlert class="mt-4" :title="'Error'" :error-messages="formErrors" />
        </template>
        <template #footer>
            <div class="w-full mt-4 flex justify-end">
                <UButton
                    v-if="!finished"
                    class="justify-center font-normal mr-2"
                    label="Cancel"
                    variant="link"
                    color="neutral"
                    type="button"
                    @click="modalOpened = false"
                />

                <UButton
                    v-if="!finished"
                    class="justify-center font-normal"
                    label="Change password"
                    color="primary"
                    type="submit"
                    form="password-change-form"
                    :disabled="loading"
                />

                <UButton
                    v-if="finished"
                    class="justify-center font-normal mr-2"
                    label="Close"
                    variant="link"
                    color="neutral"
                    type="button"
                    @click="modalOpened = false"
                />
            </div>
        </template>
    </UModal>
</template>
