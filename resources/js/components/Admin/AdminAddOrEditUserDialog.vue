<template>
    <v-dialog v-model="value" persistent max-width="600px" height="300px">
        <v-card class="rounded-lg">
            <v-card-title>
                <!-- Add user title-->
                <template v-if="userId == -1">
                    <v-icon class="mr-2" >mdi-account-plus</v-icon>
                    <span class="text-h5">Add user</span>
                </template>

                <!-- Edit user title-->
                <template v-if="userId !== -1">
                    <v-icon class="mr-2" >mdi-account-edit</v-icon>
                    <span class="text-h5">Edit user</span>
                </template>

                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            
            <!-- User form -->
            <v-card-text class="pt-4 pb-6">
                <v-form v-model="isFormValid" ref="userForm">                
                    <!-- Name -->
                    <label class="font-weight-bold">Name</label>
                    <v-text-field 
                        v-model="name"
                        filled
                        dense
                        rounded
                        placeholder="Name"
                        maxlength="32"
                        :rules="[rules.nameLength]"
                        :disabled="saving"
                    ></v-text-field>
                    
                    <!-- E-mail -->
                    <label class="font-weight-bold">E-mail address</label>
                    <v-text-field
                        v-model="email"
                        filled
                        dense
                        rounded
                        placeholder="E-mail address"
                        maxlength="64"
                        :rules="[rules.email]"
                        :disabled="saving"
                    ></v-text-field>

                    <template v-if="userId == -1">
                        <!-- Password -->
                        <label class="font-weight-bold">Password</label>
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
                        ></v-text-field>

                        <!-- Password confirmation -->
                        <label class="font-weight-bold">Confirm password</label>
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
                        ></v-text-field>
                    </template>

                    <!-- Admin -->
                    <label class="font-weight-bold">Admin</label>
                    <v-switch
                        v-model="isAdmin"
                        hide-details
                        class="mt-0"
                        color="primary"
                        label="Admin"
                        :disabled="saving"
                    ></v-switch>

                    <v-alert
                        v-if="errorMessage !== '' && errorMessage !== 'success'"
                        class="rounded-lg mt-4 mb-0"
                        color="error"
                        type="error"
                        border="left"
                        dark
                    >
                        {{ errorMessage }}
                    </v-alert>
                </v-form>
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
                    :disabled="!isFormValid || saving"
                    :loading="saving"
                >
                    <template v-if="userId == -1">Add user</template>
                    <template v-if="userId !== -1">Save</template>
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
            _userId: {
                type: Number,
                default: -1
            },
            _name: {
                type: String,
                default: ''
            },
            _email: {
                type: String,
                default: ''
            },
            _isAdmin: {
                type: Number,
                default: false
            },
        },
        emits: ['input'],
        data: function() {
            return {
                isFormValid: false,
                errorMessage: '',
                saving: false,
                userId: this.$props._userId,
                name: this.$props._name,
                email: this.$props._email,
                password: '',
                passwordConfirmation: '',
                isAdmin: Boolean(this.$props._isAdmin),

                rules: {
                    nameLength: value => {
                        if (value.length < 5 || value.length > 24) {
                            return 'Name must be between 5 and 24 characters.';
                        }

                        return true;
                    },
                    password: value => {
                        if (value.length < 8 || value.length > 32) {
                            return 'Name must be between 8 and 32 characters.';
                        }
                        
                        return true;
                    },
                    passwordMatch: value => {
                        return value == this.password || 'Password confirmation does not match the password.';
                    },
                    email: value => {
                        const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        return pattern.test(value) || 'Invalid e-mail.';
                    },
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

                let data = {
                    userId: this.userId,
                    name: this.name,
                    email: this.email,
                    isAdmin: this.isAdmin
                };

                if (this.userId == -1) {
                    data.password = this.password;
                    data.passwordConfirmation = this.passwordConfirmation;
                }

                axios.post('/user/save', data).then((response) => {
                    this.saving = false;
                    this.errorMessage = response.data;
                    
                    if (this.errorMessage == 'success') {
                        this.$emit('user-saved');
                    }
                });
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
