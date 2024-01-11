<template>
    <v-container>
        <div class="subheader">
            Patch notes
        </div>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0002</v-card-title>
            <v-card-text>
                New features:
                <ul>
                    <li>Added partial support for new languages: Chinese, Dutch, Finnish, French, Italian, Korean, Swedish, Ukrainian, Russian.</li>
                </ul>

                Bug fixes:
                <ul>
                    <li>Fixed a bug that caused DeepL translator to handle all languages as Norwegian.</li>
                </ul>

                Other changes:
                <ul>
                    <li>The selected language is displayed as capitalized text.</li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0001</v-card-title>
            <v-card-text>
                Release.
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script>
    export default {
        data: function() {
            return {
            }
        },
        props: {
        },
        mounted() {
        },
        methods: {
        }
    }
</script>
