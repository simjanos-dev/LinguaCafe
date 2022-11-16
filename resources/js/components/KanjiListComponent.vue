<template>
    <div id="kanji-list">
        <v-container>
            <v-tabs class="mb-10" background-color="background" v-model="groupBy" @change="updateKanjiList">
                <v-tab>Grade</v-tab>
                <v-tab>JLPT</v-tab>
                <v-spacer></v-spacer>
                <v-btn class="px-2" plain @click="showUnknown = !showUnknown; updateKanjiList();" v-if="showUnknown">Hide unknown <v-icon class="ml-1">mdi-eye-off</v-icon></v-btn>
                <v-btn class="px-2" plain @click="showUnknown = !showUnknown; updateKanjiList();" v-if="!showUnknown">Show unknown <v-icon class="ml-1">mdi-eye</v-icon></v-btn>
            </v-tabs>
            <v-row>
                <!-- Skeleton loader -->
                <template v-for="groupIndex in 3" v-if="loading">
                    <v-skeleton-loader
                        class="skeleton-title ml-1 my-5 mb-2"
                        type="image"
                    ></v-skeleton-loader>

                    <div class="d-flex flex-wrap">
                        <template v-for="kanjiIndex in 24 + ((groupIndex + 1) * 20)">
                            <v-skeleton-loader
                                class="skeleton-button"
                                type="image"
                            ></v-skeleton-loader>
                        </template>
                    </div>
                </template>

                <!-- JLPT info -->
                <v-alert width="100%" class="my-5" dark type="info" border="left" v-if="groupBy == 1 && !loading">
                    The JLPT data is from the previous 4 level system, which was changed in 2010. There is no official kanji list for the current JLPT.
                    The old levels are similar to the current ones, except that the old N2 is now divided between N2 and N3.
                </v-alert>
                
                <!-- Kanji List -->
                <template v-for="(group, groupIndex) in kanji" v-if="!loading">
                    <div class="subheader mt-10 pl-1 h5" v-if="group.length">
                        <template v-if="groupIndex == 0">
                            {{ groupNames[groupBy][groupIndex] }} ({{ knownKanjiCounts[groupIndex].total }})
                        </template>
                        <template v-else>
                            {{ groupNames[groupBy][groupIndex] }} ({{ knownKanjiCounts[groupIndex].total }}/{{ totalKanjiCounts[groupIndex].total }})
                        </template>
                    </div>
                    <div class="d-flex flex-wrap" v-if="group.length">
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
                    </div>
                </template>
            </v-row>
        </v-container>
    </div>
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
                }).then(function (response) {
                    console.log(response.data);
                    this.kanji = response.data.kanji;
                    this.knownKanjiCounts = response.data.known;
                    this.totalKanjiCounts = response.data.total;

                    this.loading = false;
                }.bind(this)).catch(function (error) {
                    this.loading = false;
                }).then(function () {
                });            
            }
        }
    }
</script>
