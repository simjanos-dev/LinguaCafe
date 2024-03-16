<template>
    <div id="user-manual-languages" class="ma-4 mb-16">
        <!-- Language info -->
        <div class="subheader d-flex">Languages</div>
        <v-card outlined class="rounded-lg pa-4">
            In LinguaCafe all the data are separated by the selected language. This means that any action you take in one language will not affect
            the data in other languages, so the first thing you should do in LinguaCafe is select your target language. You can change your selected
            language by clicking on it in the bottom left corner.

            <div class="mt-5">
                <b>Lemma generation:</b> When you import a text into LinguaCafe, the text processor will automatically 
                assign dictionary form to words for supported languages.
            </div>
            
            <div class="mt-2">
                <b>Gender tagging:</b> In gendered and supported languages, LinguaCafe will prepend nouns with additional information based on the words' gender.
            </div>
        </v-card>

        <!-- Supported language list -->
        <div class="subheader d-flex mt-4">Supported languages</div>
        <v-simple-table id="user-manual-language-table" class="rounded-lg border no-hover">
            <thead>
                <tr>
                    <th>Flag</th>
                    <th>Language</th>
                    <th>DeepL</th>
                    <th>Lemma generation</th>
                    <th>Gender tagging</th>
                    <th>Dictionaries</th>
                </tr>
            </thead>
            <tbody>
                <!-- Chinese -->
                <tr>
                    <td>
                        <img src="/images/flags/chinese.png" width='25' />
                    </td>
                    <td>Chinese</td>
                    <td>&check;</td>
                    <td></td>
                    <td></td>
                    <td>wiktionary, cc-cedict</td>
                </tr>

                <!-- Czech -->
                <tr>
                    <td>
                        <img src="/images/flags/czech.png" width='25' />
                    </td>
                    <td>Czech</td>
                    <td>&check;</td>
                    <td></td>
                    <td></td>
                    <td>wiktionary, dict cc</td>
                </tr>

                <!-- Dutch -->
                <tr>
                    <td>
                        <img src="/images/flags/dutch.png" width='25' />
                    </td>
                    <td>Dutch</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td></td>
                    <td>dict cc</td>
                </tr>

                <!-- Finnish -->
                <tr>
                    <td>
                        <img src="/images/flags/finnish.png" width='25' />
                    </td>
                    <td>Finnish</td>
                    <td>&check;</td>
                    <td>inaccurate</td>
                    <td></td>
                    <td>wiktionary, dict cc</td>
                </tr>
                
                <!-- French -->
                <tr>
                    <td>
                        <img src="/images/flags/french.png" width='25' />
                    </td>
                    <td>French</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td></td>
                    <td>wiktionary, dict cc</td>
                </tr>

                <!-- German -->
                <tr>
                    <td>
                        <img src="/images/flags/german.png" width='25' />
                    </td>
                    <td>German</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td>wiktionary, dict cc</td>
                </tr>

                <!-- Italian -->
                <tr>
                    <td>
                        <img src="/images/flags/italian.png" width='25' />
                    </td>
                    <td>Italian</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td></td>
                    <td>wiktionary, dict cc</td>
                </tr>

                <!-- Japanese -->
                <tr>
                    <td>
                        <img src="/images/flags/japanese.png" width='25' />
                    </td>
                    <td>Japanese</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td></td>
                    <td>jmdict, wiktionary</td>
                </tr>

                <!-- Korean -->
                <tr>
                    <td>
                        <img src="/images/flags/korean.png" width='25' />
                    </td>
                    <td>Korean</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td></td>
                    <td>wiktionary, kengdic</td>
                </tr>
                <!-- Norwegian -->
                <tr>
                    <td>
                        <img src="/images/flags/norwegian.png" width='25' />
                    </td>
                    <td>Norwegian</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td>wiktionary, dict cc</td>
                </tr>
                <!-- Russian -->
                <tr>
                    <td>
                        <img src="/images/flags/russian.png" width='25' />
                    </td>
                    <td>Russian</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td></td>
                    <td>wiktionary, dict cc</td>
                </tr>
                <!-- Spanish -->
                <tr>
                    <td>
                        <img src="/images/flags/spanish.png" width='25' />
                    </td>
                    <td>Spanish</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td></td>
                    <td>wiktionary, dict cc</td>
                </tr>
                <!-- Swedish -->
                <tr>
                    <td>
                        <img src="/images/flags/swedish.png" width='25' />
                    </td>
                    <td>Swedish</td>
                    <td>&check;</td>
                    <td>&check;</td>
                    <td></td>
                    <td>dict cc</td>
                </tr>
                <!-- Ukrainian -->
                <tr>
                    <td>
                        <img src="/images/flags/ukrainian.png" width='25' />
                    </td>
                    <td>Ukrainian</td>
                    <td>&check;</td>
                    <td></td>
                    <td></td>
                    <td>wiktionary</td>
                </tr>
                <!-- Welsh -->
                <tr>
                    <td>
                        <img src="/images/flags/welsh.png" width='25' />
                    </td>
                    <td>Welsh</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>wiktionary, eurfa</td>
                </tr>

                
            </tbody>
        </v-simple-table>
    </div>
</template>

<style scoped>
    #user-manual-languages {
        width: 100%;
        max-width: 1000px;
    }

    #user-manual-language-table {
        width: 100%;
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
 
