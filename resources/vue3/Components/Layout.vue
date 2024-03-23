<template>
    <div id="layout" class="h-screen w-full flex justify-stretch bg-surface-50">
        <div id="sidebar" class="justify-self-start w-[400px]">
            sidebar
        </div>

        <div id="content" class="grow">
            <card class="w-[94%] mx-auto mt-8">
                <template #title> Simple Card </template>
                <template #content>
                    <div 
                        v-for="index in 100"
                        :key="index"
                        class="inline-block px-1 py-1 select-none hover:bg-green-200 rounded-md"
                        @click="toggle"
                    >
                        test
                    </div>
                    <popover ref="op">
                        <div class="p-2">test</div>
                    </popover>
                </template>
            </card>
            
        </div>
    </div>
</template>

<script>
export default {
    data: () => {
        return {
            selectedCity: null,
            cities: [
                { name: 'New York', code: 'NY' },
                { name: 'Rome', code: 'RM' },
                { name: 'London', code: 'LDN' },
                { name: 'Istanbul', code: 'IST' },
                { name: 'Paris', code: 'PRS' }
            ],
        }
    },
    methods: {
        toggle(event){
            this.$refs.op.hide();
            this.$nextTick(() => {
                this.$refs.op.show(event);
            });
        }
    }
}
</script>
