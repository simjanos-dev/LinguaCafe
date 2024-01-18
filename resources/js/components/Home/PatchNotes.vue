<template>
    <v-container>
        <div class="subheader">
            Patch notes
        </div>
        
        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.5 <v-spacer /> 2024.??.??.</v-card-title>
            <v-card-text>
                There were several issues with Jellyfin integration. Now it should work properly with every language and video type. 
                Added additional information about subtitle file naming to the readme file. <br><br>

                <b>New features:</b>
                <ul>
                    <li>
                        Added support for new languages: Czech and Welsh.
                    </li>
                    <li>
                        Added Kengdic dictionary support for Korean.
                    </li>
                    <li>
                        Added CC-CEDICT dictionary support for Chinese.
                    </li>
                    <li>
                        Added Eurfa dictionary support for Welsh.
                    </li>
                    <li>
                        Added pinyin for Chinese.
                    </li>
                </ul>

                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Removed empty and duplicated records from dictionary search results.
                    </li>
                    <li>
                        Added Chinese font type. Now every Chinese and Japanese text should be displayed with NotoSansSC and NotoSansJP font types.
                    </li>
                    <li>
                        Removed word spacing from Chinese texts.
                    </li>
                    <li>
                        Removed '+' symbol from Korean lemmas.
                    </li>
                    <li>
                        Removed 'die/der/das' from the beginning of German search terms for better results.
                    </li>
                    <li>
                        Fixed an issue with the Media player that only allowed TV Show subtitles to be displayed from Jellyfin. Now every video type is playable.
                    </li>
                    <li>
                        Fixed an issue with the Media player that caused every language to be handled as Japanese.
                    </li>
                    <li>
                        Fixed an issue related to Media player missing language support. Now all languages work with it.
                    </li>
                    <li>
                        Fixed an issue on the vocabulary search page, that caused phrases to be displayed with commas 
                        between the words, or with no spaces between them. It will still need further improvement for 
                        non Japanese and Chinese languages.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        Removed all online dependencies.
                    </li>
                    <li>
                        Updated language selection dialog design, and the supported languages information in the readme.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.4 <v-spacer /> 2024.01.15.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        German lemmatization for nouns and separable verbs have been improved. 
                    </li>
                    <li>
                        Importing dictionaries have been simplified, and has a new UI page to manage them.
                    </li>
                    <li>
                        Information popups for different features have been added.
                    </li>
                </ul>

                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Added missing toolbar titles on the review page.
                    </li>
                    <li>
                        Added missing dialog close buttons.
                    </li>
                    <li>
                        Auto generated lemmas sometimes had uppercase letters. It has been changed to only contain lowercase letters.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        Docker images are now published on the GitHub Container Registry, and there is a much simpler installation and update process.
                    </li>
                    <li>
                        Changed version number format.
                    </li>
                    <li>
                        Updated MySQL image to 8.0.
                    </li>
                </ul>
            </v-card-text>
        </v-card>
        
        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.3 <v-spacer /> 2024.01.12.</v-card-title>
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
            <v-card-title>v0.2 <v-spacer /> 2024.01.09.</v-card-title>
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
            <v-card-title>v0.1 <v-spacer /> 2024.01.07.</v-card-title>
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
