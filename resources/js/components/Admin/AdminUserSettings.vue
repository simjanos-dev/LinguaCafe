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
        <v-simple-table id="User" class="no-hover border rounded-lg">
            <thead>
                <tr>
                    <th class="text-center">Name</th>
                    <th class="text-center">E-mail</th>
                    <th class="text-center">Created</th>
                    <th class="text-center">Admin</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(user, userIndex) in users">
                    <td class="text-center">{{ user.name }}</td>
                    <td class="text-center">{{ user.email }}</td>
                    <td class="text-center">{{ user.created_at_text }}</td>
                    <td class="text-center">
                        {{ user.is_admin ? 'Yes' : 'No' }}
                    </td>
                    <td class="text-center">
                        <v-btn
                            icon
                            title="Edit"
                            @click="editUser(user)"
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                        <!-- <v-btn
                            icon
                            title="Delete"
                            color="error"
                        >
                            <v-icon>mdi-delete</v-icon>
                        </v-btn> -->
                    </td>
                </tr>
            </tbody>
        </v-simple-table>
    </div>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
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
                axios.get('/users/get').then((response) => {
                    this.users = response.data;
                });
            }
        }
    }
</script>
