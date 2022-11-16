<template>
    <div id="kanji-details" >
        <div class="d-flex h3 my-4 mb-8">
            {{ kanji }} kanji details
            <v-spacer></v-spacer>
            <v-btn icon><v-icon>mdi-dots-horizontal</v-icon></v-btn>
        </div>
        <div>
            <div id="kanji-details-column">
                <div id="characters-row">
                    <!-- Kanji -->
                    <div id="character">{{ kanji }}</div>
                    
                    <!-- Kanji drawing -->
                    <div id="character-dmak-box">
                        <div id="character-dmak"></div>
                    </div>
                </div>

                <!-- Kanji info -->
                <v-simple-table id ="kanji-details-table" dense class="no-hover no-row-border no-background max-auto mt-3">
                    <tbody>
                        <tr>
                            <td>Meanings:</td>
                            <td>{{ meanings }}</td>
                        </tr>
                        <tr>
                            <td>Onyomi:</td>
                            <td>{{ readingsOn }}</td>
                        </tr>
                        <tr>
                            <td>Kunyomi:</td>
                            <td>{{ readingsKun }}</td>
                        </tr>
                        <tr>
                            <td>Strokes:</td>
                            <td>{{ strokes }}</td>
                        </tr>
                        <tr>
                            <td>Grade:</td>
                            <td>{{ gradeNames[grade] }}</td>
                        </tr>
                        <tr>
                            <td>JLPT:</td>
                            <td>{{ jlptNames[jlpt] }}</td>
                        </tr>
                        <tr>
                            <td>Frequency:</td>
                            <td>{{ frequency }}</td>
                        </tr>
                    </tbody>
                </v-simple-table>
            </div>
            <div id="kanji-words-column" class="h5">
                Words you know which contain {{ kanji }}
                <v-simple-table id="kanji-words-table" dense class="no-background mt-3">
                    <thead>
                        <tr>
                            <th class="pl-0">Word</th>
                            <th>Reading</th>
                            <th>Translation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(word, index) in words" :key="index">
                            <td class="pl-0">{{ word.word }}</td>
                            <td>{{ word.reading }}</td>
                            <td>{{ word.translation }}</td>
                        </tr>
                    </tbody>
                </v-simple-table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                jlptNames: {
                    0: 'Not in JLPT',
                    1: 'N1',
                    2: 'N2/N3',
                    3: 'N4',
                    4: 'N4',
                    5: 'N5',
                },
                gradeNames: {
                    0: 'No grade',
                    1: '1',
                    2: '2',
                    3: '3',
                    4: '4',
                    5: '5',
                    6: '6',
                    8: 'Junior highschool',
                    10: 'Jinmeiyou kanji'
                },
                words: [],
                kanji: this.$route.params.character,
                meanings: '',
                readingsOn: [],
                readingsKun: [],
                grade: 0,
                strokes: 0,
                frequency: 0,
                jlpt: 0
            }
        },
        props: {
            
        },
        mounted() {
            this.dmak = new Dmak(this.kanji, {
                element : 'character-dmak',
                uri: '/js/dmak/kanji/',
                step: 0.015,
                grid: {
                    show: false
                },
                stroke: {
                    attr: {
                        active: '#7b4cfd',
                        'stroke-width': 5,
                        'font-size': 1
                    },
                    animated: {
                        erasing: false,
                    }
                },
                drew: this.drawingFinished,
            });

            axios.post('/kanji/details', {
                kanji: this.kanji
            }).then(function (response) {
                console.log(response.data);
                this.words = response.data.words;
                this.meanings = JSON.parse(response.data.kanji.meanings).join(', ');
                this.readingsOn = JSON.parse(response.data.kanji.readings_on).join(', ');
                this.readingsKun = JSON.parse(response.data.kanji.readings_kun).join(', ');
                this.strokes = response.data.kanji.strokes;
                this.grade = response.data.kanji.grade;
                this.jlpt = response.data.kanji.jlpt;
                this.frequency = response.data.kanji.frequency;
            }.bind(this)).catch(function (error) {

            }).then(function () {
            });            
        },
        methods: {
            drawingFinished: function(x) {
                if (x == this.dmak.strokes.length - 1) {
                    setTimeout(() => {
                        this.dmak.erase();
                        this.dmak.render();
                    }, 1500);
                }
                
            }
        }
    }
</script>
