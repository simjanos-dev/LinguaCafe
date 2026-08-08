<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Store from '@src/store/Store'

const passwordChangePopup = ref<boolean>(false)

onMounted(() => {})
</script>

<template>
    <div class="mt-4">
        <PasswordChangePopup v-model="passwordChangePopup" />
        <PageSectionTitle title="Account" />

        <div class="flex flex-wrap items-center bg-elevated/50 rounded-lg p-4 my-4">
            <div class="w-full flex flex-wrap">
                <div class="w-1/2 flex flex-col">
                    <div class="font-bold">Username:</div>
                    <div>{{ Store.user?.name }}</div>
                </div>
                <div class="w-1/2 flex flex-col">
                    <div class="font-bold">E-mail:</div>
                    <div>{{ Store.user?.name }}</div>
                </div>
            </div>
            <div class="w-full flex justify-end">
                <UButton
                    class="justify-center font-normal mt-4"
                    label="Change password"
                    color="primary"
                    type="submit"
                    @click="passwordChangePopup = true"
                />
            </div>
        </div>
    </div>
</template>
