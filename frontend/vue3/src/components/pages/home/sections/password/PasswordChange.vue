<script setup lang="ts">
import { ref } from 'vue'
import PasswordChangePopup from '@components/popups/users/PasswordChangePopup.vue'

const showPasswordChangePopup = ref<boolean>(false)
</script>

<template>
    <div>
        <PasswordChangePopup v-if="showPasswordChangePopup" v-model="showPasswordChangePopup" />

        <UCard class="ring-error">
            <div class="flex items-center">
                <UIcon name="i-lucide-triangle-alert" class="mr-2 text-error size-10" />
                <span class="text-base">
                    Your account and password was created by an admin. Please change your password
                    before continuing your language learning journey.
                </span>
            </div>

            <div class="w-full flex justify-end mt-8">
                <UButton
                    label="Change password"
                    color="error"
                    @click="showPasswordChangePopup = true"
                />
            </div>
        </UCard>
    </div>
</template>
