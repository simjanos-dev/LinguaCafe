<script>
export default {
    data() {
        return {
            darkMode: true,
        };
    },
};
</script>

<template>
    <div
        :class="[
            'w-full',
            'h-full',
            'dark:bg-surface-900',
            darkMode ? 'dark' : '',
        ]"
    >
        <Navbar :dark-mode="darkMode" />
    </div>
</template>
