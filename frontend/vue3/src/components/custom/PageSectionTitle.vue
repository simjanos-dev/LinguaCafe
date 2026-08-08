<script setup lang="ts">
type Props = {
    title?: string
}

const { title } = defineProps<Props>()
</script>

<template>
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-2">
        <slot name="left"
            ><h1 class="text-pretty font-bold text-highlighted text-xl sm:text-xl">
                {{ title }}
            </h1></slot
        >
        <slot name="right"></slot>
    </div>
</template>
