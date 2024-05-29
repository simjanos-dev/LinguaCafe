<template>
    <v-container v-if="isAdmin">
        <v-tabs v-model="tab" background-color="foreground" class="rounded-lg border overflow-hidden" @change="tabChanged">
            <v-tab>Users</v-tab>
            <v-tab>Languages</v-tab>
            <v-tab>Dictionaries</v-tab>
            <v-tab>Fonts</v-tab>
            <v-tab>API</v-tab>
            <v-tab>Reviews</v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab" id="admin-tab-items" elevation="0" class="no-background rounded-lg mt-4 pa-6">
            <v-tab-item :value="0">
                <admin-user-settings></admin-user-settings>
            </v-tab-item>
            <v-tab-item :value="1">
                <admin-language-settings></admin-language-settings>
            </v-tab-item>
            <v-tab-item :value="2">
                <admin-dictionary-settings :language="$props.language"></admin-dictionary-settings>
            </v-tab-item>
            <v-tab-item :value="3">
                <admin-font-type-settings></admin-font-type-settings>
            </v-tab-item>
            <v-tab-item :value="4">
                <admin-api-settings></admin-api-settings>
            </v-tab-item>
            <v-tab-item :value="5">
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
                tabIndexes: {
                    'users': 0,
                    'languages': 1,
                    'dictionaries': 2,
                    'font-types': 3,
                    'api': 4,
                    'reviews': 5,
                },
                tabUrls: [
                    'users',
                    'languages',
                    'dictionaries',
                    'font-types',
                    'api',
                    'reviews',
                ]
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
            if (this.$route.params.page !== undefined) {
                this.tab = this.tabIndexes[this.$route.params.page];
            } 
        },
        methods: {
            tabChanged(event) {
                var page = this.tabUrls[event];

                if (this.$router.currentRoute.fullPath !== '/admin/' + page) {
                    this.$router.push('/admin/' + page);
                }
            }
        }
    }
</script>
