<template>
    <div id="admin-language-settings">
        <!-- Install language dialog -->
        <admin-install-language-dialog 
            v-model="installLanguageDialog.active"
            :language="installLanguageDialog.language"
            @language-installed="loadLanguages"
        />

        <!-- Uninstall languages dialog -->
        <admin-uninstall-languages-dialog 
            v-model="uninstallLanguagesDialog"
        />

        <!-- Title subheader -->
        <div class="d-flex subheader mt-4 mb-4 px-2 ">
            Installable languages
            <v-spacer />
            <v-btn rounded depressed color="error" @click="uninstallLanguages">
                <v-icon class="mr-1">mdi-delete</v-icon>
                Uninstall all
            </v-btn>
        </div>

        <!-- Language list -->
        <v-simple-table class="no-hover border rounded-lg" v-if="languages.length">
            <thead>
                <tr>
                    <th class="text-center" colspan="2">Language</th>
                    <th class="text-center">Install</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(language, languageIndex) in languages" :key="languageIndex">
                    <!-- Flag -->
                    <td>
                        <v-img 
                            eager
                            class="border my-2" 
                            :src="'/images/flags/' + language.name.toLowerCase() + '.png'" 
                            max-width="43" 
                            height="28"
                        ></v-img> 
                    </td>
                    
                    <!-- Language -->
                    <td>
                        {{ language.name }}
                    </td>

                    <!-- Install -->
                    <td class="text-center">
                        <!-- Already installed icon -->
                        <v-icon color="success" v-if="language.installed">
                            mdi-check-circle
                        </v-icon>
                        
                        <!-- Install button -->
                        <v-btn 
                            v-else
                            rounded 
                            depressed 
                            color="primary" 
                            @click="installLanguage(language.name)"
                        >
                            <v-icon class="mr-1">mdi-download</v-icon>
                            Install
                        </v-btn>
                    </td>
                </tr>
            </tbody>
        </v-simple-table>

        <!-- Skeleton loader language list -->
        <v-simple-table class="no-hover border rounded-lg" v-if="!languages.length">
            <thead>
                <tr>
                    <th class="text-center" colspan="2">Language</th>
                    <th class="text-center">Install</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="skeletonindex in 7" :key="skeletonindex">
                    <!-- Flag skeleton -->
                    <td>
                        <v-skeleton-loader
                            class="rounded"
                            type="card"
                        ></v-skeleton-loader>
                    </td>
                    
                    <!-- Language skeleton-->
                    <td>
                        <v-skeleton-loader
                            class="rounded-pill"
                            type="card"
                        ></v-skeleton-loader>
                    </td>

                    <!-- Install button skeleton-->
                    <td class="text-center">
                        <v-skeleton-loader
                            class="rounded-pill"
                            type="card"
                        ></v-skeleton-loader>
                    </td>
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
                uninstallLanguagesDialog: false,
                installLanguageDialog: {
                    active: false,
                    language: '',
                }
            }
        },
        props: {
        },
        mounted() {
            this.loadLanguages();
        },
        methods: {
            installLanguage(language) {
                this.installLanguageDialog.active = true;
                this.installLanguageDialog.language = language;
            },
            uninstallLanguages() {
                this.uninstallLanguagesDialog = true;
            },
            loadLanguages() {
                this.languages = [];
                axios.get('/languages/get-admin-language-settings-data').then((response) => {
                    for (let i = 0; i < response.data.languages.length; i++) {
                        this.languages.push({
                            name: response.data.languages[i],
                        });

                        // add installed information
                        if (response.data.installedLanguages.includes(response.data.languages[i])) {
                            this.languages[this.languages.length - 1].installed = true;
                        } else {
                            this.languages[this.languages.length - 1].installed = false;
                        }
                    }
                });
            }
        }
    }
</script>
