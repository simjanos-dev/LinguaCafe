<template>
    <v-card outline id="admin-font-type-settings" class="rounded-lg">
        font type component
    </v-card>
</template>

<script>
    export default {
        data: function() {
            return {
                fonts: [],
            }
        },
        props: {
        },
        mounted() {
        },
        methods: {
            loadFonts() {
                this.fonts = [];
                // axios.get('/fonts/get').then((response) => {
                //     this.fonts = response.data;
                // });
            }
        }
    }
</script>
