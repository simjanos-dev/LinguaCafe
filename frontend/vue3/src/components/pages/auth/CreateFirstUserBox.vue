<script setup lang="ts">
import { ref } from 'vue'
import store from '@store/Store'

const showCreateUserPopup = ref(false)
</script>

<template>
    <div v-if="!store.hasUser">
        <CreateUserPopup v-model="showCreateUserPopup" :first-user="true" />
        <UAlert variant="subtle" color="neutral" icon="i-lucide-user-round-plus" class="mb-4">
            <template #title>First user</template>
            <template #description>
                It seems like it is your first time using LinguaCafe after installing it, and you
                haven't created a user yet.
            </template>
            <template #actions>
                <div class="w-full flex justify-end">
                    <UButton
                        color="primary"
                        size="sm"
                        label="Create first user"
                        @click="showCreateUserPopup = true"
                    />
                </div>
            </template>
        </UAlert>
    </div>
</template>
