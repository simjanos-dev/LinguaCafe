<template>
    <v-container>
        <div class="subheader">
            Patch notes
        </div>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.12<v-spacer /> 2024.05.17.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Docker installation and update process has been improved. It does not require git and chmod commands anymore (except once for already existing users).
                    </li>
                    <li>
                        Added book length to the library list view.
                    </li>
                    <li>
                        Added word inflections table for Japanese.
                    </li>
                    <li>
                        Added text to speech option for the text reader.
                    </li>
                    <li>
                        Added bottom sheet vocabulary for mobile view.
                    </li>
                    <li>
                        Added progressive web app support. Now users can use LinguaCafe as a full screen mobile app.
                    </li>
                    <li>
                        Added font type management system.
                    </li>
                </ul>

                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Added missing kanji images. Now users can see the kanji stroke order and writing animation on the kanji info pages.
                    </li>
                    <li>
                        Book word counts now update after importing something.
                    </li>
                    <li>
                        Hover vocabulary now disappears when the user moves their mouse pointer to empty space.
                    </li>
                    <li>
                        Importing texts now skips too long words instead of throwing an error.
                    </li>
                    <li>
                        Importing dictionaries now skips records that contain a word or a definition that is too long instead of throwing an error.
                    </li>
                    <li>
                        Fixed highlighted word colors that were displayed incorrectly at several places.
                    </li>
                    <li>
                        Interactive text is not selectable anymore on the review page natively like other texts in the browser.
                    </li>
                    <li>
                        Previous example sentences are not visible anymore on the review page while the new one is loading.
                    </li>
                    <li>
                        Users cannot open a chapter in the text reader anymore that does not belong to their selected language.
                    </li>
                    <li>
                        JMDict's source language is not editable anymore.
                    </li>
                    <li>
                        Copying a selected word with ctrl+c won't set the word's level to new anymore.
                    </li>
                    <li>
                        It is now impossible to create a phrase without any words, which would corrupt the database.
                    </li>
                    <li>
                        Fixed light theme's warning color, because it was too yellow and lacking in contrast. For devices that already opened LinguaCafe, users must click on the reset color button to update it.
                    </li>
                    <li>
                        Fixed plain text mode copy-paste in Firefox.
                    </li>
                    <li>
                        Symbols won't be combined anymore with words in Japanese. This only applies to texts that are imported after this update.
                    </li>
                    <li>
                        Fixed a bug that caused text importing to fail in Japanese, if the last word was combined with the previous word during post processing.
                    </li>
                    <li>
                        Polish language had a font type that displayed incorrect letters.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        The import dialog won't close by itself anymore after importing has finished.
                    </li>
                    <li>
                        Laravel now produces more detailed logs, and logs both to the log file and the docker terminal.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.11.3 <v-spacer /> 2024.05.08.</v-card-title>
            <v-card-text>
                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Fixed a bug that caused practice review mode to be always turned off.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.11.2 <v-spacer /> 2024.04.27.</v-card-title>
            <v-card-text>
                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Fixed annoying multi word text selection bug.
                    </li>
                    <li>
                        Fixed furigana.
                    </li>
                    <li>
                        Fixed plain text mode.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.11 <v-spacer /> 2024.04.25.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Language installation. Some languages have been removed from the docker image to reduce its size, and became installable on the admin page.
                    </li>
                    <li>
                        Added new languages: Croatian, Danish, Macedonian, Polish, Portuguese, Romanian, Slovenian, Thai and Turkish.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        The default language has been changed to Spanish.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.10.1 <v-spacer /> 2024.04.21.</v-card-title>
            <v-card-text>
                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Fixed a bug that disabled some hotkeys.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.10 <v-spacer /> 2024.04.19.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Added a new default list layout for the library page.
                    </li>
                    <li>
                        Added a logout confirmation dialog.
                    </li>
                    <li>
                        Added theme customization.
                    </li>
                    <li>
                        Added different background colors for different word levels.
                    </li>
                    <li>
                        Added a separate color for ignored words' text color.
                    </li>
                    <li>
                        Added a hotkey to set words' level back to new.
                    </li>
                </ul>

                <b>Bug fixes:</b>
                <ul>
                    <li>
                        There will be no error message anymore due to empty book name when you click on "Create book" button.
                    </li>
                    <li>
                        While creating a book, the save button won't be grayed out anymore if you type in a name. 
                        Previously it only turned active after you removed the focus from the name input field.
                    </li>
                    <li>
                        While creating or editing a book and uploading an image, clicking on the cancel button will now 
                        revert the image to the default or previous image, instead of showing an error message.
                    </li>
                    <li>
                        Fixed a bug that caused words imported on the vocabulary page to not be added to the review system.
                    </li>
                    <li>
                        Pressing the enter key when creating or editing a book or a chapter now will act the same as clicking on the Save button. 
                        Pressing the enter key when importing something now will act as the Continue or Import button.
                    </li>
                    <li>
                        Fixed a bug that caused hover vocabulary box to appear even when plain text mode was active 
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        Improved dictionary search UI: made dictionary colors less distracting and vocabulary sidebar more compact and uniform.
                    </li>
                    <li>
                        The online user manual has been moved from the GitHub Page to the GitHub Wiki. It also moved from a Vuetify format to Markdown.
                    </li>
                    <li>
                        DeepL API key error will not appear on the admin page anymore if it set to the default value.
                    </li>
                    <li>
                        Added a warning on how hover vocabulary box works in review settings.
                    </li>
                    <li>
                        Lightened default dark mode text color to make it more readable.
                    </li>
                    <li>
                        Made navigation sidebar more compact.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.9 <v-spacer /> 2024.03.28.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Added new supported languages: English, Greek and Latin.
                    </li>
                    <li>
                        Made text editable when importing a website.
                    </li>
                    <li>
                        Added settings dialog for the review page.
                    </li>
                    <li>
                        Expanded example sentence mode on the review page. Now it has 3 options: disabled, plain text and interactive text.
                    </li>
                    <li>
                        Added hover vocabulary on the review page.
                    </li>
                    <li>
                        Added dictionary edit dialog for the admin page.
                    </li>
                    <li>
                        DeepL translator's target language now can be changed.
                    </li>
                    <li>
                        Added support for HanDeDict dictionary.
                    </li>
                    <li>
                        Added support for every dict cc dictionary. Now you can import multiple of them for the same language.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        MacOS users have a new, simpler install and update process.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.8 <v-spacer /> 2024.03.21.</v-card-title>
            <v-card-text>
                <b>New features:</b>
                <ul>
                    <li>
                        Added "website" import option.
                    </li>
                    <li>
                        Added vocabulary word import from .csv.
                    </li>
                    <li>
                        Added start review dialog for the review button in the navigation sidebar.
                    </li>
                    <li>
                        Added review finished information cards, instead of redirecting the user to the homepage.
                    </li>
                    <li>
                        Word counts now load automatically when you open a book.
                    </li>
                    <li>
                        Added a setting for hover vocabulary's delay.
                    </li>
                    <li>
                        Added user manual. Will be extended and updated regularly.
                    </li>
                </ul>

                <b>Bug fixes:</b>
                <ul>
                    <li>
                        Review cards' content overflowed the buttons.
                    </li>
                    <li>
                        Books with long titles hid their open button.
                    </li>
                    <li>
                        Daily read words goal was not updating while reviewing phrases if the sentence mode was not selected.
                    </li>
                    <li>
                        Vocabulary sidebar had a minor visual problem with its edges.
                    </li>
                    <li>
                        Text reader had an error if the opened chapter had no text.
                    </li>
                    <li>
                        Renamed label "stage" to "level" on vocabulary page to make it consistent.
                    </li>
                    <li>
                        Non Chinese and Japanese phrases were only searchable on the vocabulary page without spaces. This fix only applies to phrases created after this update.
                    </li>
                    <li>
                        Non Chinese and Japanese phrases were displayed incorrectly on the vocabulary sidebar.
                    </li>
                    <li>
                        Phrases were displayed incorrectly on the vocabulary export dialog.
                    </li>
                    <li>
                        Reading fields were visible on vocabulary search page's edit dialog for languages that had no readings.
                    </li>
                    <li>
                        Numpad hotkeys were not working.
                    </li>
                    <li>
                        Hover vocabulary's search dictionary setting was not working when it was turned off.
                    </li>
                    <li>
                        Hover vocabulary sent a request to the server even if you took the mouse off from the hovered word before the delay ended.
                    </li>
                </ul>

                <b>Other changes:</b>
                <ul>
                    <li>
                        If DeepL is disabled for a language, the dictionaries will not show a DeepL error message anymore, 
                        and they also won't make a request to retrieve the DeepL translations.
                    </li>
                    <li>
                        For better performance and to avoid accidentaly opening hover vocabulary on words, a slight delay was added before 
                        the hover vocabulary box becomes visible. The dictionary and DeepL searches already had this delay, it just has 
                        been applied to the visibility of the hover vocabulary box as well.
                    </li>
                </ul>
            </v-card-text>
        </v-card>

        <v-card outlined class="rounded-lg mb-8">
            <v-card-title>v0.7 <v-spacer /> 2024.02.14.</v-card-title>
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
                    <li>
                        Added windows installation guide and installation file.
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
                    <li>
                        Fixed an issue that caused long chapter titles to hide the text.
                    </li>
                    <li>
                        Furigana now displays the editable reading field. Previously it displayed the reading that was assigned to the exact word in text while importing it.
                    </li>
                    <li>
                        Fixed a visual issue where the day text was not visible in the calendar while using dark theme.
                    </li>
                    <li>
                        Added missing lemma to review card when using example sentence mode.
                    </li>
                    <li>
                        Removed furigana from e-book imports.
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
