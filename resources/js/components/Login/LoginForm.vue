<template>
    <v-container class="d-flex justify-center">
        <theme-selection-dialog v-model="themeSelectionDialog" @input="updateTheme"></theme-selection-dialog>

        <!-- Create user dialog -->
        <admin-edit-user-dialog 
            v-if="addUserDialog"
            v-model="addUserDialog"
            :_user-id="-1"
            _name=""
            :_email="email"
            :_is-admin="1"
            admin-lock
            @user-saved="addUserDialogSaved"
        ></admin-edit-user-dialog>

        <!-- Login and create first user form -->
        <v-card outlined class="rounded-lg mt-16" width="600px">
            <!-- Form title -->
            <v-card-title>
                <v-icon class="mr-2">mdi-account</v-icon>Login
                <v-spacer />
                <v-btn rounded depressed @click="themeSelectionDialog = true;">
                    <v-icon class="mr-2">mdi-weather-sunny</v-icon> / <v-icon class="ml-2">mdi-weather-night</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text class="pt-4 pb-6">
                <v-form v-model="isFormValid" ref="loginForm">
                    <!-- First user information alerts -->
                    <v-alert
                        v-if="$props.userCount == 0 && !firstUserAdded"
                        class="rounded-lg mb-8"
                        color="primary"
                        type="info" 
                        border="left"
                        dark
                    >
                        It seems like this is your first time using LinguaCafe after installation. Please create your first user, 
                        it will be automatically set as admin.

                        <div class="d-flex mt-4">
                            <v-spacer />
                            <v-btn 
                                rounded 
                                depressed 
                                color="gray"
                                class="text--text"
                                @click="addUserDialog = true;"
                            >   
                                <v-icon color="text" class="mr-2">mdi-account-plus</v-icon>
                                Create first user
                            </v-btn>
                        </div>
                    </v-alert>

                    <v-alert
                        v-if="firstUserAdded"
                        class="rounded-lg mb-8"
                        color="success"
                        type="success"
                        border="left"
                        dark
                    >
                        User account created successfully.
                    </v-alert>                

                    <!-- Forms -->
                    <label class="font-weight-bold">E-mail address</label>
                    <v-text-field
                        v-model="email"
                        rounded
                        filled
                        dense
                        name="linguacafe-email"
                        placeholder="E-mail address"
                        :rules="[rules.email]"
                        @keyup.enter="login"
                    />

                    <label class="font-weight-bold">Password</label>
                    <v-text-field
                        v-model="password"
                        rounded
                        filled
                        dense
                        type="password"
                        name="linguacafe-password"
                        placeholder="Password"
                        :rules="[rules.password]"
                        @keyup.enter="login"
                    />

                    <!-- Error alert -->
                    <v-alert
                        v-if="error !== ''"
                        class="rounded-lg mt-4 mb-0"
                        color="error"
                        type="error"
                        border="left"
                        dark
                    >
                        {{ error }}
                    </v-alert>
                </v-form>
            </v-card-text>

            <!-- Action buttons -->
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="primary" 
                    rounded 
                    :disabled="loading || !isFormValid" 
                    :loading="loading"
                    @click="login"
                >
                    Login
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-container>
</template>

<script>
    export default {
        props: {
            userCount: Number
        },
        data: function() {
            return {
                themeSelectionDialog: false,
                isFormValid: false,
                addUserDialog: false,
                firstUserAdded: false,
                email: '',
                password: '',
                error: '',
                loading: false,
                

                rules: {
                    password: value => {
                        if (value.length < 1) {
                            return 'Invalid password.';
                        }
                        
                        return true;
                    },
                    email: value => {
                        const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        return pattern.test(value) || 'Invalid e-mail.';
                    }
                }
            };
        },
        mounted: function() {
        },
        methods: {
            createFirstUser() {
                this.loading = true;
                axios.post('/login', {
                    email: this.email,
                    password: this.password,
                    remember: true
                }).then((response) => {
                    if(response.data.length) {
                        this.error = 'Invalid e-mail or password';
                        this.loading = false;
                    } else {
                        window.location.href = "/";
                    }
                }).catch((error) => {
                    this.loading = false;
                    this.error = 'An error has occurred while creating the user.';
                });
            },
            addUserDialogSaved() {
                this.addUserDialog = false;
                this.firstUserAdded = true;
            },
            login() {
                if(!this.$refs.loginForm.validate()) {
                    return;
                }

                this.loading = true;
                axios.post('/login', {
                    email: this.email,
                    password: this.password,
                    remember: true
                }).then((response) => {
                    if(response.data.length) {
                        this.error = 'Invalid email or password';
                        this.loading = false;
                    } else {
                        window.location.href = "/";
                    }
                }).catch((error) => {
                    this.loading = false;
                    this.error = 'Invalid email or password';
                });
            },
            updateTheme() {
                window.location.href = "/";
            },
        }
    }
</script>
