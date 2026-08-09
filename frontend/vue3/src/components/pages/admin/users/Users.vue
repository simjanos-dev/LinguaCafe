<script setup lang="ts">
import UserService from '@services/users/UserService'
import { onMounted, ref } from 'vue'
import moment from 'moment'

import type { User } from '@lctypes/User'
import type { TableColumn } from '@nuxt/ui'
import CreateUserPopup from '@components/popups/users/CreateUserPopup.vue'

const userService = new UserService()

const createUserPopup = ref<boolean>(false)
const editUserPopup = ref<boolean>(false)
const editedUser = ref<null | User>(null)

// table
const search = ref<string>('')
const loading = ref<boolean>(false)
const users = ref<null | User[]>(null)

const columns: TableColumn<User>[] = [
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => row.getValue('name'),
        meta: {
            class: {
                th: 'text-center',
            },
        },
    },
    {
        accessorKey: 'email',
        header: 'E-mail',
        cell: ({ row }) => row.getValue('email'),
        meta: {
            class: {
                th: 'text-center',
            },
        },
    },
    {
        accessorKey: 'is_admin',
        header: 'Admin',
        meta: {
            class: {
                th: 'text-center',
                td: 'text-center',
            },
        },
    },
    {
        accessorKey: 'created_at',
        header: 'Created at',
        cell: ({ row }) => {
            return moment(row.getValue('created_at')).format('YYYY-MM-DD HH:mm:ss')
        },
        meta: {
            class: {
                th: 'text-center',
                td: 'text-center',
            },
        },
    },
    {
        id: 'actions',
        header: 'Actions',
        meta: {
            class: {
                th: 'text-center',
                td: 'text-center flex justify-center',
            },
        },
    },
]

const loadUsers = async () => {
    loading.value = true
    let response = await userService.getUsers()
    loading.value = false

    if (response.ok && response.data) {
        users.value = response.data
    }
}

const openEditUserPopup = (user: User) => {
    editedUser.value = user
    editUserPopup.value = true
}

onMounted(() => {
    loadUsers()
})
</script>

<template>
    <div class="mt-4">
        <CreateUserPopup v-model="createUserPopup" @user-created="loadUsers()" />
        <EditUserPopup v-model="editUserPopup" :user="editedUser" @user-updated="loadUsers()" />

        <div class="w-full flex flex-wrap bg-elevated/50 rounded-lg p-4 my-4">
            <div class="w-full flex justify-end">
                <UButton
                    class="ml-1"
                    size="md"
                    color="primary"
                    @click="createUserPopup = true"
                    icon="i-lucide-user-plus"
                    :disabled="loading"
                    >Create</UButton
                >
            </div>
            <div class="w-full">
                <UInput
                    v-model="search"
                    class="w-full my-4"
                    variant="subtle"
                    placeholder="Search..."
                    icon="i-lucide-search"
                />
            </div>
            <div class="w-full mt-4">
                <UTable
                    :data="users ?? []"
                    :columns="columns"
                    class="flex-1"
                    :loading="loading"
                    v-model:global-filter="search"
                >
                    <!-- Actions -->
                    <template #actions-cell="{ row }">
                        <div class="w-full mx-auto">
                            <UButton
                                class="ml-1"
                                :icon="loading ? '' : 'i-lucide-pen'"
                                size="md"
                                color="primary"
                                variant="ghost"
                                :disabled="loading"
                                @click="openEditUserPopup(row.original)"
                            />
                        </div>
                    </template>

                    <!-- Admin -->
                    <template #is_admin-cell="{ row }">
                        <div class="w-full mx-auto flex justify-center">
                            <UIcon
                                v-if="row.original.is_admin"
                                name="i-lucide-shield-check"
                                class="size-6 text-amber-500"
                            />
                        </div>
                    </template>
                </UTable>
            </div>
        </div>
    </div>
</template>
