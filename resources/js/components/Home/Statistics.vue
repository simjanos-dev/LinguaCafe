<template>
    <div>
        <div class="subheader subheader-margin-top d-flex">
            Statistics
        </div>
        <div id="statistics">
            <v-card 
                outlined 
                class="statistic rounded-lg mr-4 mb-4 pa-4"
                v-for="(statistic, index) in statistics"
                :key="index"
            >
                <div class="statistic-icon">
                    <v-icon :color="$vuetify.theme.currentTheme[statistic.color]">{{ statistic.icon }}</v-icon>
                </div>

                <div class="statistic-data">
                    <div 
                        class="statistic-value" 
                        :style="{color: $vuetify.theme.currentTheme[statistic.color]}"
                    >
                        {{ formatNumber(statistic.value) }}
                    </div>
                    <div class="statistic-name">{{ statistic.name }}</div>
                </div>
            </v-card>
        </div>
    </div>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                statistics : [],
            }
        },
        props: {
        },
        mounted() {
            this.loadStatistics();
        },
        methods: {
            loadStatistics() {
                axios.post('/statistics/get').then((response) => {
                    this.statistics = response.data;
                });
            },
            formatNumber: formatNumber
        }
    }
</script>
