<template>
    <v-container id="kanji-list">
        <v-tabs 
            v-model="groupBy" 
            background-color="white" 
            class="rounded-lg border overflow-hidden"
            @change="updateKanjiList" 
        >
            <v-tab>Grade</v-tab>
            <v-tab>JLPT</v-tab>
            <v-spacer></v-spacer>
            
            <!-- Hide unknown button -->
            <v-btn 
                v-if="showUnknown"
                rounded
                depressed
                text
                class="px-2 kanji-list-toolbar-button" 
                @click="showUnknown = !showUnknown; updateKanjiList();" 
            >
                Hide unknown <v-icon class="ml-1">mdi-eye-off</v-icon>
            </v-btn>
            
            <!-- Show unknown button -->
            <v-btn 
                v-if="!showUnknown"
                rounded
                depressed
                text
                class="px-2 kanji-list-toolbar-button" 
                @click="showUnknown = !showUnknown; updateKanjiList();" 
            >
                Show unknown <v-icon class="ml-1">mdi-eye</v-icon>
            </v-btn>
        </v-tabs>

        <div>
            <!-- Skeleton loader -->
            <template v-for="groupIndex in 3" v-if="loading">
                <div class="subheader mt-8">
                    <v-skeleton-loader
                        class="skeleton-title ml-1 my-2 mb-0"
                        type="image"
                    ></v-skeleton-loader>
                </div>

                <v-card outlined class="rounded-lg">
                    <v-card-text class="d-flex flex-wrap mt-0">
                        <template v-for="kanjiIndex in 24 + ((groupIndex + 1) * 20)">
                            <v-skeleton-loader
                                class="skeleton-button"
                                type="image"
                            ></v-skeleton-loader>
                        </template>
                    </v-card-text>
                </v-card>
            </template>

            <!-- JLPT info -->
            <v-alert width="100%" class="my-5" type="info" color="primary" border="left" v-if="groupBy == 1 && !loading">
                The JLPT data is from the previous 4 level system, which was changed in 2010. There is no official kanji list for the current JLPT.
                The old levels are similar to the current ones, except that the old N2 is now divided between N2 and N3.
            </v-alert>
            
            <!-- Kanji List -->
            <template v-for="(group, groupIndex) in kanji" v-if="!loading">
                <div class="subheader mt-8" v-if="group.length">
                    <template v-if="groupIndex == 0">
                        {{ groupNames[groupBy][groupIndex] }} ({{ knownKanjiCounts[groupIndex] === undefined ? 0 : knownKanjiCounts[groupIndex].total }})
                    </template>
                    <template v-else>
                        {{ groupNames[groupBy][groupIndex] }} ({{ knownKanjiCounts[groupIndex] === undefined ? 0 : knownKanjiCounts[groupIndex].total }}/{{ totalKanjiCounts[groupIndex].total }})
                    </template>
                </div>
                <v-card outlined class="d-flex flex-wrap rounded-lg" v-if="group.length">
                    <v-card-text>
                        <template v-for="(kanji, kanjiIndex) in group">
                            <v-btn
                                icon
                                :class="{'kanji': true, 'pa-0': true,  'learned': kanji.known}"
                                :key="groupIndex + '-' + kanjiIndex"
                                v-if="showUnknown || kanji.known"
                                :to="'/kanji/' + kanji.kanji"
                            >
                                {{ kanji.kanji }}
                            </v-btn>
                        </template>
                    </v-card-text>
                </v-card>
            </template>
        </div>
    </v-container>
</template>

<script>
    export default {
        data: function() {
            return {
                groupBy: 0,
                showUnknown: false,
                loading: true,
                kanji: [],
                knownKanjiCounts: [],
                totalKanjiCounts: [],
                filteredKanji: [],
                groupNames: [
                    {
                        0: 'No grade',
                        1: 'Grade 1',
                        2: 'Grade 2',
                        3: 'Grade 3',
                        4: 'Grade 4',
                        5: 'Grade 5',
                        6: 'Grade 6',
                        8: 'Junior highschool',
                        10: 'Jinmeiyou kanji'
                    },
                    {
                        0: 'Not in JLPT',
                        1: 'JLPT N1',
                        2: 'JLPT N2/N3',
                        4: 'JLPT N4',
                        5: 'JLPT N5'
                    }
                ]
            }
        },
        props: {
            
        },
        mounted() {
            this.updateKanjiList();
        },
        methods: {
            updateKanjiList: function() {
                this.loading = true;
                axios.post('/kanji/search', {
                    groupBy: this.groupBy == 0 ? 'grade' : 'jlpt',
                    showUnknown: this.showUnknown,
                }).then((response) => {
                    this.kanji = response.data.kanji;
                    this.knownKanjiCounts = response.data.known;
                    this.totalKanjiCounts = response.data.total;

                    this.loading = false;
                }).catch((error) => {
                    this.loading = false;
                });            
            }
        }
    }
</script>
