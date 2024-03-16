<template>
    <div id="user-manual-vocabulary-import" class="ma-4 mb-16">
        <!-- Vocabulary import -->
        <div class="subheader d-flex">Vocabulary import</div>
        <v-card outlined class="rounded-lg pa-4">
            If you have a list of words that you already know before you started using LinguaCafe, you can import them from a .csv file. 
            
            <div class="mt-5">
                To import words, go to the <b>Vocabulary</b> page, select the <b>Data</b> dropdown menu, and inside that click on the <b>Import</b> button.
                On the import dialog you can select your .csv file and a few options. If the <b>Only update</b> option is enabled, no new words will be added to 
                the system, this enables you to only update fields for words that you have already encountered in LinguaCafe.
            </div>

            <div class="mt-5">
                The .csv file can have these columns, in this order: 
                <ul>
                    <li>
                        Word: required, must not contain any spaces.
                    </li>
                    <li>
                        Translation: optional, can be left empty.
                    </li>
                    <li>
                        Lemma: optional, can be left empty.
                    </li>
                    <li>
                        Reading: optional, can be left empty.
                    </li>
                    <li>
                        Lemma reading: optional, can be left empty.
                    </li>
                    <li>
                        Level: optional, cannot be left empty. Accepted values: new, ignored, learned, 1, 2, 3, 4, 5, 6, 7.
                    </li>
                </ul>

                At least the first column must be present in the .csv file. Any further columns can be added to it in the order 
                showed above. If a column is not provided, those fields will not be changed in the database. However if a column 
                is provided, and it's left empty in a row, it will be overwritten in the database with an empty value.

                <div class="my-5">
                    <v-icon large class="mr-2" color="warning">mdi-alert</v-icon> Changes after importing cannot be reverted!
                </div>

                After the import is complete, you will see a message about the number of created, updated and rejected words.
            </div>
        </v-card>
    </div>
</template>

<style scoped>
    #user-manual-vocabulary-import {
        width: 100%;
        max-width: 1000px;
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
 
