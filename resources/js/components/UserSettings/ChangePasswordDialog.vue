<template>
    <v-dialog v-model="value" persistent max-width="600px" height="300px">
        <v-card class="rounded-lg">
            <v-card-title>
                <v-icon class="mr-2">mdi-lock-reset</v-icon>Change password
                <v-spacer></v-spacer>
                <v-btn icon @click="close"> 
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            
            <!-- Change password form -->
            <v-card-text class="pt-4 pb-6">
                <v-form v-model="isFormValid" ref="userForm">
                    <template>
                        <!-- Password -->
                        <label class="font-weight-bold">New password</label>
                        <v-text-field
                            v-model="password"
                            type="password"
                            filled
                            dense
                            rounded
                            placeholder="Password"
                            maxlength="32"
                            style="overflow: hidden;"
                            :rules="[rules.password]"
                            :disabled="saving"
                            @keyup.enter="save"
                        ></v-text-field>

                        <!-- Password confirmation -->
                        <label class="font-weight-bold">Confirm new password</label>
                        <v-text-field
                            v-model="passwordConfirmation"
                            type="password"
                            filled
                            dense
                            rounded
                            placeholder="Confirm password"
                            maxlength="32"
                            :rules="[rules.passwordMatch]"
                            :disabled="saving"
                            @keyup.enter="save"
                        ></v-text-field>
                    </template>
                </v-form>
                
                <v-alert
                    v-if="saveResult !== '' && saveResult !== 'success'"
                    class="rounded-lg mt-4 mb-0"
                    color="error"
                    type="error"
                    border="left"
                    dark
                >
                    {{ saveResult }}
                </v-alert>

                <v-alert
                    v-if="saveResult == 'success'"
                    class="rounded-lg mt-4 mb-0"
                    color="success"
                    type="success"
                    border="left"
                    dark
                >
                    Password changed successfully!
                </v-alert>
                
            </v-card-text>
            
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>

                <!-- Save button -->
                <v-btn 
                    rounded 
                    depressed
                    color="primary" 
                    @click="save"
                    :disabled="!isFormValid || saving || saveResult == 'success'"
                    :loading="saving"
                >
                    Change password
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean
        },
        emits: ['input'],
        data: function() {
            return {
                isFormValid: false,
                saving: false,
                password: '',
                passwordConfirmation: '',
                saveResult: '',

                rules: {
                    password: value => {
                        if (value.length < 8 || value.length > 32) {
                            return 'Password must be between 8 and 32 characters.';
                        }
                        
                        return true;
                    },
                    passwordMatch: value => {
                        return value == this.password || 'Password confirmation does not match the password.';
                    }
                },
            };
        },
        mounted: function() {
        },
        methods: {
            save() {
                if (!this.$refs.userForm.validate()) {
                    return;
                }

                this.saving = true;
                axios.post('/user/change-password', {
                    password: this.password,
                    passwordConfirmation: this.passwordConfirmation
                }).then((response) => {
                    this.saving = false;
                    this.saveResult = response.data;
                    
                    if (response.data == 'success') {
                        setTimeout(() => {
                            this.$emit('password-changed');
                        }, 1000);
                    }
                });
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
