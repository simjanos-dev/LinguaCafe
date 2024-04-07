<template>
   <v-app :class="{'eink': theme == 'eink', 'dark': theme == 'dark'}">
        
        <!-- Dialogs -->
        <start-review-dialog v-model="startReviewDialog" />
        <logout-dialog v-model="logoutDialog"/>

        <template v-if="$router.currentRoute.path !== '/login'">
            <theme-selection-dialog v-model="themeSelectionDialog" @input="updateTheme"></theme-selection-dialog>
            <language-selection-dialog v-model="languageSelectionDialog"></language-selection-dialog>
            <v-navigation-drawer
                id="navigation-drawer" 
                app 
                dense
                :class="{'eink': theme == 'eink'}" 
                :mini-variant="$vuetify.breakpoint.md || navbarCollapsed" 
                :permanent="$vuetify.breakpoint.mdAndUp" 
                v-model="drawer" 
                color="navigation"
            >
                <!-- Logo -->
                <div id="logo" class="my-5">
                    <span v-if="$vuetify.breakpoint.lgAndUp && !navbarCollapsed">
                            Lingua Cafe
                    </span>
                </div>

                <v-list nav shaped dense class="pl-0">
                    <!-- Navigation buttons -->
                    <v-list-item 
                        class="navigation-button" 
                        v-for="(item, index) in navigation" 
                        :key="index" 
                        :to="item.url"
                        @click="navigationClick(item.name, $event)"
                    >
                        <v-icon> {{ item.icon }} </v-icon>
                        <span class="pl-6"> {{ item.name }} </span>
                    </v-list-item>
                    <v-list-item class="navigation-button" @click="openLogoutDialog">
                        <v-icon> mdi-logout </v-icon>
                        <span class="pl-6"> Logout </span>
                    </v-list-item>
                </v-list>

                <template v-slot:append>
                    <!-- Large navigation drawer -->
                    <template v-if="!$vuetify.breakpoint.md && !navbarCollapsed">
                        <v-list nav shaped dense class="pl-0">
                            <!-- Navigation buttons -->
                            <v-list-item class="navigation-button" @click="collapseNavbar">
                                <v-icon> mdi-arrow-collapse-left </v-icon>
                                <span class="pl-6"> Hide </span>
                            </v-list-item>
                            <v-list-item class="navigation-button" @click="navigationClick('User manual', $event)">
                                <v-icon> mdi-account-question </v-icon>
                                <span class="pl-6"> User manual</span>
                            </v-list-item>
                            <v-list-item class="navigation-button" @click="themeSelectionDialog = true;">
                                <v-icon> mdi-palette </v-icon>
                                <span class="pl-6"> Theme</span>
                            </v-list-item>
                            <v-list-item class="navigation-button" @click="languageSelectionDialog = true;">
                                <v-img class="border" :src="'/images/flags/' + selectedLanguage.toLowerCase() + '.png'" max-width="26" height="17"></v-img>
                                <span class="pl-5"> Language</span>
                            </v-list-item>
                        </v-list>
                    </template>

                    <!-- Mini navigation drawer -->
                    <template v-else>
                        <v-btn v-if="$vuetify.breakpoint.lgAndUp" id="collapse" rounded text class="mini-drawer-button" @click="expandNavbar" title="Expand sidebar">
                            <v-icon>mdi-arrow-collapse-right</v-icon>
                        </v-btn>
                        <v-btn id="user-manual" rounded text class="mini-drawer-button" @click="navigationClick('User manual', $event)" title="User manual">
                            <v-icon>mdi-account-question</v-icon>
                        </v-btn>
                        <v-btn id="theme" rounded text class="mini-drawer-button" @click="themeSelectionDialog = true" title="Theme">
                            <v-icon>mdi-palette</v-icon>
                        </v-btn>
                        <v-btn id="language" rounded text class="mini-drawer-button" @click="languageSelectionDialog = true" title="Select language">
                            <v-img :src="'/images/flags/' + selectedLanguage.toLowerCase() + '.png'" max-width="31" height="20"></v-img> 
                        </v-btn>
                    </template>
                </template>
            </v-navigation-drawer>

            <!-- Bottom navigation -->
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
        </template>
        
        <v-main :style="{background: $vuetify.theme.currentTheme.background}" :class="{ eink: theme == 'eink'}">
            <router-view :user-count="$props._userCount" :language="selectedLanguage" :key="$route.fullPath"></router-view>
        </v-main>
    </v-app>
</template>

<script>
import themes from './../themes';
    export default {
        data: function() {
            return {
                selectedLanguage: this.$props._selectedLanguage,
                theme: (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme'),
                logoutDialog: false,
                themeSelectionDialog: false,
                languageSelectionDialog: false,
                startReviewDialog: false,
                drawer: false,
                navbarVisible: true,
                navbarCollapsed: false,
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
                        name: 'Review',
                        url: '',
                        click: this.openStartReviewDialog,
                        icon: 'mdi-playlist-check',
                        bottomNav: false,
                    },
                    // {
                    //     name: 'Flashcards',
                    //     url: '/flashcards',
                    //     icon: 'mdi-cards',
                    //     bottomNav: true,
                    // },
                    {
                        name: 'User settings',
                        url: '/user-settings',
                        icon: 'mdi-account-cog',
                        bottomNav: false,
                    },
                    {
                        name: 'Admin settings',
                        url: '/admin',
                        icon: 'mdi-shield-lock',
                        bottomNav: false,
                    }
                ]
            }
        },
        props: {
            _selectedLanguage: String,
            _userName: {
                type: String,
                default: '',
            },
            _userCount: Number,
        },
        beforeMount() {
            if (this.$props._selectedLanguage == 'japanese') {
                this.navigation.splice(3, 0, {
                    name: 'Kanji',
                    url: '/kanji/search',
                    icon: 'mdi-ideogram-cjk',
                    bottomNav: false,
                });
            }

            // load theme
            var themeName = this.$cookie.get('theme') === null ? 'light' : this.$cookie.get('theme');
            this.$vuetify.theme.themes['light'] = this.$cookie.get('theme') === null ? themes.light : themes[this.$cookie.get('theme')];
            this.$vuetify.theme.themes['dark'] = themes.dark;
            this.$vuetify.theme.dark = (themeName == 'dark');

            // load navbar status
            if (this.$cookie.get('navbar-collapsed') !== null) {
                this.navbarCollapsed = this.$cookie.get('navbar-collapsed') === 'true';
            }
        },
        methods: {
            collapseNavbar() {
                this.navbarCollapsed = true;
                this.$cookie.set('navbar-collapsed', this.navbarCollapsed, 3650);
            },
            expandNavbar() {
                this.navbarCollapsed = false;
                this.$cookie.set('navbar-collapsed', this.navbarCollapsed, 3650);
            },
            navigationClick(itemName, event) {
                if (itemName === 'Review') {
                    this.startReviewDialog = true;
                    event.preventDefault();
                }

                // clicked on user manual
                if (itemName === 'User manual' && this.$router.currentRoute.path !== '/user-manual') {
                    this.$router.push({ path: '/user-manual', replace: true });
                }

            },
            updateTheme() {
                this.theme = (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme');
            },
            openLogoutDialog() {
                this.logoutDialog = true;
            },
        }
    }
</script>
