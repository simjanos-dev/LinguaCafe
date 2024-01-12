<template>
    <div>
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
            
            <div class="subheader subheader-margin-top d-flex">
                About
            </div>

            <div id="about" class="d-flex flex-wrap">
                <v-card outlined class="rounded-lg pt-0 mr-4 mb-4" width="290px">
                    <v-card-title>Developement</v-card-title>
                    <v-card-text>
                        You can find more information about LinguaCafe's developement on these links.
                        <div class="footer-link-box mb-1 mt-4">
                            <router-link to="/attributions"><v-icon class="mr-2">mdi-copyright</v-icon>Attributions</router-link>
                        </div>
                        <div class="footer-link-box mb-1">
                            <a href="https://simjanos-dev.github.io/LinguaCafeHome/"><v-icon class="mr-2">mdi-file-document</v-icon>Overview</a>
                        </div>
                        <div class="footer-link-box mb-1">
                            <a href="https://github.com/simjanos-dev/LinguaCafe"><v-icon class="mr-2">mdi-github</v-icon>Github</a>
                        </div>
                        <div class="footer-link-box mb-1">
                            <a href="https://www.reddit.com/r/linguacafe/"><v-icon class="mr-2">mdi-reddit</v-icon>Reddit</a>
                        </div>
                    </v-card-text>
                </v-card>

                <v-card outlined class="rounded-lg pt-0 mr-4 mb-4" width="290px">
                    <v-card-title>Contact</v-card-title>
                    <v-card-text>
                        You can contact the developer of LinguaCafe on these platforms.
                        <div class="footer-link-box mb-1 mt-4">
                            <a href="https://discord.gg/SuJqqA5d"><v-icon class="mr-2">mdi-message-text</v-icon>Discord server invite</a>
                        </div>
                        <div class="footer-link-box mb-1 primary--text">
                            <v-icon class="mr-2">mdi-message-text</v-icon>Discord: linguacafe_47757
                        </div>
                        
                        <div class="footer-link-box mb-1">
                            <a href="https://www.reddit.com/u/linguacafe/"><v-icon class="mr-2">mdi-reddit</v-icon>Reddit</a>
                        </div>
                        <div class="footer-link-box mb-1 primary--text">
                            <v-icon class="mr-2">mdi-email</v-icon>E-mail: simjanos.dev@gmail.com
                        </div>
                    </v-card-text>
                </v-card>

                <v-card outlined class="rounded-lg pt-0 mr-4 mb-4" width="290px">
                    <v-card-title>Version</v-card-title>
                    <v-card-text>
                        The current LinguaCafe version is V0003.
                        <div class="footer-link-box mb-1 mt-4">
                            <router-link to="/patch-notes"><v-icon class="mr-2">mdi-update</v-icon>Patch notes</router-link>
                        </div>
                    </v-card-text>
                </v-card>
            </div>
        </v-container>
    </div>
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
