<script setup lang="ts">
import { ref, reactive } from 'vue'
import * as zod from 'zod'
import UserService from '@src/services/users/AuthService'
import FormResponseErrorAlert from '@components/custom/FormResponseErrorAlert.vue'
import CreateFirstUserBox from '@components/pages/auth/CreateFirstUserBox.vue'

import type { ApiCallResult } from '@src/types/apicall/ApiCallResult'
import type { User } from '@lctypes/User'
import type { FormError } from '@nuxt/ui'

const userService = new UserService()
const loginErrors = ref<FormError[]>([])
const loginFormSchema = zod.object({
    email: zod.email('Invalid email'),
    password: zod
        .string('Password is required')
        .min(8, 'Must be between 8 and 32 characters.')
        .max(32, 'Must be between 8 and 32 characters.'),
    remember: zod.boolean(),
})

type Schema = zod.output<typeof loginFormSchema>

const loginFormState = reactive<Partial<Schema>>({
    email: undefined,
    password: undefined,
    remember: true,
})

const login = async function () {
    loginErrors.value = []

    const loginResponse: ApiCallResult<User> = await userService.login(
        loginFormState.email,
        loginFormState.password,
        loginFormState.remember
    )

    if (!loginResponse.ok && loginResponse.errorMessages) {
        loginErrors.value = loginResponse.errorMessages
    }
}

const showPassword = ref(false)
</script>

<template>
    <div class="flex justify-center">
        <UForm
            :schema="loginFormSchema"
            :state="loginFormState"
            :validate-on="['change', 'input']"
            @submit="login"
        >
            <UCard variant="subtle" class="w-[400px]">
                <template #header>
                    <div class="flex flex-wrap justify-center items-center text-xl">
                        <UIcon name="i-lucide-user" class="mr-2" /> Login
                    </div></template
                >

                <CreateFirstUserBox />

                <UFormField label="Email" name="email" required>
                    <UInput
                        v-model="loginFormState.email"
                        size="lg"
                        variant="subtle"
                        class="w-full"
                        autofocus
                        required
                    />
                </UFormField>

                <UFormField class="mt-4" label="Password" name="password" required>
                    <UInput
                        v-model="loginFormState.password"
                        size="lg"
                        variant="subtle"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full"
                    >
                        <template #trailing>
                            <UTooltip :text="showPassword ? 'Hide password' : 'Show password'">
                                <UButton
                                    color="neutral"
                                    variant="link"
                                    size="sm"
                                    :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    :aria-pressed="showPassword"
                                    aria-controls="password"
                                    @click="showPassword = !showPassword"
                                />
                            </UTooltip>
                        </template>
                    </UInput>
                </UFormField>

                <UFormField class="mt-4" name="remember">
                    <UCheckbox v-model="loginFormState.remember" label="Remember me"
                /></UFormField>

                <FormResponseErrorAlert
                    class="mt-4"
                    :title="'Login error'"
                    :error-messages="loginErrors"
                />

                <template #footer>
                    <UButton class="w-full justify-center font-normal" type="submit" loading-auto>
                        Login
                    </UButton>
                </template>
            </UCard>
        </UForm>
    </div>
</template>
