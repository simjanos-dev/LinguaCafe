<template>
    <v-container>
        <v-tabs v-model="tab" background-color="white" class="rounded-lg border overflow-hidden">
            <v-tab>Account</v-tab>
            <v-tab>Themes</v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab" id="admin-tab-items" elevation="0" class="rounded-lg mt-4 pa-6">
            <v-tab-item :value="0">
                account
            </v-tab-item>
            <v-tab-item :value="1">
                theme
            </v-tab-item>

            <!-- <v-tab-item :value="0">
                <admin-user-settings></admin-user-settings>
            </v-tab-item>
            <v-tab-item :value="1">
                <admin-dictionary-settings :language="$props.language"></admin-dictionary-settings>
            </v-tab-item>
            <v-tab-item :value="2">
                <admin-api-settings></admin-api-settings>
            </v-tab-item>
            <v-tab-item :value="3">
                <admin-review-settings></admin-review-settings>
            </v-tab-item> -->
        </v-tabs-items>
    </v-container>
</template>

<script>
    export default {
        data: function() {
            return {
                tab: 'account',
                pages: {
                    'account': 0,
                    'themes': 1
                }
            }
        },
        props: {
            language: String
        },
        mounted() {
            if (this.$route.params.currentPage !== undefined) {
                this.tab = this.pages[this.$route.params.currentPage];
            }
        },
        methods: {
        }
    }
</script>
