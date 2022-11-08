<template>
    <div id="chapters" class="d-flex flex-column justify-center flex-nowrap" v-if="book !== null">
        <v-card outlined class="chapter button-box mx-auto mt-6 mb-2">
            <v-card-actions class="px-0">
                <v-btn :small="$vuetify.breakpoint.xsOnly" class="mx-0" rounded color="green" :to="'/chapters/create/' + book.id"><v-icon>mdi-plus</v-icon> Create</v-btn>
                <v-spacer></v-spacer>
                <v-btn :small="$vuetify.breakpoint.xsOnly" class="mx-0" rounded color="primary" :to="'/chapters/read/' + randomChapter"  v-if="chapters.length"><v-icon>mdi-shuffle-variant</v-icon> Read random chapter</v-btn>
            </v-card-actions>
        </v-card>

        <v-card :class="{'chapter': true, 'rounded-lg': true, 'mx-auto': true, 'my-6': index}" v-for="(chapter, index) in chapters" :key="index">
            <div class="d-flex flex-wrap flex-sm-nowrap justify-space-between">
                <v-card-text class="pa-0">
                    <v-card-title class="chapter-title pa-3">
                        {{ chapter.name }}
                        <v-spacer></v-spacer>
                        <v-btn icon><v-icon>mdi-dots-horizontal</v-icon></v-btn>
                    </v-card-title>
                    <v-simple-table dense class="chapter-info-table pb-4  mx-auto">
                        <tbody>
                            <tr>
                                <td width="200px">Words</td>
                                <td class="text-center"><div class="info-table-value">{{ chapter.wordCount.total }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Unique words</td>
                                <td class="text-center"><div class="info-table-value">{{ chapter.wordCount.unique }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Known words</td>
                                <td class="text-center"><div class="info-table-value">{{ chapter.wordCount.known }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">Highlighted words</td>
                                <td class="text-center"><div class="info-table-value highlighted px-2" :style="{'background-color': $vuetify.theme.currentTheme.highlightedWord }">{{ chapter.wordCount.highlighted }}</div></td>
                            </tr>
                            <tr>
                                <td width="200px">New words</td>
                                <td class="text-center"><div class="info-table-value highlighted px-2" :style="{'background-color': $vuetify.theme.currentTheme.newWord }">{{ chapter.wordCount.new }}</div></td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                    <v-card-actions class="pa-3">
                        <v-spacer></v-spacer>
                        <!--<v-btn class="action-button" rounded color="primary" :to="'/chapters/edit/' + book.id + '/' + chapter.id">Edit</v-btn>-->
                        <v-btn :small="$vuetify.breakpoint.xsOnly" class="action-button" rounded color="primary" :to="'/review/' + book.id + '/' + chapter.id"><v-icon>mdi-spellcheck</v-icon> Review</v-btn>
                        <v-btn :small="$vuetify.breakpoint.xsOnly" class="action-button" rounded color="primary" :to="'/chapters/read/' + chapter.id"><v-icon>mdi-book-open-variant</v-icon> Read</v-btn>
                    </v-card-actions>
                </v-card-text>
            </div>
        </v-card>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                book: null,
                chapters: [],
                randomChapter: 0,
            }
        },
        props: {
            
        },
        mounted() {
            axios.post('/chapters', {
                'bookId': this.$route.params.bookId,
            }).then(function (response) {
                this.book = response.data.book;
                this.chapters = response.data.chapters;
                this.randomChapter = this.chapters[Math.floor(Math.random() * this.chapters.length)].id;

            }.bind(this)).catch(function (error) {
                
            }).then(function () {

            });
        },
        methods: {
        }
    }
</script>
