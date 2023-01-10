<template>
    <v-container id="home">
        <!--
        <table class="table table-bordered table-sm col-md-6 offset-md-3" v-if="this.statistics !== null">
            <thead></thead>
            <tbody>
                <tr>
                    <td>Days of reading:</td>
                    <td>{{ statistics.days_of_learning }}</td>
                </tr>
                <tr>
                    <td>Known words:</td>
                    <td>{{ statistics.learned }}</td>
                </tr>
                <tr>
                    <td>Words to review:</td>
                    <td>{{ statistics.words_to_review }} / {{ statistics.words_to_review_total }}</td>
                </tr>
                <tr>
                    <td>Read words:</td>
                    <td>{{ statistics.readWordCount }} ({{ statistics.readWordCountToday }})</td>
                </tr>                
                <tr v-if="statistics.kanjiCount !== undefined">
                    <td>Kanji:</td>
                    <td>{{ statistics.kanjiCount }}</td>
                </tr>
            </tbody>
        </table>
        -->
        
        <div class="subheader d-flex">
            Calendar

            <v-spacer></v-spacer>
            <v-btn icon class="calendar-title-button" @click="previousMonth">
                <v-icon>mdi-arrow-left</v-icon>
            </v-btn>
            <v-dialog
                v-model="showDatePicker"
                persistent
                width="290px"
                class="rounded-xl"
                content-class="date-picker-dialog"
            >
                <template v-slot:activator="{ on, attrs }">
                    <v-btn 
                        rounded
                        text
                        color="text"
                        @click="showDatePicker = true;"
                    >
                        <v-icon>mdi-calendar</v-icon>&nbsp;{{ pickerDateFormated }}
                    </v-btn>
                </template>
                <v-date-picker 
                    class="rounded-xl"
                    v-model="pickerDate"
                    scrollable
                    type="month"
                    :show-current="false"
                    color="primary"
                    header-color="primary"
                    @change="datePickerChanged"
                >
                    <v-spacer></v-spacer>
                    <v-btn text rounded @click="showDatePicker = false">Close</v-btn>
                </v-date-picker>
            </v-dialog>
            <v-btn icon class="calendar-title-button" @click="nextMonth">
                <v-icon>mdi-arrow-right</v-icon>
            </v-btn>
        </div>
        
        <div id="calendar" class="rounded-lg border ma-3 pa-2 mx-auto">
            <v-menu
                content-class="calendar-popup-menu rounded-lg pa-4"
                v-model="showCalendarMenu"
                absolute
                :close-on-click="false"
                :close-on-content-click="false"
                :position-x="calendarPopupX"
                :position-y="calendarPopupY"
                offset-y
            >
                <div id="calendar-popup-date" class="mb-4" v-if="calendarPopupDay">
                    <span>{{ calendarPopupDay.fullDate }}</span>
                    <v-spacer></v-spacer>
                    <span id="calendar-popup-reviews-due">
                        <v-icon>mdi-clock-outline</v-icon>
                        {{ calendarPopupDay.reviewsDue }}
                    </span>
                </div>
                <div id="calendar-popup-achievements" v-if="calendarPopupDay">
                    <v-simple-table dense class="no-row-border no-hover">
                        <tbody>
                            <tr v-for="(achievement, index) in calendarPopupAchievements" :key="index">
                                <td>{{ goalTexts[achievement.type] }}</td>
                                <td v-if="achievement.goalQuantity">{{ achievement.achievedQuantity }}/{{ achievement.goalQuantity }}</td>
                                <td v-if="!achievement.goalQuantity"> none </td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                </div>
            </v-menu>

            <div id="calendar-months">
                <div class="calendar-month" v-for="(month, index) in selectedMonths" :key="index">
                    <div class="calendar-month-title">{{ month.formattedString }}</div>
                    <div class="calendar-month-days">
                        <div 
                            :class="{
                                'calendar-day': true, 
                                'achievement': day.achievement !== null,
                            }"
                            @click.stop="openCalendarDayPopup($event, day)"
                            v-for="(day, dayIndex) in month.days" 
                            :key="index + '-' + dayIndex"
                        >
                            <div :class="{
                                'calendar-day-background': true,
                                'half-achievement': selectedGoal !== 'reviews_due' && day.achievement !== null && day.achievement.achievedQuantity < day.achievement.goalQuantity,
                                }"
                                v-if="day.achievement !== null || (selectedGoal == 'reviews_due' && day.reviewsDue)"
                                :style="{'background-color': goalColors[selectedGoal]}"
                            ></div>
                            <div class="calendar-day-text" v-if="selectedGoal !== 'reviews_due'">{{ day.day }}</div>
                            <div class="calendar-day-text" v-if="selectedGoal == 'reviews_due'">
                                {{ day.reviewsDue ? day.reviewsDue : '-' }}
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="calendar-buttons">
                <v-chip 
                    class="calendar-goal-button" 
                    :color="selectedGoal == 'reviews_due' ? goalColors[selectedGoal] : ''" 
                    @click="selectedGoal = 'reviews_due'; updateCalendar();"
                >
                    Reviews due
                </v-chip>
                <v-chip 
                    class="calendar-goal-button ml-1" 
                    :color="selectedGoal == 'review' ? goalColors[selectedGoal] : ''" 
                    @click="selectedGoal = 'review'; updateCalendar();"
                >
                    Reviews
                </v-chip>
                <v-chip 
                    class="calendar-goal-button mx-1" 
                    :color="selectedGoal == 'read_words' ? goalColors[selectedGoal] : ''" 
                    @click="selectedGoal = 'read_words'; updateCalendar();"
                >
                    Reading
                </v-chip>
                <v-chip 
                    class="calendar-goal-button" 
                    :color="selectedGoal == 'learn_words' ? goalColors[selectedGoal] : ''" 
                    @click="selectedGoal = 'learn_words'; updateCalendar();"
                >
                    New words
                </v-chip>
            </div>
        </div>

        <div class="subheader">Daily goals</div>
        <div id="goals">
            <goal-component 
                v-for="(goal, index) in goals" :key="index"
                :type="goal.type"
                :goal-quantity="goal.quantity"
                :todays-achieved-quantity="goal.todaysQuantity"
                :color="goalColors[goal.type]"
                >
            </goal-component>
        </div>
    </v-container>
</template>

<script>
    const moment = require('moment');
    export default {
        data: function() {
            return {
                statistics : null,
                
                // goal data
                goals: [],
                goalColors: {
                    'review': this.$vuetify.theme.currentTheme.dailyGoalReview,
                    'read_words': this.$vuetify.theme.currentTheme.dailyGoalReading,
                    'learn_words': this.$vuetify.theme.currentTheme.dailyGoalNew,
                    'reviews_due': '#ffc68c',
                },
                goalTexts: {
                    'review': 'reviews',
                    'read_words': 'words',
                    'learn_words': 'new words',
                },

                // calendar data
                showReviewsDue: false,
                calendarData: [],
                reviewsDue: [],
                pickerDate: new moment().format('YYYY-MM'),
                pickerDateFormated: '',
                currentMonth: new moment().startOf('month'),
                selectedMonths: [],
                selectedGoal: 'read_words',
                showDatePicker: false,
                showCalendarMenu: false,
                calendarPopupX: 0,
                calendarPopupY: 0,
                calendarPopupDay: null,
                calendarPopupAchievements: [],
            }
        },
        props: {
            
        },
        mounted() {
            document.body.addEventListener("click", this.closeCalendarDayPopup);
            //load statistics data
            axios.post('/statistics/get').then(function (response) {
                this.statistics = response.data;
            }.bind(this)).catch(function (error) {}).then(function () {});

            // load goals data
            axios.post('/goals/get').then(function (response) {
                this.goals = response.data;
            }.bind(this)).catch(function (error) {}).then(function () {});

            // load achievements data
            axios.post('/get-calendar-data').then(function (response) {
                this.calendarData = response.data;
                console.log(this.calendarData);
                // create calendar
                this.updateCalendar();
            }.bind(this)).catch(function (error) {}).then(function () {});
            
            this.datePickerChanged();
        },
        methods: {
            openCalendarDayPopup: function(event, day) {
                var position = event.target.getBoundingClientRect();
                
                // close calendar if the user clicked on the already selected word
                if (this.showCalendarMenu && 
                    this.calendarPopupX == position.left - 100 &&
                    this.calendarPopupY == position.bottom + 5) {
                    this.showCalendarMenu = false;
                    return;
                }
                
                // display calendar popup
                this.calendarPopupDay = JSON.parse(JSON.stringify(day));
                this.showCalendarMenu = false;
                this.calendarPopupX = position.left - 100;
                this.calendarPopupY = position.bottom + 5;

                // get achievements for popup
                this.calendarPopupAchievements = [];
                for (var i = 0; i < this.calendarData.length; i++) {
                    //console.log(this.calendarData[i].day, this.calendarPopupDay.fullDate);
                    if (this.calendarData[i].day == this.calendarPopupDay.fullDate) {
                        this.calendarPopupAchievements = JSON.parse(JSON.stringify(this.calendarData[i].achievements));
                        break;
                    }
                }

                // add default goals to calendar (reading, reviews, new words)
                var defaultGoalTypes = ['read_words', 'review', 'learn_words'];
                for (var i = 0; i < defaultGoalTypes.length; i++) {
                    if (this.calendarPopupAchievements.find(o => o.type == defaultGoalTypes[i]) === undefined) {
                        this.calendarPopupAchievements.push({
                            type: defaultGoalTypes[i],
                            goalQuantity: 0,
                            achievedQuantity: 0
                        });
                    }
                }

                this.calendarPopupAchievements.sort((a, b) => defaultGoalTypes.indexOf(a.type) - defaultGoalTypes.indexOf(b.type));


                this.$nextTick(() => {
                    this.showCalendarMenu = true;
                });
                
            },
            closeCalendarDayPopup: function() {
                this.showCalendarMenu = false;
            },
            datePickerChanged: function() {
                this.pickerDateFormated = new moment(this.pickerDate).format('YYYY, MMMM');
                this.currentMonth = moment(this.pickerDate).startOf('month');
                this.updateCalendar();
                this.showDatePicker = false;
            },
            nextMonth: function() {
                this.currentMonth.add(1, 'month').startOf('month');
                this.pickerDate = new moment(this.currentMonth).format('YYYY-MM');
                this.pickerDateFormated = moment(this.pickerDate).format('YYYY, MMMM');
                this.updateCalendar();
            },
            previousMonth: function() {
                this.currentMonth.subtract(1, 'month').startOf('month');
                this.pickerDate = new moment(this.currentMonth).format('YYYY-MM');
                this.pickerDateFormated = moment(this.pickerDate).format('YYYY, MMMM');
                this.updateCalendar();
            },
            updateCalendar: function () {
                this.selectedMonths = null;
                this.selectedMonths = [];
                
                // loop through days of the month
                var currentDate = new moment(this.currentMonth.format('YYYY-MM-DD')).startOf('month').subtract(4, 'months');
                var lastDay = new moment(this.currentMonth.format('YYYY-MM-DD')).endOf('month');
                while (currentDate.isBefore(lastDay)) {
                    if (!this.selectedMonths.length || this.selectedMonths[this.selectedMonths.length - 1].formattedString !== currentDate.format('YYYY, MMMM')) {
                        this.selectedMonths.push({
                            formattedString: currentDate.format('YYYY, MMMM'),
                            days: [],
                        });
                    }

                    var day = {
                        day: currentDate.format('D'),
                        dayWithLeadingZeros: currentDate.format('DD'),
                        fullDate: currentDate.format('YYYY-MM-DD'),
                        achievement: null,
                        reviewsDue: 0,
                    };
                    
                    // add achievements for the day
                    for (var i = 0; i < this.calendarData.length; i++) {

                        if (this.calendarData[i].day !== currentDate.format('YYYY-MM-DD')) {
                            continue;
                        }

                        day.reviewsDue = this.calendarData[i].reviewsDue;
                        for (var j = 0; j < this.calendarData[i].achievements.length; j++){
                            if (this.selectedGoal == this.calendarData[i].achievements[j].type) {
                                day.achievement = {
                                    name: this.calendarData[i].achievements[j].name,
                                    type: this.calendarData[i].achievements[j].type,
                                    achievedQuantity: this.calendarData[i].achievements[j].achievedQuantity,
                                    goalQuantity: this.calendarData[i].achievements[j].goalQuantity,
                                }
                            }
                        }
                    }
                    
                    this.selectedMonths[this.selectedMonths.length - 1].days.push(day);
                    currentDate.add(1, 'days');
                }

                this.selectedMonths.reverse();
            },
        }
    }
</script>
