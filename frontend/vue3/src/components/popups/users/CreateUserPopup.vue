<script setup lang="ts">
import { ref, computed, reactive, watch } from 'vue'
import * as zod from 'zod'
import UserService from '@services/users/UserService'

import type { FormError } from '@nuxt/ui'

const userService = new UserService()

// modal dialog
type Props = {
    modelValue: boolean
    firstUser?: boolean
}

const { modelValue, firstUser = false } = defineProps<Props>()
const modalOpened = computed({
    get: () => modelValue,
    set: value => emit('update:modelValue', value),
})

const emit = defineEmits(['update:modelValue', 'user-created'])

// form
const loading = ref<boolean>(false)
const finished = ref<boolean>(false)
const createUserErrors = ref<FormError[]>([])
const showPassword = ref<boolean>(false)
const createUserFormSchema = zod
    .object({
        name: zod
            .string('Name is required')
            .min(4, 'Name must be at least 4 characters')
            .max(255, 'Name must be below 255 characters'),
        email: zod.email('Invalid email'),
        password: zod
            .string('Password is required')
            .min(8, 'Must be between 8 and 32 characters.')
            .max(32, 'Must be between 8 and 32 characters.'),
        passwordConfirmation: zod.string('Password is required'),
        isAdmin: zod.boolean(),
    })
    .refine(data => data.password === data.passwordConfirmation, {
        message: 'Passwords do not match',
        path: ['passwordConfirmation'],
    })

type Schema = zod.output<typeof createUserFormSchema>

const createUserFormState = reactive<Partial<Schema>>({
    name: undefined,
    email: undefined,
    password: undefined,
    passwordConfirmation: undefined,
    isAdmin: true,
})

const createUser = async function () {
    createUserErrors.value = []
    loading.value = true
    finished.value = false

    const createUserResponse = await userService.createUser(
        createUserFormState.name,
        createUserFormState.email,
        createUserFormState.password,
        createUserFormState.passwordConfirmation,
        createUserFormState.isAdmin
    )

    loading.value = false

    if (!createUserResponse.ok && createUserResponse.errorMessages) {
        createUserErrors.value = createUserResponse.errorMessages
    }

    if (createUserResponse.ok) {
        finished.value = true
        emit('user-created')
    }
}

watch(
    () => modelValue,
    value => {
        if (!value) {
            return
        }

        createUserFormState.name = undefined
        createUserFormState.email = undefined
        createUserFormState.password = undefined
        createUserFormState.passwordConfirmation = undefined
        createUserFormState.isAdmin = true

        createUserErrors.value = []
        loading.value = false
        finished.value = false
    }
)
</script>

<template>
    <UModal class="w-96" variant="" title="Create user" v-model:open="modalOpened">
        <template #body>
            <div class="p-4" v-if="!loading && !finished">
                <UForm
                    id="create-user-form"
                    autocomplete="off"
                    :schema="createUserFormSchema"
                    :state="createUserFormState"
                    :validate-on="['change', 'input']"
                    @submit="createUser"
                >
                    <UFormField label="Name" name="name" required>
                        <UInput
                            v-model="createUserFormState.name"
                            autocomplete="off"
                            size="lg"
                            variant="subtle"
                            class="w-full"
                            autofocus
                            required
                        />
                    </UFormField>

                    <UFormField class="mt-4" label="E-mail" name="email" required>
                        <UInput
                            v-model="createUserFormState.email"
                            autocomplete="off"
                            size="lg"
                            variant="subtle"
                            class="w-full"
                            required
                        />
                    </UFormField>

                    <UFormField class="mt-4" label="Password" name="password" required>
                        <UInput
                            v-model="createUserFormState.password"
                            autocomplete="new-password"
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
                            v-model="createUserFormState.passwordConfirmation"
                            autocomplete="off"
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

                    <UFormField class="mt-4" label="User role" name="isAdmin" required>
                        <USwitch
                            v-model="createUserFormState.isAdmin"
                            label="Admin"
                            :disabled="firstUser"
                        />
                    </UFormField>
                </UForm>

                <FormResponseErrorAlert
                    class="mt-4"
                    :title="'Error'"
                    :error-messages="createUserErrors"
                />
            </div>

            <!-- Saving -->
            <div v-if="loading" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">Creating...</div>
                <UIcon name="i-lucide-loader-circle" class="size-12 animate-spin text-primary" />
            </div>

            <!-- Updated -->
            <div v-if="finished" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">User created</div>
                <UIcon name="i-lucide-circle-check" class="size-12 text-success" />
            </div>
        </template>
        <template #footer>
            <div class="w-full mt-4 flex justify-end">
                <UButton
                    class="justify-center font-normal mr-2"
                    :label="finished ? 'Close' : 'Cancel'"
                    variant="link"
                    color="neutral"
                    type="button"
                    @click="modalOpened = false"
                    :disabled="loading"
                />

                <UButton
                    v-if="!finished"
                    class="justify-center font-normal"
                    label="Create user"
                    type="submit"
                    form="create-user-form"
                    loading-icon="i-lucide-loader-circle"
                    :lading="loading"
                    :disabled="loading"
                />
            </div>
        </template>
    </UModal>
</template>
