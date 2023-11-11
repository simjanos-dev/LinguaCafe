<template>
    <v-dialog v-model="value" persistent max-width="300px">
        <v-card class="rounded-lg">
            <v-card-title>
                <span class="text-h5">Language</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text>
                <v-list rounded>
                    <v-list-item-group color="primary" v-model="selectedLanguage">
                        <v-list-item 
                            v-for="(language, index) in languages"
                            :key="index"
                            :value="language.toLowerCase()"
                            class="regular-list-height my-1" 
                            @click="selectLanguage(language)" 
                        >
                            <v-list-item-avatar tile min-width="60">
                                <v-img 
                                    class="border" 
                                    :src="'/images/flags/' + language.toLowerCase()" 
                                    max-width="43" 
                                    height="28"
                                ></v-img> 
                            </v-list-item-avatar>
                            <v-list-item-content>{{ language }}</v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
        },
        emits: ['input'],
        data: function() {
            return {
                selectedLanguage: 'japanese',

                languages: [
                    'Chinese',
                    'German',
                    'Japanese',
                    'Korean',
                    'Norwegian',
                    'Spanish',
                ],
            };
        },
        mounted: function() {
            axios.get('/language/get').then(function (response) {
                this.selectedLanguage = response.data;
            }.bind(this)).catch(function (error) {}).then(function () {});
        },
        methods: {
            selectLanguage: function(newLanguage) {
                this.selectedLanguage = newLanguage.toLowerCase();
                axios.get('/language/change/' + this.selectedLanguage).then(function (response) {
                    document.location.href = '/';
                }.bind(this)).catch(function (error) {}).then(() => {
                });
            },
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
