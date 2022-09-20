<template>
    <div id="edit-flash-card-collection-form">
        <button id="save-flash-card-collection-button" class="btn btn-primary" @click="save">Save flash card collection</button>
        <button class="btn btn-primary" @click="cancel()">Cancel</button>
        <input id="import-file" class="form-control" type="file" ref="importFileInput" @change="importFlashCards()">
        <div class="form-group">
            <label class="form-check-label" for="name">Name</label>
            <input class="form-control" type="text" name="name" v-model="name" required>
        </div>
        <div class="form-group">
            <label class="form-check-label" for="type">Type</label>
            <select class="form-control" name="type" v-model="type">
                <option value="normal">Normal</option>
                <option value="typing">Typing</option>
            </select>
        </div><br>
        <table id="edit-flash-cards-table" class="table table-sm table-bordered">
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
                    <tr v-if="!flashCard.deleted && selectedFlashCard !== index" :key="index">
                        <td>{{ (index + 1) }}</td>
                        <td><div :title="flashCard.level">{{ flashCard.level }}</div></td>
                        <td><div :title="flashCard.sentence">{{ flashCard.sentence }}</div></td>
                        <td><div :title="flashCard.reading">{{ flashCard.reading }}</div></td>
                        <td><div :title="flashCard.translation">{{ flashCard.translation }}</div></td>
                        <td>
                            <button class="btn btn-primary flash-card-button" @click="editFlashCard(index)"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-primary flash-card-button" @click="deleteFlashCard(index)"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr class="edit" v-if="!flashCard.deleted && selectedFlashCard == index" :key="index">
                        <td>{{ '#' + (index + 1) }}</td>
                        <td>{{ flashCard.level }}</td>
                        <td><textarea class="form-control" v-model="flashCard.sentence"></textarea></td>
                        <td><textarea class="form-control" v-model="flashCard.reading"></textarea></td>
                        <td><textarea class="form-control" v-model="flashCard.translation"></textarea></td>
                        <td><button class="btn btn-primary flash-card-button" @click="selectedFlashCard = -1"><i class="fa fa-check"></i></button></td>
                    </tr>
                </template>
            </tbody>
        </table>
        <button class="btn btn-primary" @click="addFlashCard()">Add flash card</button>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                language: this.$props._language,
                id: this.$props._id,
                name: this.$props._name,
                type: this.$props._type,
                flashCards: [],
                selectedFlashCard: -1,
            }
        },
        props: {
            _language: {
                type: String,
                default: ''
            },
            _id: {
                type: Number,
                default: -1
            },
            _name: {
                type: String,
                default: ''
            },
            _type: {
                type: String,
                default: 'normal'
            },
            _flashCards: {
                type: String,
                default: '{}'
            }
        },
        mounted() {
            var flashCardsProp = JSON.parse(this.$props._flashCards);
            for (var i = 0; i < flashCardsProp.length; i++) {
                this.flashCards.push({
                    id: flashCardsProp[i].id,
                    level: flashCardsProp[i].level,
                    sentence: flashCardsProp[i].sentence_raw,
                    reading: flashCardsProp[i].reading,
                    translation: flashCardsProp[i].translation,
                    deleted: false
                });
            }
        },
        methods: {
            editFlashCard(index) {
                this.selectedFlashCard = index;
            },
            importFlashCards() {
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
            addFlashCard() {
                this.flashCards.push({
                    id: -1,
                    level: 1,
                    sentence: '',
                    translation: '',
                    reading: '',
                    deleted: false
                });
            },
            deleteFlashCard(index) {
                if (this.flashCards[index].id == -1) {
                    this.flashCards.splice(index, 1);
                } else {
                    this.flashCards[index].deleted = true;
                }
            },
            save() {
                axios.post('/save-flash-card-collection', {
                    flashCardCollectionId: this.id,
                    name: this.name,
                    type: this.type,
                    flashCards: JSON.stringify(this.flashCards)
                })
                .then(function (response) {
                    window.location.href = '/flash-card-collections';
                }.bind(this))
                .catch(function (error) {
                    
                    console.log(error);
                }).then(function () {
                    // always
                });
            },
            cancel() {
                window.location.href = '/flash-card-collections';
            }
        }
    }
</script>
