<template>
    <v-container v-if="isAdmin">
        <v-tabs v-model="tab" background-color="white" class="rounded-lg border overflow-hidden">
            <v-tab>Users</v-tab>
            <v-tab>Dictionaries</v-tab>
            <v-tab>API</v-tab>
            <v-tab>Reviews</v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab" id="admin-tab-items" elevation="0" class="rounded-lg mt-4 pa-6">
            <v-tab-item :value="0">
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
            </v-tab-item>
        </v-tabs-items>
    </v-container>
    <v-container v-else-if="!loading">
        You do not have permission to access this section.
    </v-container>
</template>

<script>
    export default {
        data: function() {
            return {
                tab: 0,
                isAdmin: false,
                loading: true,
            }
        },
        props: {
            language: String
        },
        beforeMount() {
            axios.get('/users/is-admin').then((response) => {
                this.isAdmin = response.data;
                this.loading = false;
            });
        },
        mounted() {
        },
        methods: {
        }
    }
</script>
