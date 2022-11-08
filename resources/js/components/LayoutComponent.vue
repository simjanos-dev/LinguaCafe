<template>
   <v-app>
        <v-navigation-drawer app floating class="d-none d-sm-none d-md-none d-lg-flex d-xl-flex" color="navigation">
            <v-list-item class="justify-center" width="100px">
                <v-icon class="mr-3" color="primary">mdi-coffee</v-icon> <span>Lingua</span>
            </v-list-item>

            <v-list dense nav >
                <v-list-item-group color="primary">
                    <v-list-item v-for="(item, index) in navigation" :key="index"  :to="item.url"  class="text-decoration-none">
                        <v-list-item-icon><v-icon v-text="item.icon"></v-icon></v-list-item-icon>
                        <v-list-item-content><v-list-item-title v-text="item.name"></v-list-item-title></v-list-item-content>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>

        <v-main :style="{background: $vuetify.theme.currentTheme.background}">
            <v-container id="content" class="pa-0" fluid>
                <router-view :key="$route.fullPath"></router-view>
            </v-container>
        </v-main>
        
        <v-bottom-navigation grow class="d-flex d-sm-flex d-md-flex d-lg-none d-xl-none" background-color="primary">
            <v-btn class="text-decoration-none"  height="56" v-for="(item, index) in navigation" :key="index"  :to="item.url" v-if="item.topbar">
                <span>{{ item.name }}</span>
                <v-icon>{{ item.icon }}</v-icon>
            </v-btn>
        </v-bottom-navigation>
    </v-app>
</template>

<script>
    export default {
        data: function() {
            return {
                navbarVisible: true,
                navigation: [
                    {
                        name: 'Home',
                        url: '/',
                        icon: 'mdi-home',
                        topbar: true,
                    },
                    {
                        name: 'Library',
                        url: '/books',
                        icon: 'mdi-bookshelf',
                        topbar: true,
                    },
                    {
                        name: 'Vocabulary',
                        url: '/vocabulary/search',
                        icon: 'mdi-translate',
                        topbar: true,
                    },
                    {
                        name: 'Review',
                        url: '/review',
                        icon: 'mdi-playlist-check',
                        topbar: false,
                    },
                    {
                        name: 'Flashcards',
                        url: '/flashcards',
                        icon: 'mdi-cards',
                        topbar: true,
                    }
                ]
            }
        },
        props: {
            
        },
        mounted() {
        },
        methods: {
        }
    }
</script>
