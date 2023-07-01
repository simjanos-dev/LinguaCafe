<template>
    <v-dialog v-model="value" persistent max-width="300px">
        <v-card class="rounded-lg">
            <v-card-title>
                <span class="text-h5">Theme</span>
            </v-card-title>
            <v-card-text>
                <v-list nav rounded>
                    <v-list-item-group color="primary" v-model="selectedTheme">
                        <v-list-item class="my-1" v-for="(theme, index) in displayNames" :key="index" :value="index" @click="selectTheme(index)">
                            <v-list-item-content>
                                <v-list-item-title class="text-center">{{ theme }}</v-list-item-title>
                            </v-list-item-content>
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
    export default {
        props: {
            value : Boolean,
        },
        emits: ['input'],
        data: function() {
            return {
                selectedTheme: this.$cookie.get('theme') === null ? 'light' : this.$cookie.get('theme'),
                displayNames: {
                    light: 'Light theme',
                    dark: 'Dark theme',
                    eink: 'Eink theme',
                },
            };
        },
        mounted: function() {
            console.log(this.themes);
        },
        methods: {
            selectTheme: function(newTheme) {
                this.$cookie.set('theme', newTheme, 3650);
                this.$vuetify.theme.themes['light'] = themes[newTheme];
                this.$vuetify.theme.dark = (newTheme == 'dark');

                this.close();
                
            },
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
