<script setup lang="ts">
import LanguageService from '@services/languages/LanguageService'
import { onMounted, ref } from 'vue'
import { toUpperCase } from '@src/helpers/StringHelper'

import type { Language } from '@lctypes/Language'
import type { TableColumn } from '@nuxt/ui'

const languageService = new LanguageService()

// install popup
const uninstallLanguagesPopup = ref<boolean>(false)
const installLanguagePopup = ref<boolean>(false)
const selectedLanguage = ref<null | Language>(null)

// table
const search = ref<string>('')
const loading = ref<boolean>(false)
const languages = ref<null | Language[]>(null)
const installedLanguages = ref<null | Language[]>(null)

const columns: TableColumn<Language>[] = [
    {
        accessorKey: 'flag',
        header: 'Flag',
        meta: {
            class: {
                th: 'text-center',
                td: 'flex justify-center text-center',
            },
        },
    },
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => toUpperCase(row.getValue('name'), true),
        meta: {
            class: {
                th: 'text-center',
            },
        },
    },
    {
        id: 'actions',
        header: 'Install',
        meta: {
            class: {
                th: 'text-center',
                td: 'text-center flex justify-center',
            },
        },
    },
]

const loadLanguages = async () => {
    loading.value = true
    let response = await languageService.getInstallRequiredLanguages()
    loading.value = false

    if (response.ok && response.data) {
        languages.value = response.data
    }
}

const loadInstalledLanguages = async () => {
    loading.value = true
    let response = await languageService.getInstalledLanguages()
    loading.value = false

    if (response.ok && response.data) {
        installedLanguages.value = response.data
    }
}

const openInstallLanguagePopup = (language: Language) => {
    installLanguagePopup.value = true
    selectedLanguage.value = language
}

const languageInstalled = (language: Language) => {
    if (!installedLanguages.value) {
        return false
    }

    return installedLanguages.value.some(item => item.name === language.name)
}

const reloadLanguages = () => {
    loadLanguages()
    loadInstalledLanguages()
}

onMounted(() => {
    reloadLanguages()
})
</script>

<template>
    <div class="mt-4">
        <UninstallEveryLanguagePopup
            v-model="uninstallLanguagesPopup"
            @uninstalled="reloadLanguages"
        />

        <InstallLanguagePopup
            v-if="selectedLanguage"
            v-model="installLanguagePopup"
            :language="selectedLanguage"
            @installed="reloadLanguages"
        />

        <div class="w-full flex flex-wrap bg-elevated/50 rounded-lg p-4 my-4">
            <div class="w-full flex justify-end">
                <UButton
                    class="ml-1"
                    size="md"
                    color="primary"
                    icon="i-lucide-trash"
                    :disabled="loading"
                    @click="uninstallLanguagesPopup = true"
                    >Uninstall all</UButton
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
                    :data="languages ?? []"
                    :columns="columns"
                    class="flex-1"
                    :loading="loading"
                    v-model:global-filter="search"
                >
                    <!-- flag -->
                    <template #flag-cell="{ row }">
                        <Flag :language="row.original" />
                    </template>

                    <!-- Actions -->
                    <template #actions-cell="{ row }">
                        <div class="w-full mx-auto flex justify-center">
                            <UButton
                                v-if="!languageInstalled(row.original)"
                                class="ml-1"
                                :icon="'i-lucide-hard-drive-download'"
                                size="md"
                                color="primary"
                                :disabled="loading"
                                @click="openInstallLanguagePopup(row.original)"
                                >Install</UButton
                            >

                            <UIcon
                                v-else
                                name="i-lucide-circle-check"
                                class="text-success size-7"
                            />
                        </div>
                    </template>
                </UTable>
            </div>
        </div>
    </div>
</template>
