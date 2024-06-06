<template>
    <div id="admin-user-settings">
        <!-- Add or edit user dialog -->
        <admin-edit-user-dialog
            v-if="editDialog.active"
            v-model="editDialog.active"
            :_user-id="editDialog.userId"
            :_is-current-user="editDialog.isCurrentUser"
            :_name="editDialog.name"
            :_email="editDialog.email"
            :_is-admin="editDialog.isAdmin"
            @user-saved="closeUserDialog"
        ></admin-edit-user-dialog>

        <!-- Title subheader -->
        <div class="d-flex subheader mt-4 mb-4 px-2 ">
            Users
            <v-spacer />
            <v-btn rounded dark color="primary" @click="addUser" >
                <v-icon class="mr-1">mdi-plus</v-icon>
                Add user
            </v-btn>
        </div>

        <!-- User list -->
        <v-card outlined class="rounded-lg pa-2 pb-0 mb-32">
            <v-card-title>
                <v-text-field
                    v-model="usersFilter"
                    append-icon="mdi-magnify"
                    label="Search"
                    filled
                    dense
                    hide-details
                    single-line
                    rounded
                ></v-text-field>
            </v-card-title>
            <v-data-table
                class="my-4 mb-0 no-hover"
                :headers="[
                    { text: 'Name', value: 'name' },
                    { text: 'E-mail', value: 'email' },
                    { text: 'Created', value: 'created_at_text', align: 'center' },
                    { text: 'Admin', value: 'is_admin', align: 'center' },
                    { text: 'Actions', value: 'actions', align: 'center', sortable: false },
                ]"
                :items="users"
                :loading="loading"
                :search="usersFilter"
            >
                <!-- Admin -->
                <template v-slot:item.is_admin="{ item }">
                    {{ item.is_admin ? 'Yes' : 'No' }}
                </template>

                <!-- Actions -->
                <template v-slot:item.actions="{ item }">
                    <v-btn
                        icon
                        title="Edit"
                        @click="editUser(item)"
                    >
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>
    </div>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                loading: false,
                usersFilter: '',
                editDialog: {
                    active: false,
                    userId: -1,
                    isCurrentUser: false,
                    name: '',
                    email: '',
                    isAdmin: 0,
                },
                users: [],
            }
        },
        props: {
        },
        mounted() {
            this.loadUsers();
        },
        methods: {
            addUser() {
                this.editDialog.userId = -1;
                this.editDialog.isCurrentUser = false;
                this.editDialog.active = true;
                this.editDialog.name = '';
                this.editDialog.email = '';
                this.editDialog.isAdmin = 0;
            },
            editUser(user) {
                this.editDialog.userId = user.id;
                this.editDialog.isCurrentUser = user.is_current_user;
                this.editDialog.active = true;
                this.editDialog.name = user.name;
                this.editDialog.email = user.email;
                this.editDialog.isAdmin = user.is_admin;
            },
            closeUserDialog() {
                this.editDialog.active = false;
                this.loadUsers();
            },
            loadUsers() {
                this.users = [];
                this.loading = true;
                axios.get('/users/get').then((response) => {
                    this.loading = false;
                    this.users = response.data;
                });
            }
        }
    }
</script>
