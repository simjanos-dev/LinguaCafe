<template>
    <div id="home">
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
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                statistics : null,
            }
        },
        props: {
            
        },
        mounted() {
            axios.post('/statistics').then(function (response) {
                this.statistics = response.data;
            }.bind(this)).catch(function (error) {
            }).then(function () {
            });            
        },
        methods: {
        }
    }
</script>
