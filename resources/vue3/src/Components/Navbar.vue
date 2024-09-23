<script>
export default {
    props: {
        darkMode: Boolean,
    },
    data() {
        return {
            visible: true,
            collapsed: false,
        };
    },
};
</script>

<template>
    <div :class="['card', 'flex', 'justify-center']">
        <Drawer
            v-model:visible="visible"
            :dismissable="false"
            :modal="false"
            :show-close-icon="false"
            :class="[
                'overflow-hidden',
                collapsed ? 'w-[100px]' : 'w-[300px]',
                $props.darkMode ? 'dark' : '',
            ]"
            @click="collapsed = !collapsed"
        >
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
            eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim
            ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut
            aliquip ex ea commodo consequat.
        </Drawer>
    </div>
</template>
