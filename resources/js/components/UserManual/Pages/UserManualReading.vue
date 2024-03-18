<template>
    <div id="user-manual-reading" class="ma-4 mb-16">
        <!-- Library -->
        <div class="subheader d-flex">Library</div>
        <v-card outlined class="rounded-lg pa-4">
            In LinguaCafe all the texts are arranged into <b>books</b> and <b>chapters</b>. It is just an arbitrary name for them, they can be anything: 
            books, news articles, podcast transcripts or movie subtitles. You can import and manage your <b>books</b> on the <b>Library</b> page. 
        </v-card>

        <!-- Reading -->
        <div class="subheader subheader-margin-top d-flex">Reading</div>
        <v-card outlined class="rounded-lg pa-4">
            When you first start reading a text, all your words will be yellow, which represents new words that you haven't seen before. Click on the words 
            that you are not familiar with, and select a translation from the dictionary. These words' level will be set to 7, and they will 
            become green, which represents words that you are currently learning. These words will be highlighted in any text that is imported into your library.

            <div class="mt-5">
                Every word has a level, which represents how well you know that word. The closer it is to 0, the better you know it. A word's level can be set manually, 
                or it will increase automatically by <b>reviewing</b> that word, and eventually it's level will be set to <b>learned</b>.
            </div>

            <div class="mt-5">
                You can also select multiple words and create <b>phrases</b>. A <b>phrase</b> functions the same way as a word would.
            </div>

            <div class="mt-5">
                Click on the <b>Finished reading</b> button when you read the whole <b>chapter</b>. It will set all the new words in the <b>chapter</b> to known,
                and update your statistics.
            </div>
        </v-card>

        <!-- Hover vocabulary box -->
        <div class="subheader subheader-margin-top d-flex">Hover vocabulary box</div>
        <v-card outlined class="rounded-lg pa-4">
            <b>Hover vocabulary box</b> is a popup box that appears when you move the cursor over a word. This is the quickest way to retreive translations for a word or phrase.
            
            <div class="mt-5">
                It shows 3 types of translations:
                <ul>
                    <li>
                        <v-icon small class="mr-1">mdi-account-edit</v-icon> Translations you added manually.
                    </li>
                    <li>
                        <v-icon small class="mr-1">mdi-list-box</v-icon> Exact matches from every enabled imported dictionary.
                    </li>
                    <li>
                        <v-icon small class="mr-1">mdi-translate</v-icon> DeepL translation.
                    </li>
                </ul>
            </div>

            If you only want to see your manually added translations, you can turn off the <b>Hover vocabulary dictionary search</b> option in the settings.

            <div class="mt-5">
                When you move the cursor over a word there is a slight delay before the <b>hover vocabulary box</b> appears. This is done 
                to increase performance, and to avoid unwanted request to the DeepL server and save monthly limits. You can also adjust this delay in the settings.
            </div>
        </v-card>
    </div>
</template>

<style scoped>
    #user-manual-reading {
        width: 100%;
        max-width: 1000px;
    }

    #user-manual-reading ul {
        padding-left: 0px;
    }

    #user-manual-reading ul li {
        list-style: none;
    }
</style>

<script>
    export default {
        data: function() {
            return {
            }
        },
        props: {
        },
        methods: {
        }
    }
</script>
 
