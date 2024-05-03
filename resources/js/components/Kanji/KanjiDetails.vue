<template>
    <v-container id="kanji">
        <div class="subheader small-margin">Kanji info</div>
        <v-card outlined id="kanji-info-box" class="rounded-lg">
            <v-card-text class="d-flex flex-wrap justify-center">
                <div id="characters">
                    <!-- Kanji -->
                    <div class="character">{{ kanji }}</div>
                    
                    <!-- Kanji drawing -->
                    <div class="character"><div id="character-dmak"></div></div>
                </div>

                <!-- Kanji info -->
                <div id ="kanji-info">
                    <div id="chip-info" class="mb-2">
                        <v-chip small class="mb-1" dark color="pink" v-if="strokes">
                            {{ strokes }} strokes
                        </v-chip>
                        <v-chip small class="mb-1" dark color="indigo" v-if="grade">
                            {{ grade }}. grade
                        </v-chip>
                        <v-chip small class="mb-1" dark color="teal" v-if="jlpt">
                            JLPT {{ jlptNames[jlpt] }}
                        </v-chip>
                        <v-chip small class="mb-1" dark color="red" v-if="frequency">
                            {{ frequency }}. most common
                        </v-chip>
                    </div>
                    <div class="kanji-info-group mt-3">
                        <div class="kanji-info-title">Meanings</div>
                        <div class="kanji-info my-2 pl-4">
                            <span class="mr-6" v-for="(meaning, index) in meanings">{{ meaning }}</span>
                        </div>
                    </div>
                    <div class="kanji-info-group my-4" v-if="readingsKun.length">
                        <div class="kanji-info-title">Kun'yomi</div>
                        <div class="kanji-info my-2 pl-4">
                            <span class="mr-6" v-for="(reading, index) in readingsKun">{{ reading }}</span>
                        </div>
                    </div>
                    <div class="kanji-info-group"  v-if="readingsOn.length">
                        <div class="kanji-info-title">On'yomi</div>
                        <div class="kanji-info my-2 pl-4">
                            <span class="mr-6" v-for="(reading, index) in readingsOn">{{ reading }}</span>
                        </div>
                    </div>
                </div>
            </v-card-text>
        </v-card>
        
        <!-- Kanji radicals -->
        <div class="subheader mt-8">Radicals</div>
        <div id="kanji-radicals" class="d-flex flex-wrap">
            <div class="kanji-radical mr-3" v-for="(radical, index) in radicals">{{ radical.radical }} <span>{{ radical.strokes }} strokes</span></div>
        </div>
        
        <!-- Kanji words -->
        <div class="subheader mt-8">Known words with {{ kanji }}</div>
        <div id="kanji-words" class="h5">
            <v-simple-table id="kanji-words-table" dense class="mt-3 mb-4 border rounded-lg no-hover">
                <thead>
                    <tr>
                        <th>Word</th>
                        <th class="reading">Reading</th>
                        <th>Translation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(word, index) in words" :key="index">
                        <td>{{ word.word }}</td>
                        <td class="reading">{{ word.reading }}</td>
                        <td>{{ word.translation }}</td>
                    </tr>
                </tbody>
            </v-simple-table>
        </div>
    </v-container>
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
                kanji: this.$route.params.character,
                radicals: [],
                words: [],
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
                uri: '/images/kanji/',
                step: 0.01,
                grid: {
                    show: false
                },
                stroke: {
                    attr: {
                        active: this.$vuetify.theme.currentTheme.primary,
                        stroke: this.$vuetify.theme.currentTheme.text,
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
            }).then((response) => {
                this.words = response.data.words;
                this.radicals = JSON.parse(response.data.radicals);
                this.meanings = JSON.parse(response.data.kanji.meanings);
                this.readingsOn = JSON.parse(response.data.kanji.readings_on);
                this.readingsKun = JSON.parse(response.data.kanji.readings_kun);
                this.strokes = response.data.kanji.strokes;
                this.grade = response.data.kanji.grade;
                this.jlpt = response.data.kanji.jlpt;
                this.frequency = response.data.kanji.frequency;
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
