<template>
   <v-app>
        <theme-selection-dialog v-model="themeSelectionDialog" @change="updateTheme"></theme-selection-dialog>
        <v-navigation-drawer id="navigation-drawer" :class="{'eink': theme == 'eink'}" floating :mini-variant="$vuetify.breakpoint.md" app :permanent="$vuetify.breakpoint.mdAndUp" v-model="drawer" color="navigation">
            <div id="logo" class="my-8"><v-icon>mdi-coffee</v-icon> Lingua Cafe</div>
            <v-list nav rounded>
                <v-list-item-group color="primaryDark">
                    <v-list-item class="navigation-button" v-for="(item, index) in navigation" :key="index"  :to="item.url">
                        <v-icon> {{ item.icon }} </v-icon>
                        <span class="pl-6"> {{ item.name }} </span>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
            <template v-slot:append>
                <v-btn id="theme" rounded text class="ma-2" @click="themeSelectionDialog = true">
                    <v-icon>mdi-palette</v-icon>
                    <span class="pl-6">Theme</span>
                </v-btn>
                <v-btn id="language" rounded text class="ma-2">
                    <v-img :src="'/images/flags/japanese'" width="20"></v-img> 
                    <span class="pl-6">Japanese</span>
                </v-btn>
            </template>
        </v-navigation-drawer>

        <v-main :style="{background: $vuetify.theme.currentTheme.background}" :class="{ eink: theme == 'eink'}">
            <router-view :key="$route.fullPath"></router-view>
        </v-main>
        
        <v-bottom-navigation dense grow shift class="d-flex d-sm-flex d-md-none" dark background-color="primary">
            <v-btn class="text-decoration-none" width="60" style="float: left;" @click="drawer = true;">
                <span>More</span>
                <v-icon>mdi-menu</v-icon>
            </v-btn><v-spacer></v-spacer>
            <v-btn 
                class="text-decoration-none"
                grow 
                v-for="(item, index) in navigation"
                :key="index"
                :to="item.url"
                v-if="item.bottomNav"
            >
                <span>{{ item.name }}</span>
                <v-icon>{{ item.icon }}</v-icon>
            </v-btn>
        </v-bottom-navigation>
    </v-app>
</template>

<script>
    import themes from './../themes';
    export default {
        data: function() {
            return {
                theme: (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme'),
                themeSelectionDialog: false,
                drawer: false,
                navbarVisible: true,
                navigation: [
                    {
                        name: 'Home',
                        url: '/',
                        icon: 'mdi-home',
                        bottomNav: true,
                    },
                    {
                        name: 'Library',
                        url: '/books',
                        icon: 'mdi-bookshelf',
                        bottomNav: true,
                    },
                    {
                        name: 'Vocabulary',
                        url: '/vocabulary/search',
                        icon: 'mdi-translate',
                        bottomNav: true,
                    },
                    {
                        name: 'Kanji',
                        url: '/kanji/search',
                        icon: 'mdi-ideogram-cjk',
                        bottomNav: false,
                    },
                    {
                        name: 'Review',
                        url: '/review/false/-1/-1',
                        icon: 'mdi-playlist-check',
                        bottomNav: false,
                    },
                    {
                        name: 'Flashcards',
                        url: '/flashcards',
                        icon: 'mdi-cards',
                        bottomNav: true,
                    }
                ]
            }
        },
        props: {
            
        },
        mounted() {
            // load theme
            var themeName = this.$cookie.get('theme') === null ? 'light' : this.$cookie.get('theme');
            this.$vuetify.theme.themes['light'] = this.$cookie.get('theme') === null ? themes.light : themes[this.$cookie.get('theme')];
            //this.$vuetify.theme.themes['dark'] = themes.dark;
            this.$vuetify.theme.dark = (themeName == 'dark');
        },
        methods: {
            updateTheme: function() {
                this.theme = (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme');
            }
        }
    }
</script>
