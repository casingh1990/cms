<template>
    <div>
        <div>
            <div class="row" v-if="mode === 'list'">
                <div class="col-xs-12">
                    <div :class="messageType" v-if="message" role="alert">
                        {{ message }}
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td>{{ user.id }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.role }}</td>
                                <td>
                                    <button @click="openForEdit(user)" class="btn btn-default">
                                        Edit
                                    </button>
                                    <button v-if="user.id !== authuser.id" @click="remove(user)" class="btn btn-danger">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else-if="mode === 'add'">
                <div class="col-xs-8">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" v-model="newUser.name">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" class="form-control" v-model="newUser.email">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" v-model="newUser.password">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control" v-model="newUser.password_confirmation">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" v-model="newUser.role">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-default" @click="returnToList">Cancel</button>
                        <button class="btn btn-primary" @click="createAndCloseNewUser">Save</button>
                    </div>
                </div>
            </div>

            <div v-else>
                <div class="col-xs-8">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" class="form-control" v-model="editableUser.email">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" :disabled="!hasOtherAdmin" v-model="editableUser.role">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                        <span id="helpBlock" class="help-block" v-show="!hasOtherAdmin">This is the last admin user so
                            we cannot change the role</span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" v-model="password"
                            placeholder="Only enter a value here if you want to set a new password for this user">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control" v-model="passwordConfirmation">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-default" @click="returnToList">Cancel</button>
                        <button class="btn btn-primary" @click="saveAndCloseEditableUser">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group" v-if="mode !== 'add'">
            <button @click="openAddUserForm">Add</button>
        </div>
    </div>
</template>
<script>
import axios from 'axios';

export default {
    name: 'Users',
    props: {
        authuser: {
            type: Object
        }
    },
    data() {
        return {
            password: '',
            passwordConfirmation: '',
            mode: "list",
            users: [],
            editableUser: {},
            newUser: {},
            message: null,
            messageType: null,
        }
    },
    mounted() {
        this.getUsers();
    },
    computed: {
        hasOtherAdmin() {
            let hasAdmin = false;
            this.users.forEach((user) => {
                if (user.role === 'admin' && user.id !== this.editableUser.id) {
                    hasAdmin = true;
                }
            })

            return hasAdmin;
        }
    },
    methods: {
        openAddUserForm() {
            this.newUser = {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                role: '',
            };
            this.mode = 'add';
        },
        createAndCloseNewUser() {
            axios.post('/users/add', this.newUser)
                .then(() => {
                    this.returnToList();
                    this.getUsers();
                });
        },
        openForEdit(user) {
            this.editableUser = JSON.parse(JSON.stringify(user));
            this.password = '';
            this.confirmPassword = '';
            this.mode = 'editing';
        },
        saveAndCloseEditableUser() {

            if (this.password !== '') {
                if (this.password === this.passwordConfirmation) {
                    this.editableUser.new_password = this.password;
                } else {
                    alert('Password and Confirm Password do no match');
                    return 1;
                }
            }

            axios.put('/users/update', this.editableUser)
                .then(() => {
                    this.returnToList();
                    this.getUsers();
                });
        },
        returnToList() {
            this.mode = 'list';
        },
        remove(user) {
            if (confirm('Are you sure you want to delete ' + user.name + '?')) {
                axios.delete('/users/' + user.id).then(() => {
                    this.showMessage('User ' + user.name + ' was removed successfully');
                    this.getUsers();
                }).catch(() => {
                    this.showMessage('Error removing user ' + user.name, 'danger');
                });
            }
        },
        getUsers() {
            axios.get('/users/list')
                .then((response) => {
                    this.users = response.data;
                })
        },
        showMessage(message, type = 'success') {
            this.message = message;
            this.messageType = 'alert alert-' + type + ' show';
            setTimeout(() => {
                this.message = null;
                this.messageType = null;
            }, 10000);
        }
    }
};
</script>