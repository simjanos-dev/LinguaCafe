<script setup lang="ts">
// todo: refactore import store -> import Store
import store from '@store/Store'

type Props = {
    collapsed: boolean
}

const { collapsed } = defineProps<Props>()
const emit = defineEmits(['toggle-sidebar-collapse', 'logout'])
</script>

<template>
    <div class="item-end mt-auto mb-4" :class="collapsed ? 'px-1' : 'px-3'">
        <div class="relative w-full flex flex-wrap justify-between items-center">
            <UDropdownMenu
                size="lg"
                color="neutral"
                :ui="{
                    content: 'w-64',
                }"
                :items="[
                    {
                        label: 'Select language',
                        icon: 'i-lucide-languages',
                    },
                    {
                        label: 'Select theme',
                        icon: 'i-lucide-palette',
                    },
                    {
                        label: 'Logout',
                        icon: 'i-lucide-log-out',
                        kbds: ['s-l'],
                        onSelect: () => {
                            emit('logout')
                        },
                    },
                ]"
            >
                <div
                    class="flex justify-between items-center select-none cursor-pointer hover:bg-muted rounded-lg"
                    :class="[collapsed ? 'p-3 mx-auto' : 'p-2 flex-grow']"
                >
                    <UUser
                        :name="store.user?.name"
                        :description="store.user?.selected_language.name"
                        :avatar="{
                            src: `/images/flags/${store.user?.selected_language.name}.png`,
                        }"
                        v-if="!collapsed"
                    />

                    <UAvatar
                        v-else
                        :src="`/images/flags/${store.user?.selected_language.name}.png`"
                        size="xs"
                    />

                    <UIcon v-if="!collapsed" name="i-lucide-ellipsis-vertical" class="size-5" />
                </div>
            </UDropdownMenu>

            <div
                class="select-none cursor-pointer hover:bg-muted rounded-lg p-3"
                :class="collapsed ? 'mx-auto' : ''"
                @click="emit('toggle-sidebar-collapse')"
            >
                <UIcon v-if="!collapsed" name="i-lucide-square-arrow-left" class="size-6" />

                <UIcon v-if="collapsed" name="i-lucide-square-arrow-right" class="size-6" />
            </div>
        </div>
    </div>
</template>
