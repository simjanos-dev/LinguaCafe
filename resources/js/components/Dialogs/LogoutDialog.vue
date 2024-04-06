<template>
    <v-dialog v-model="value" persistent max-width="500px" height="300px" :loading="loading">
        <v-card class="rounded-lg">
            <v-card-title>
                <v-icon class="mr-2">mdi-logout</v-icon>
                <span class="text-h5">Logout</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-4 pb-6">
                Are you sure you want to logout?
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>
                <v-btn rounded text @click="logout"><v-icon class="mr-1">mdi-logout</v-icon>Logout</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
        },
        emits: ['input'],
        data: function() {
            return {
                loading: false,
            };
        },
        mounted: function() {
        },
        methods: {
            logout() {
                this.loading = true;
                axios.post('/logout').then((response) => {
                    window.location.href = "/";
                })
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
