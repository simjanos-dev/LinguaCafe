<template>
    <v-container>
        <div class="subheader">
            Patch notes
        </div>
       
        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.7 <v-spacer /> 2024.02.??.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Replaced the python Django server with Bottle for better performance.
                    </li>
                    <li>
                        Added dynamic language model loading. Models are only going to be loaded at the first import for each language. This will significantly decrease memory use based on which and how many langauges are used on a server after its startup.
                    </li>
                    <li>
                        Added new "Plain text" import option.
                    </li>
                    <li>
                        Added new "Text file" import option.
                    </li>
                    <li>
                        Added new "Jellyfin subtitle" import option.
                    </li>
                    <li>
                        Added new "Subtitle file" import option.
                    </li>
                    <li>
                        Setting words' and phrases' level is now possible while hovering over them.
                    </li>
                    <li>
                        Added dictionary search for hover vocabulary box. 
                    </li>
                    <li>
                        Added DeepL search for hover vocabulary box. 
                    </li>
                    <li>
                        Separated DeepL search from regular dictionary search in the vocabulary box. Users won't have to wait for DeepL server's response to see the dictionary search results which load much faster.
                    </li>
                    <li>
                        Added loading indicator for vocabulary box dictionary search.
                    </li>
                    <li>
                        Added an option to automatically highlight a word when it gets a translation added to it. 
                    </li>
                    <li>
                        A list of words which were not counted in the statistics now will be automatically set to ignored when imported. Also added all numbers and more symbols to the list.
                    </li>
                    <li>
                        Vocabulary search page's edit dialog now will close on its own after saving it, and the search results will be updated automatically.
                    </li>
                    <li>
                        Added chapter length option to import dialog.
                    </li>
                    <li>
                        Added hotkeys for text scrolling.
                    </li>
                    <li>
                        The selected library layout will now be remembered.
                    </li>
                    <li>
                        Added option to change the default MySQL database and user.
                    </li>
                </ul>
                
                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Fixed a bug that caused the records on the vocabulary search page to appear in random order inside the specified order by parameter.
                    </li>
                    <li>
                        Fixed an issue in the text reader glossary, where empty parentheses were visible in languages that have no readings.
                    </li>
                    <li>
                        Fixed a visual issue where the scrollbars' background were a different color than the area they were placed on.
                    </li>
                </ul>
                
                <b>Other changes:</b>
                <ul>
                    <li>
                        Removed media player page, it has been replaced with Jellyfin subtitle import option.
                    </li>
                    <li>
                        Improved text selecting design. 
                    </li>
                    <li>
                        Removed unnecessary files from the webserver image and decreased its size.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.6 <v-spacer /> 2024.01.29.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Added an always open and fixed sidebar vocabulary as a default option for screens wider than 960px. It can be turned off in the text reader settings.
                    </li>
                    <li>
                        Added a minimalistic vocabulary box, that appears when the user moves the mouse over a word. It can be turned off in the text reader settings.
                    </li>
                    <li>
                        Added youtube subtitle import option.
                    </li>
                    <li>
                        Added hotkeys for the text reader and review pages.
                    </li>
                    <li>
                        Added proper finish reading screen.
                    </li>
                </ul>
                
                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Users clicking anywhere outside of the vocabulary box will close the vocabulary box. Previously it only worked if the user clicked on an empty space inside the text box.
                    </li>
                    <li>
                        Increased maximum execution time for importing dictionaries. If your dictionary import did not end with a success message but still functional, I recommend importing it again, because it probably did not import all the records from the dictionary file.
                    </li>
                </ul>
                
                <b>Other changes:</b>
                <ul>
                    <li>
                        Updated node.js to v20.11.0.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.5.2 <v-spacer /> 2024.01.23.</v-card-title>
            <v-card-text>
                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Fixed a problem with deployment, and added a simple migration guide.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.5.1 <v-spacer /> 2024.01.22.</v-card-title>
            <v-card-text>
                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Fixed a bug that prevented lemmas end lemma readings to be saved.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.5 <v-spacer /> 2024.01.21.</v-card-title>
            <v-card-text>
                The docker installation process has been improved since the last version. Fixed all the known issues with it, and 
                removed the requirement for users to create their folders manually and to modify the docker-compose.yml file.

                <br><br>
                There were several issues with Jellyfin integration. Now it should work properly with every language and video type. 
                Also added information about subtitle file naming to the readme file. <br><br>

                Lemma modifications only apply to new words that haven't been imported yet. There will be an option in the future, to 
                overwrite already existing lemmas.<br><br>

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
                        Added pinyin support for Chinese.
                    </li>
                    <li>
                        Added furigana support for Japanese.
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
                        Fixed an issue related to Media player missing language support, now all languages work with it.
                    </li>
                    <li>
                        Fixed an issue on the vocabulary search page, that caused phrases to be displayed with commas 
                        between the words, or with no spaces between them. It will still need further improvement for 
                        non Japanese and Chinese languages.
                    </li>
                    <li>
                        Fixed an error that broke the Kanji page if you had no known Kanji yet from every category.
                    </li>
                    <li>
                        Fixed dark theme style on color picker.
                    </li>
                    <li>
                        Added a missing label to text reader's settings dialog.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        Moved the dictionary search to the main section of the popup vocabulary.
                    </li>
                    <li>
                        Removed all online dependencies.
                    </li>
                    <li>
                        Updated language selection dialog design, and the supported languages information in the readme.
                    </li>
                    <li>
                        Changed chapter size from 9000 to 4000 characters for importing e-books due to performance issues. There will be an option
                        to edit this limit soon.
                    </li>
                    <li>
                        Unified text reader and subtitle reader settings. Users don't have to modify settings at both places separately anymore.
                    </li>
                    <li>
                        Added dynamic subtitle rendering for Media player, which increased performance significantly. For this I had to remove 
                        customizable spacing between subtitles, due to a bug that moved the vocabulary box around. This will be fixed in the
                        future.
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
