<template>
    <v-container id="flashcard-collections" class="d-flex flex-column flex-nowrap flex-sm-row flex-sm-wrap" v-if="flashcardCollections.length">
            <!--
                <v-btn color="primary" to="/flashcards/edit/">Create new collection</v-btn>
            -->
            <v-card class="flashcard-collection d-flex flex-column ma-6 mx-auto mx-sm-6" v-for="(collection, index) in flashcardCollections" :key="index">
                <v-card-title class="pa-3 text-center">
                    {{ collection.name }}
                    <v-spacer></v-spacer>
                    <v-btn icon><v-icon>mdi-dots-vertical</v-icon></v-btn>
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-simple-table dense class="flashcard-collection-info-table no-hover pb-4  mx-auto">
                        <tbody>
                            <tr>
                                <td width="200px">Cards</td>
                                <td class="text-center"><div class="info-table-value">347</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Due today</td>
                                <td class="text-center"><div class="info-table-value">18</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Unseen</td>
                                <td class="text-center"><div class="info-table-value">116</div></td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                </v-card-text>
                <v-spacer></v-spacer>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    
                    <v-btn color="primary" :to="'/flashcards/edit/' + collection.id">Edit</v-btn>
                    <v-btn color="primary">Review</v-btn>
                </v-card-actions>
            </v-card>
    </v-container>
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
                if(confirm('Do you want to delete this collection?')) {
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
