<script setup lang="ts">
import type { Language } from '@lctypes/Language'

type Props = {
    language: Language
}

const { language } = defineProps<Props>()
</script>

<template>
    <img :src="`/images/flags/${language.name}.png`" class="w-10 h-[26px] rounded-xs object-fill" />
</template>
