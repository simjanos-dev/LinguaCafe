<template>
    <div id="edit-flashcards">
        <v-btn color="primary" @click="save">Save</v-btn>
        <v-btn color="primary" @click="cancel">Cancel</v-btn>
        
        <v-alert
            class="my-3" 
            border="left"
            type="error"
            v-if="error"
            >
            Something went wrong. Please try again.
        </v-alert>

        <v-text-field 
            class="mt-6"
            filled
            dense
            label="Name"
            v-model="name"
            :rules="[rules.required]"
        ></v-text-field>

        <v-select
            dense
            filled
            v-model="type"
            :items="types"
            item-text="text"
            item-value="value"
            label="Select"
        ></v-select>
        
        <v-simple-table id="flashcards" class="py-0 no-hover border" dense>
            <thead>
                <tr>
                    <th class="index">#</th>
                    <th class="level">Level</th>
                    <th class="target-language">{{ language.charAt(0).toUpperCase() + language.slice(1) }}</th>
                    <th class="reading">Reading</th>
                    <th class="translation">Translation</th>
                    <th class="actions px-2">Options</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="(flashCard, index) in flashCards">
                    <tr v-if="!flashCard.deleted && selectedFlashcard !== index" :key="index">
                        <td class="index">{{ (index + 1) }}</td>
                        <td class="level"><div :title="flashCard.level">{{ flashCard.level }}</div></td>
                        <td class="target-language"><div :title="flashCard.sentence">{{ flashCard.sentence }}</div></td>
                        <td class="reading"><div :title="flashCard.reading">{{ flashCard.reading }}</div></td>
                        <td class="translation"><div :title="flashCard.translation">{{ flashCard.translation }}</div></td>
                        <td class="actions px-2">
                            <v-btn icon><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                        </td>
                    </tr>
                    <tr class="edit" v-if="!flashCard.deleted && selectedFlashcard == index" :key="index">
                        <td class="index">{{ '#' + (index + 1) }}</td>
                        <td class="level">{{ flashCard.level }}</td>
                        <td class="target-language"><textarea class="form-control" v-model="flashCard.sentence"></textarea></td>
                        <td class="reading"><textarea class="form-control" v-model="flashCard.reading"></textarea></td>
                        <td class="translation"><textarea class="form-control" v-model="flashCard.translation"></textarea></td>
                        <td class="actions px-2"><v-btn color="primary" @click="selectedFlashcard = -1"><v-icon>mdi-check</v-icon></v-btn></td>
                    </tr>
                </template>
            </tbody>
        </v-simple-table>

        <v-btn color="primary" @click="addFlashcard()">Add flashcard</v-btn>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                rules: {
                    required: (value) => {
                        return !!value || 'Required.';
                    },
                },
                error: false,
                types: [
                    {
                        text: 'Normal',
                        value: 'normal'
                    },
                    {
                        text: 'Typing',
                        value: 'typing'
                    }
                ],
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
