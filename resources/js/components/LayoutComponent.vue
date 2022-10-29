<template>
    <div id="page"><!--
    --><div id="topbar">
            <router-link class="topbar-button" to="/">Home</router-link>
            <router-link class="topbar-button" to="/books">Library</router-link>
            <router-link class="topbar-button" to="/vocabulary/search">Vocabulary</router-link>
            <router-link class="topbar-button" to="/review">Review</router-link>
            <router-link class="topbar-button" to="/flashcards">Flashcards</router-link>
            <a class="topbar-button right" href="#" @click.prevent="logout">Logout</a>

            <a class="topbar-button dropdown" href="#" @click.stop="topbarPopup = !topbarPopup">Menus</a>
            <div :class="{'topbar-popup': true, 'visible': topbarPopup}">
                <router-link class="topbar-button popup" to="/">Home</router-link>
                <router-link class="topbar-button popup" to="/books">Library</router-link>
                <router-link class="topbar-button popup" to="/vocabulary/search">Vocabulary</router-link>
                <router-link class="topbar-button popup" to="/review">Review</router-link>
                <router-link class="topbar-button popup" to="/flashcards">Flashcards</router-link>
            </div>
        </div><!-- 
    --><div id="sidebar">
            <div id="sidebar-brand"><i class="fa fa-coffee"></i> Lingua</div>
            <div id="sidebar-buttons"> 
                <router-link class="sidebar-button" to="/"><div class="icon"><i class="fa fa-home"></i></div> Home</router-link>
                <router-link class="sidebar-button" to="/books"><div class="icon"><i class="fa fa-book-open"></i></div> Library</router-link>
                <router-link class="sidebar-button" to="/vocabulary/search"><div class="icon"><i class="fa fa-language"></i></div> Vocabulary</router-link>
                <router-link class="sidebar-button" to="/review"><div class="icon"><i class="fa fa-spell-check"></i></div> Review</router-link>
                <router-link class="sidebar-button" to="/flashcards"><div class="icon"><i class="fa fa-mobile"></i></div> Flashcards</router-link>
                <a class="sidebar-button right" href="#" @click.prevent="logout"><div class="icon"><i class="fa fa-sign-out-alt"></i></div> Logout</a>
            </div>
        </div>
        <div id="content">
            <router-view :key="$route.fullPath"></router-view>
        </div>

        <form id="logout-form" action="/logout" method="POST"></form>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                page: 'test',
                topbarPopup: false,
            }
        },
        props: {
            
        },
        mounted() {
            document.getElementById('app').addEventListener('click', () => { this.topbarPopup = false; });
        },
        methods: {
            loadUrl(url) {
                this.topbarPopup = false;

                axios.get(url).then(function (response) {
                    this.page = response.data;
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                })
                .then(function () {
                });
            },
            logout: function() {
                axios.post('/logout').then(function (response) {
                    document.location.href = '/';
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                })
                .then(function () {
                });
            }
        }
    }
</script>
