<template>
    <v-container>
        <div class="subheader">
            Patch notes
        </div>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0003</v-card-title>
            <v-card-text>
                New features:
                <ul>
                    <li>Docker installation and update process has been reworked and made easier.</li>
                    <li>The "Highlight words" option in the text and subtitle reader settings dialog has been separated to 2 options: 
                        "Hide all highlighting" and "Hide new word highlighting". This makes it possible to hide only the 
                        yellow new word highlighting.
                    </li>
                </ul>

                Bug fixes:
                <ul>
                    <li>Fixed all issues with "Plain text mode".</li>
                </ul>

                Other changes:
                <ul>
                    <li>The "/storage/app/dictionaries" folder has been created in the repository, and won't have to be created manually.</li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0002 <v-spacer /> 2024.01.09.</v-card-title>
            <v-card-text>
                New features:
                <ul>
                    <li>Added partial support for new languages: Chinese, Dutch, Finnish, French, Italian and Korean.</li>
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
            <v-card-title>v0001 <v-spacer /> 2024.01.07.</v-card-title>
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
