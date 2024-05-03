<template>
    <v-container>
        <v-tabs v-model="tab" background-color="white" class="rounded-lg border overflow-hidden">
            <v-tab>Account</v-tab>
            <v-tab>Themes</v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab" id="admin-tab-items" elevation="0" class="no-background rounded-lg mt-4 pa-6">
            <v-tab-item :value="0">
                Nothing is here yet.
            </v-tab-item>
            <v-tab-item :value="1">
                <user-settings-themes />
            </v-tab-item>
        </v-tabs-items>
    </v-container>
</template>

<script>
    export default {
        data: function() {
            return {
                tab: 1,
            }
        },
        props: {
            language: String
        },
        mounted() {
        },
        methods: {
        }
    }
</script>
