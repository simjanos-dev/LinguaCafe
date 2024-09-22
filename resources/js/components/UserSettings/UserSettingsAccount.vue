<template>
    <div id="user-settings-account">
        <!-- Password change dialog -->
        <change-password-dialog
            v-model="passwordChangeDialog"
            @password-changed="passwordChanged"
        ></change-password-dialog>

        <!-- Delete language data -->
        <div class="subheader mt-4 mb-2 d-flex">
            Account
        </div>
        <v-card outlined class="rounded-lg pb-0 mb-32">
            <v-card-text>
                <v-row>
                    <v-col>
                        <b>Username:</b> <br>
                        {{ this.$store.state.shared.userName }}
                    </v-col>
                    <v-col>
                        <b>E-mail address:</b> <br>
                        {{ this.$store.state.shared.userEmail }}
                    </v-col>
                </v-row>
                <div class="d-flex mt-4">
                    <v-spacer />
                    
                    <!-- Change password button -->
                    <v-btn
                        v-if="!passwordChangeSuccess"
                        rounded
                        depressed
                        color="primary"
                        @click="passwordChangeDialog = true;"
                    >
                        <v-icon class="mr-2">mdi-lock-reset</v-icon>
                        Change password
                    </v-btn>

                    <!-- Password changed success message -->
                    <v-alert class="mb-0" border="left" color="success" dense v-else>
                        Your password has been changed successfully.
                    </v-alert>
                </div>
            </v-card-text>
        </v-card>

        <!-- Delete language data -->
        <div class="subheader mt-6 mb-2 d-flex">
            <v-icon large color="red" class="mr-2">
                mdi-alert
            </v-icon>
                
            Delete language data
            <v-spacer />
            <v-img 
                eager
                class="border my-2 rounded" 
                :src="'/images/flags/' + $props.language.toLowerCase() + '.png'" 
                max-width="43" 
                height="28"
            ></v-img> 
        </div>
        <v-card outlined class="rounded-lg pb-0 mb-32" :loading="deleting">
            <v-card-text>
                This action will delete <b>all</b> your data in {{ formattedLanguageText }}. Your data in other languages will not be affected. 

                <div class="mt-4">
                    Data to be deleted:
                    <ul>
                        <li>Books</li>
                        <li>Chapters</li>
                        <li>Vocabulary</li>
                        <li>Phrases</li>
                        <li>Example sentences</li>
                        <li>Achieved goal statistics</li>
                    </ul>
                </div>

                <div id="delete-confirm-text" class="mt-6">
                    <label class="font-weight-bold">Type "delete all my {{ $props.language }} data"</label>
                    <v-text-field 
                        v-model="confirmText"
                        filled
                        dense
                        rounded
                        hide-details
                        placeholder="Confirm deletion"
                        width="200"
                    ></v-text-field>
                </div>

                <!-- Error message -->
                <v-alert
                    v-if="!deleting && deletionError"
                    class="rounded-lg mt-4 mb-0"
                    color="error"
                    type="error"
                    border="left"
                    dark
                >
                    An error has occurred while deleting your {{ formattedLanguageText }} data.
                </v-alert>

                <!-- Success message -->
                <v-alert
                    v-if="!deleting && deletionSuccess"
                    class="rounded-lg mt-4 mb-0"
                    color="success"
                    type="success"
                    border="left"
                    dark
                >
                    Your {{ formattedLanguageText }} data has been deleted successfully. 
                </v-alert>
                
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn 
                    rounded 
                    depressed 
                    color="error" 
                    :disabled="deleting || confirmText !== `delete all my ${$props.language} data`"
                    @click="deleteLanguageData"
                >
                    <v-icon class="mr-2">mdi-delete</v-icon>
                    Delete
                </v-btn>
            </v-card-actions>
        </v-card>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                passwordChangeDialog: false,
                passwordChangeSuccess: false,
                confirmText: '',
                formattedLanguageText: this.$props.language.charAt(0).toUpperCase() + this.$props.language.slice(1),
                deleting: false,
                deletionError: false,
                deletionSuccess: false,
            }
        },
        props: {
            language: String,
        },
        mounted() {
           
        },
        methods: {
            passwordChanged() {
                this.passwordChangeDialog = false
                this.passwordChangeSuccess = true
            },
            deleteLanguageData() {
                this.deleting = true;
                this.deletionSuccess = false;
                this.deletionError = false;

                axios.delete(`/users/delete-language-data/${this.$props.language}`).then((response) => {
                    this.deletionSuccess = true;
                    this.deleting = false;
                }).catch((error) => {
                    this.deletionError = true;
                    this.deleting = false;
                });
            }
        }
    }
</script>
