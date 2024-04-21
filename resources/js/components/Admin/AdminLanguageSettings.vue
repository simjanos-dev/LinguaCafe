<template>
    <div id="admin-language-settings">
        <!-- User list -->
        <v-simple-table class="no-hover border rounded-lg">
            <thead>
                <tr>
                    <th class="text-center">Flag</th>
                    <th class="text-center">Language</th>
                    <th class="text-center">Installed</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(language, languageIndex) in languages">
                    <td class="text-center">Flag</td>
                    <td class="text-center">Language</td>
                    <td class="text-center">Installed</td>
                    <td class="text-center">Actions</td>
                </tr>
            </tbody>
        </v-simple-table>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                languages: [],
            }
        },
        props: {
        },
        mounted() {
            this.loadLanguages();
        },
        methods: {
            loadLanguages() {
                this.languages = [];
                // axios.get('/lan/get').then((response) => {
                //     this.languages = response.data;
                // });
            }
        }
    }
</script>
