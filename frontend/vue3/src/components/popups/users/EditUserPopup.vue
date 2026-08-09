<script setup lang="ts">
import { ref, computed, reactive, watch } from 'vue'
import * as zod from 'zod'
import UserService from '@services/users/UserService'

import type { FormError } from '@nuxt/ui'
import type { User } from '@lctypes/User'

const userService = new UserService()

// modal dialog
type Props = {
    modelValue: boolean
    firstUser?: boolean
    user: null | User
}

const { modelValue, firstUser = false, user } = defineProps<Props>()
const modalOpened = computed({
    get: () => modelValue,
    set: value => emit('update:modelValue', value),
})

const emit = defineEmits(['update:modelValue', 'user-updated'])

// form
const loading = ref<boolean>(false)
const finished = ref<boolean>(false)
const updateUserErrors = ref<FormError[]>([])
const updateUserFormSchema = zod.object({
    name: zod
        .string('Name is required')
        .min(4, 'Name must be at least 4 characters')
        .max(255, 'Name must be below 255 characters'),
    email: zod.email('Invalid email'),
    isAdmin: zod.boolean(),
})

type Schema = zod.output<typeof updateUserFormSchema>

const updateUserFormState = reactive<Partial<Schema>>({
    name: undefined,
    email: undefined,
    isAdmin: true,
})

const updateUser = async function () {
    if (
        !user ||
        !updateUserFormState.name ||
        !updateUserFormState.email ||
        updateUserFormState.isAdmin === undefined
    ) {
        return
    }

    updateUserErrors.value = []
    loading.value = true
    finished.value = false

    const updateUserResponse = await userService.updateUser(
        user.id,
        updateUserFormState.name,
        updateUserFormState.email,
        updateUserFormState.isAdmin
    )

    loading.value = false

    if (!updateUserResponse.ok && updateUserResponse.errorMessages) {
        updateUserErrors.value = updateUserResponse.errorMessages
    }
    if (updateUserResponse.ok) {
        finished.value = true
        emit('user-updated')
    }
}

watch(
    () => modelValue,
    value => {
        if (!value) {
            return
        }

        updateUserFormState.name = user?.name ?? undefined
        updateUserFormState.email = user?.email ?? undefined
        updateUserFormState.isAdmin = user?.is_admin ?? false
        loading.value = false
        finished.value = false
        updateUserErrors.value = []
    }
)
</script>

<template>
    <UModal class="w-96" title="Edit user" v-model:open="modalOpened">
        <template #body>
            <div class="p-4" v-if="!loading && !finished">
                <UForm
                    id="update-user-form"
                    autocomplete="off"
                    :schema="updateUserFormSchema"
                    :state="updateUserFormState"
                    :validate-on="['change', 'input']"
                    @submit="updateUser"
                >
                    <UFormField label="Name" name="name" required>
                        <UInput
                            v-model="updateUserFormState.name"
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
                            v-model="updateUserFormState.email"
                            autocomplete="off"
                            size="lg"
                            variant="subtle"
                            class="w-full"
                            required
                        />
                    </UFormField>

                    <UFormField class="mt-4" label="User role" name="isAdmin" required>
                        <USwitch
                            v-model="updateUserFormState.isAdmin"
                            label="Admin"
                            :disabled="firstUser"
                        />
                    </UFormField>
                </UForm>
                <FormResponseErrorAlert
                    class="mt-4"
                    :title="'Error'"
                    :error-messages="updateUserErrors"
                />
            </div>

            <!-- Saving -->
            <div v-if="loading" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">Updating...</div>
                <UIcon name="i-lucide-loader-circle" class="size-12 animate-spin text-primary" />
            </div>

            <!-- Updated -->
            <div v-if="finished" class="flex flex-wrap justify-center">
                <div class="w-full flex justify-center mb-4">User updated</div>
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
                    label="Update user"
                    type="submit"
                    form="update-user-form"
                    loading-icon="i-lucide-loader-circle"
                    :loading="loading"
                />
            </div>
        </template>
    </UModal>
</template>
