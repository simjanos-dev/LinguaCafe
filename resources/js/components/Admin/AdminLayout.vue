<template>
    <v-container>
        <v-tabs v-model="tab" background-color="white" class="rounded-lg border overflow-hidden">
            <v-tab>Users</v-tab>
            <v-tab>Dictionaries</v-tab>
            <v-tab>Jellyfin API</v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab" id="admin-tab-items" class="rounded-lg border mt-4 pa-6">
            <v-tab-item :value="0">
                <div class="subheader">Users</div>
            </v-tab-item>
            <v-tab-item :value="1">
                <admin-dictionary-settings :language="$props.language"></admin-dictionary-settings>
            </v-tab-item>
            <v-tab-item :value="2">
                <div class="subheader">Jellyfin</div>
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
