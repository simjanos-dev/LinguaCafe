<template>
    <v-container id="home" class="pb-12">
        <calendar 
            ref="calendar"
            @achievement-quantity-change="updateGoals"
        ></calendar>
        <goals
            ref="goals"
            @goal-quantity-change="updateCalendar"
        ></goals>
        <statistics
            ref="statistics"
        ></statistics>
    </v-container>
</template>


<script>
    import {formatNumber} from './../../helper.js';
    const moment = require('moment');
    export default {
        data: function() {
            return {
                theme: (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme'),
            }
        },
        props: {
            
        },
        mounted() {
        },
        methods: {
            updateCalendar() {
                this.$refs.calendar.loadCalendarData();
                this.$refs.statistics.loadStatistics();
            },
            updateGoals() {
                this.$refs.goals.loadGoals();
            },
            formatNumber: formatNumber,
        }
    }
</script>
