<template>
    <div id="page"><!--
        --><div id="sidebar">
            <div id="sidebar-brand"><i class="fa fa-coffee"></i> Lingua</div>
            <div id="sidebar-buttons"> 
                <router-link class="sidebar-button" to="/"><div class="icon"><i class="fa fa-home"></i></div> Home</router-link>
                <router-link class="sidebar-button" to="/books"><div class="icon"><i class="fa fa-book-open"></i></div> Library</router-link>
                <router-link class="sidebar-button" to="/vocabulary/search"><div class="icon"><i class="fa fa-language"></i></div> Vocabulary</router-link>
                <router-link class="sidebar-button" to="/review"><div class="icon"><i class="fa fa-spell-check"></i></div> Review</router-link>
                <router-link class="sidebar-button" to="/flashcards"><div class="icon"><i class="fa fa-mobile"></i></div> Flashcards</router-link>
            </div>
        </div><!--
        --><router-view :key="$route.fullPath"></router-view><!--
    --></div>
</template>

<script>
    export default {
        data: function() {
            return {
                page: 'test'
            }
        },
        props: {
            
        },
        mounted() {
        },
        methods: {
            loadUrl(url) {
                axios.get(url, {
                    headers: {
                        "no-layout": "true",
                    },
                }).then(function (response) {
                    this.page = response.data;
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
