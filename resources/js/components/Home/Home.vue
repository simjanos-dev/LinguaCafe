<template>
    <v-container id="home" class="pb-12">
        <change-password-dialog
            v-model="passwordChangeDialog"
            @password-changed="passwordChangeFinished"
        ></change-password-dialog>

        <template v-if="!passwordChanged">
            <div class="subheader subheader-margin-top d-flex">
                <v-icon large color="error" class="mr-2">mdi-alert</v-icon>Password change
            </div>

            <v-alert
                id="password-change-alert"
                class="rounded-lg mt-2"
                color="foreground"
                border="left"
            >
                Your account and password was created by an admin. Please change your password before continuing your language learning journey.

                <div class="d-flex mt-4">
                    <v-spacer />
                    <v-btn 
                        rounded 
                        depressed
                        color="error"
                        @click="passwordChangeDialog = true;"
                    >   
                        <v-icon class="mr-2">mdi-lock-reset</v-icon>
                        Change password
                    </v-btn>
                </div>
            </v-alert>
        </template>

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
                passwordChanged: true,
                passwordChangeDialog: false
            }
        },
        props: {
        },
        mounted() {
            axios.get('/user/is-password-changed').then((response) => {
                this.passwordChanged = Boolean(response.data);
            });
        },
        methods: {
            updateCalendar() {
                this.$refs.calendar.loadCalendarData();
                this.$refs.statistics.loadStatistics();
            },
            updateGoals() {
                this.$refs.goals.loadGoals();
            },
            passwordChangeFinished() {
                this.passwordChanged = true;
                this.passwordChangeDialog = false;
            },
            formatNumber: formatNumber,
        }
    }
</script>
