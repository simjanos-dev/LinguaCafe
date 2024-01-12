<template>
    <v-container>
        <div class="subheader">
            Patch notes
        </div>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0003 <v-spacer /> 2024.01.12.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Added partial support for new languages: Russian, Swedish and Ukrainian.
                    </li>
                    <li>
                        Docker installation and update process has been reworked and made simpler.
                    </li>
                    <li>
                        The "Highlight words" option in the text and subtitle reader settings dialog has been split into 2 options: 
                        "Hide all highlighting" and "Hide new word highlighting". This makes it possible to hide only the 
                        yellow new word highlighting.
                    </li>
                </ul>

                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Fixed all issues with "Plain text mode".
                    </li>
                    <li>Fixed a bug that caused words to only have top and bottom border without background when "Highlight words" option was turned off, 
                        and the user hovered over them.
                    </li>
                    <li>
                        Missing toolbar titles have been added to text reader.
                    </li>
                    <li>
                        There was a non-standard scroll function used that caused issues in some browsers. 
                        It has been fixed, and it was added as an option for text and subtitle reader.
                    </li>
                    <li>
                        An issue has been fixed that caused white space characters being displayed as clickable words 
                        on the text reader page, and it broke the software if the user clicked on them.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        The "/storage/app/dictionaries" folder has been created in the repository, and won't have to be created manually.
                    </li>
                    <li>
                        All tokenizer models have been replaced with smaller sized ones due to possible performance issues. They will be replaced 
                        with larger models for specific languages if it causes word tagging accuracy issues.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0002 <v-spacer /> 2024.01.09.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Added partial support for new languages: Chinese, Dutch, Finnish, French, Italian and Korean.
                    </li>
                </ul>

                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Fixed a bug that caused DeepL translator to handle all languages as Norwegian.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        The selected language is displayed as capitalized text.
                    </li>
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
