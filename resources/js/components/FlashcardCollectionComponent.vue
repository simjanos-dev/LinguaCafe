<template>
    <div id="edit-flashcard-collection-form">
        <button id="save-flashcard-collection-button" class="btn btn-primary" @click="save">Save flash card collection</button>
        <button class="btn btn-primary" @click="cancel()">Cancel</button>
        <input id="import-file" class="form-control" type="file" ref="importFileInput" @change="importFlashcards()">
        <div class="text-box red" v-if="errors.save">
            Something went wrong. Please try again.
        </div>
        <div class="form-group">
            <label class="form-check-label" for="name">Name</label>
            <input class="form-control" type="text" name="name" v-model="name" required>
        </div>
        <div class="text-box red" v-if="errors.emptyName">
            You must type in a name.
        </div>
        <div class="form-group">
            <label class="form-check-label" for="type">Type</label>
            <select class="form-control" name="type" v-model="type">
                <option value="normal">Normal</option>
                <option value="typing">Typing</option>
            </select>
        </div><br>
        <table id="edit-flashcards-table" class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Level</th>
                    <th>{{ language.charAt(0).toUpperCase() + language.slice(1) }}</th>
                    <th>Reading</th>
                    <th>Translation</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template v-for="(flashCard, index) in flashCards">
                    <tr v-if="!flashCard.deleted && selectedFlashcard !== index" :key="index">
                        <td>{{ (index + 1) }}</td>
                        <td><div :title="flashCard.level">{{ flashCard.level }}</div></td>
                        <td><div :title="flashCard.sentence">{{ flashCard.sentence }}</div></td>
                        <td><div :title="flashCard.reading">{{ flashCard.reading }}</div></td>
                        <td><div :title="flashCard.translation">{{ flashCard.translation }}</div></td>
                        <td>
                            <button class="btn btn-primary flashcard-button" @click="editFlashcard(index)"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-primary flashcard-button" @click="deleteFlashcard(index)"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr class="edit" v-if="!flashCard.deleted && selectedFlashcard == index" :key="index">
                        <td>{{ '#' + (index + 1) }}</td>
                        <td>{{ flashCard.level }}</td>
                        <td><textarea class="form-control" v-model="flashCard.sentence"></textarea></td>
                        <td><textarea class="form-control" v-model="flashCard.reading"></textarea></td>
                        <td><textarea class="form-control" v-model="flashCard.translation"></textarea></td>
                        <td><button class="btn btn-primary flashcard-button" @click="selectedFlashcard = -1"><i class="fa fa-check"></i></button></td>
                    </tr>
                </template>
            </tbody>
        </table>
        <button class="btn btn-primary" @click="addFlashcard()">Add flash card</button>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                errors: {
                    save: false,
                    emptyName: false,
                },
                language: '',
                id: -1,
                name: '',
                type: 'normal',
                flashCards: [],
                selectedFlashcard: -1,
            }
        },
        props: {
        },
        mounted() {
            if (this.$route.params.flashcardCollectionId !== undefined) {
                axios.post('/flashcards/get', {
                    flashcardCollectionId: this.$route.params.flashcardCollectionId,
                }).then(function (response) {
                    var data = response.data;
                    this.language = data.language;
                    this.id = data.flashcardCollection.id;
                    this.name = data.flashcardCollection.name;
                    this.type = data.flashcardCollection.type;;
                    

                    var flashcards = data.flashcards;
                    for (var i = 0; i < flashcards.length; i++) {
                        this.flashCards.push({
                            id: flashcards[i].id,
                            level: flashcards[i].level,
                            sentence: flashcards[i].sentence_raw,
                            reading: flashcards[i].reading,
                            translation: flashcards[i].translation,
                            deleted: false
                        });
                    }
                }.bind(this)).catch(function (error) {}).then(function () {});
            }
        },
        methods: {
            editFlashcard(index) {
                this.selectedFlashcard = index;
            },
            importFlashcards() {
                var reader = new FileReader();
                reader.readAsText(this.$refs.importFileInput.files[0], "UTF-8");
                reader.onload =  event => {
                    var lines = event.target.result.split(/\r*\n\r*/);
                    for (var i = 0; i < lines.length; i++) {
                        var data = lines[i].split('|');
                        if (data.length < 3) {
                            continue;
                        }

                        var flashCard = {
                            id: -1,
                            level: 1,
                            sentence: data[0],
                            reading: data[1],
                            translation: data[2],
                            deleted: false
                        }

                        this.flashCards.push(flashCard);
                    }
                }

                reader.onerror = event => {
                    console.error(event);
                }
            },
            addFlashcard() {
                this.flashCards.push({
                    id: -1,
                    level: 1,
                    sentence: '',
                    translation: '',
                    reading: '',
                    deleted: false
                });
            },
            deleteFlashcard(index) {
                if (this.flashCards[index].id == -1) {
                    this.flashCards.splice(index, 1);
                } else {
                    this.flashCards[index].deleted = true;
                }
            },
            save() {
                if (this.name == '') {
                    this.errors.emptyName = true;
                    return;
                }

                axios.post('/flashcards/save', {
                    flashCardCollectionId: this.id,
                    name: this.name,
                    type: this.type,
                    flashCards: JSON.stringify(this.flashCards)
                })
                .then(function (response) {
                    if (response.data == 'success') {
                        this.$router.push('/flashcards');
                    } else {
                        this.errors.save = true;
                    }
                }.bind(this))
                .catch(function (error) {
                    
                    console.log(error);
                }).then(function () {
                    // always
                });
            },
            cancel() {
                this.$router.push('/flashcards');
            }
        }
    }
</script>
