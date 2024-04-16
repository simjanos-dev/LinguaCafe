<template>
    <div id="admin-review-settings">
        
        <!-- SRS info -->
        <div class="subheader mt-4">Spaced repetition system</div>
        <v-alert dark border="left" type="info" color="primary" class="mt-2 mb-4">
            Numbers represent how many days later words will be reviewed again 
            after a review or a manual level change.<br><br>

            You can set multiple numbers for a single level by typing in numbers delimited by a comma. 
            In this case the next review date will be selected based on which day has the least amount of 
            reviews scheduled at the time of the level change.
        </v-alert>

        <!-- SRS settings -->
        <v-card outlined class="rounded-lg" :loading="!reviewIntervals.length">
            <v-card-text>
                <label class="font-weight-bold mt-4">SRS settings</label>

                <v-simple-table dense class="no-hover no-lines">
                    <tbody>
                        <tr v-for="(interval, index) in reviewIntervals" :key="index">
                            <td class="pt-4">
                                Level {{ interval.name }}:
                            </td>
                            <td class="pt-4">
                                <v-text-field 
                                    v-model="interval.values" 
                                    filled 
                                    rounded 
                                    dense 
                                    hide-details 
                                    :disabled="!index"
                                    @change="reviewIntervalChanged($event, index)" 
                                />
                            </td>
                        </tr>
                    </tbody>
                </v-simple-table>
            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn 
                    rounded 
                    depressed 
                    color="primary"
                    :disabled="!reviewIntervals.length || saving"
                    :loading="saving"
                    @click="saveSettings"
                >
                    Save
                </v-btn>
            </v-card-actions>
        </v-card>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                saving: false,
                saveStatus: '',
                reviewIntervals: [],
            }
        },
        props: {
            language: String
        },
        mounted() {
            this.loadSettings();
        },
        methods: {
            reviewIntervalChanged(value, index) {
                // split value
                let intervals = [1];
                if (value.length) {
                    intervals = value.split(',');
                }

                // parse numbers and restrict undesired values
                for (let intervalIndex = 0; intervalIndex < intervals.length; intervalIndex++) {
                    let parsedInterval = parseInt(intervals[intervalIndex]);
                    intervals[intervalIndex] = isNaN(parsedInterval) ? 1 : parsedInterval;
                    
                    if (intervals[intervalIndex] > 3650) {
                        intervals[intervalIndex] = 3650;
                    }

                    if (intervals[intervalIndex] < 1) {
                        intervals[intervalIndex] = 1;
                    }
                }

                this.reviewIntervals[index].name = (7 - index) + '';
                this.reviewIntervals[index].values = intervals.join(',');

                this.$nextTick(() => {
                    this.$forceUpdate();
                });
            },
            saveSettings() {
                this.saving = true;


                let reviewIntervalsArray = {};
                for (let intervalIndex = 0; intervalIndex < this.reviewIntervals.length; intervalIndex++) {
                    let key = (parseInt(this.reviewIntervals[intervalIndex].name) * -1);
                    reviewIntervalsArray[key] = this.reviewIntervals[intervalIndex].values.split(',');
                    reviewIntervalsArray[key] = reviewIntervalsArray[key].map(Number);
                }
                
                axios.post('/settings/global/update', {
                    'settings': {
                        'reviewIntervals': reviewIntervalsArray,
                    }
                }).then(() => {
                    this.reviewIntervals = [];
                    this.loadSettings();
                });
            },
            loadSettings() {
                axios.post('/settings/global/get', {
                    'settingNames': ['reviewIntervals']
                }).then((result) => {
                    Object.keys(result.data.reviewIntervals).forEach((key, index) => {
                        this.reviewIntervals.push({
                            name: (key * -1) + '',
                            values: result.data.reviewIntervals[key].join(',')
                        });
                    });
                    
                    this.saving = false;
                    this.$forceUpdate();
                });
            }
        }
    }
</script>
 