<template>
    <div id="user-manual-reading" class="ma-4 mb-16">
        <!-- Hover vocabulary box -->
        <div class="subheader d-flex">Hover vocabulary box</div>
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
 
