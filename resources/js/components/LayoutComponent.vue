<template>
   <v-app>
        <v-navigation-drawer id="navigation-drawer" floating :mini-variant="$vuetify.breakpoint.md" app permanent v-model="drawer" color="navigation" v-if="$vuetify.breakpoint.mdAndUp">
            <div id="logo" class="my-8"><v-icon>mdi-coffee</v-icon> Lingua Cafe</div>
            <v-list nav rounded>
                <v-list-item-group color="primaryDark">
                    <v-list-item class="navigation-button" v-for="(item, index) in navigation" :key="index"  :to="item.url">
                        <div v-if="drawerMinimized">
                            <v-icon> {{ item.icon }} </v-icon>
                            <span> {{ item.name }} </span>
                        </div>

                        <template v-else>
                            <v-list-item-icon><v-icon v-text="item.icon"></v-icon></v-list-item-icon>
                            <v-list-item-content class="ml-2"><v-list-item-title v-text="item.name"></v-list-item-title></v-list-item-content>
                        </template>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
            <template v-slot:append>
                <v-btn id="language" rounded text class="ma-2">
                    <v-img :src="'/images/flags/japanese'" height="30" width="43"></v-img> 
                    <span class="pl-2">Japanese</span>
                </v-btn>
            </template>
        </v-navigation-drawer>

        <v-main :style="{background: $vuetify.theme.currentTheme.background}">
            <router-view :key="$route.fullPath"></router-view>
        </v-main>
        
        <v-bottom-navigation dense grow shift class="d-flex d-sm-flex d-md-none" dark background-color="primary">
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
                drawerMinimized: true,
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
        },
        methods: {
        }
    }
</script>
