<template>
    <v-dialog v-model="value" persistent max-width="300px">
        <v-card class="rounded-lg">
            <v-card-title>
                <span class="text-h5">Theme</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text>
                <v-list nav rounded>
                    <v-list-item-group color="primary" v-model="selectedTheme">
                        <v-list-item class="my-1" v-for="(theme, index) in displayNames" :key="index" :value="index" @click="selectTheme(index)">
                            <v-list-item-avatar tile min-width="60">
                                <v-icon>{{ theme.icon }}</v-icon>
                            </v-list-item-avatar>
                            <v-list-item-content>{{ theme.name }}</v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    import themes from './../../themes';
    import ThemeService from './../../services/ThemeService';
    import { DefaultLocalStorageManager } from './../../services/LocalStorageManagerService'
    export default {
        props: {
            value : Boolean,
        },
        emits: ['input'],
        data: function() {
            return {
                selectedTheme: ThemeService.getCurrentTheme(),
                displayNames: {
                    auto: {
                        name: 'Auto',
                        icon: 'mdi-theme-light-dark'
                    },
                    light: {
                        name: 'Light theme',
                        icon: 'mdi-weather-sunny'
                    },
                    dark: {
                        name: 'Dark theme',
                        icon: 'mdi-weather-night'
                    },
                    eink: {
                        name: 'Eink theme',
                        icon: 'mdi-tablet'
                    }
                },
            };
        },
        mounted: function() {
            console.log(this.$vuetify.theme.themes);
        },
        methods: {
            selectTheme: function(newTheme) {
                // switch to user's system theme if 'auto' is selected
                if (newTheme === 'auto') {
                    DefaultLocalStorageManager.saveSetting('theme-auto', true);
                    newTheme = ThemeService.getAutoTheme()
                } else {
                    DefaultLocalStorageManager.saveSetting('theme-auto', false);
                }

                DefaultLocalStorageManager.saveSetting('theme', newTheme);
                this.$vuetify.theme.dark = (newTheme === 'dark');
                ThemeService.loadTheme(this.$vuetify);

                this.close();

            },
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
