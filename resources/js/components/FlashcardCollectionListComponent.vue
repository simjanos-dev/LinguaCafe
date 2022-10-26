<template>
    <div id="flashcard-collections" v-if="flashcardCollections.length">
        <router-link class="sidebar-button" to="/flashcards/edit/">
            <button id="create-flashcard-collection-button" class="btn btn-primary">Create new collection</button>
        </router-link>
        <div class="flashcard-collection" v-for="(collection, index) in flashcardCollections" :key="index">
            <div class="flashcard-collection-name">{{ collection.name }}</div>
            <div class="flashcard-collection-buttons">
                <button class="btn btn-primary" @click="deleteFlashcardCollection(collection.id)">Delete</button>
                <router-link class="sidebar-button" :to="'/flashcards/edit/' + collection.id">
                    <button class="btn btn-primary">Edit</button>
                </router-link>
                <router-link class="sidebar-button" :to="'#' + collection.id">
                    <button class="btn btn-primary">Practice</button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                flashcardCollections: [],
            }
        },
        props: {
            
        },
        mounted() {
            this.loadFlashcardCollections();
        },
        methods: {
            loadFlashcardCollections: function () {
                axios.post('/flashcards').then(function (response) {
                    this.flashcardCollections = response.data;
                }.bind(this)).catch(function (error) {
                    
                }).then(function () {

                });
            },
            deleteFlashcardCollection: function(flashcardCollectionId) {
                if(confirm('Are you sure you want to delete this collection?')) {
                    axios.post('/flashcards/delete', {
                        flashcardCollectionId: flashcardCollectionId
                    }).then(function (response) {
                        if (response.data == 'success') {
                            this.loadFlashcardCollections();
                        }
                    }.bind(this)).catch(function (error) {
                        
                    }).then(function () {

                    });
                }
            }
        }
    }
</script>
