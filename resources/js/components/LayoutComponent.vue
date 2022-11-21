<template>
   <v-app>
        <v-app-bar id="app-bar" app dense dark clipped-left color="primary" height="52">
            <div id="logo" class="mr-10"><v-icon class="mr-1">mdi-coffee</v-icon> Immersion</div>
            
            <ul id="app-bar-links" class="d-none d-sm-block">
                <li v-for="(item, index) in navigation" :key="index">
                    <v-btn class="px-3" plain :to="item.url">{{ item.name }}</v-btn>
                </li>
            </ul>
        </v-app-bar>

        <v-navigation-drawer id="navigation-drawer" class="d-flex justify-column" bottom app clipped temporary v-model="drawer">
            <v-spacer></v-spacer>
            <v-list class="mt-0" dense nav>
                <v-list-item-group>
                    <v-list-item v-for="(item, index) in navigation" :key="index"  :to="item.url"  class="text-decoration-none">
                        <v-list-item-icon><v-icon v-text="item.icon"></v-icon></v-list-item-icon>
                        <v-list-item-content><v-list-item-title v-text="item.name"></v-list-item-title></v-list-item-content>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>

        <v-main :style="{background: $vuetify.theme.currentTheme.background}">
            <router-view :key="$route.fullPath"></router-view>
        </v-main>
        
        <v-bottom-navigation dense grow class="d-flex d-sm-flex d-md-flex d-lg-none d-xl-none" dark background-color="primary">
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
    export default {
        data: function() {
            return {
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
                        url: '/review',
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
        },
        methods: {
        }
    }
</script>
