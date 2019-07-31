<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isAdminOrAuthor()">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">User Table</h3>

                        <div class="card-tools">

                            <button class="btn btn-success" @click="newModal()">Add New <i
                                class="fas fa-user-plus"></i></button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Registered at</th>
                                <th>Modify</th>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id">
                                <td v-text="user.id"></td>
                                <td v-text="user.name"></td>
                                <td v-text="user.email"></td>
                                <td>{{user.type | upText}}</td>
                                <td>{{ user.created_at | myDate}}</td>
                                <td>
                                    <a href="#" @click="editModal(user)">
                                        <i class="fa fa-edit blue"></i>
                                    </a>
                                    /
                                    <a href="#" @click="deleteUser(user.id)">
                                        <i class="fa fa-trash red"></i>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <pagination :data="users" @pagination-change-page="getResults"></pagination>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div><!-- /.row -->

        <div v-if="!$gate.isAdminOrAuthor()">
            <not-found></not-found>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="addNewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-show="editMode" id="exampleModalLabel">Update User's info</h5>
                        <h5 class="modal-title" v-show="!editMode" id="exampleModalLabel">Add new</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent=" editMode ? updateUser() : createUser()">
                        <div class="modal-body">
                            <div class="form-group">
                                <input v-model="form.name" type="text" name="name" placeholder="Name"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>
                            <div class="form-group">
                                <input v-model="form.email" type="email" name="email" placeholder="Email address"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">

                                <has-error :form="form" field="email"></has-error>
                            </div>
                            <div class="form-group">
                                <textarea v-model="form.bio" name="bio" cols="30" rows="2"
                                          placeholder="Short bio for user"
                                          class="form-control"
                                          :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                                <has-error :form="form" field="bio"></has-error>
                            </div>
                            <div class="form-group">
                                <select name="type" v-model="form.type" id="type" class="form-control"
                                        :class="{ 'is-invalid': form.errors.has('type') }">
                                    <option value="">Select user role</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">Standard User</option>
                                    <option value="author">Author</option>
                                </select>
                                <has-error :form="form" field="type"></has-error>
                            </div>
                            <div class="form-group">
                                <input v-model="form.password" type="password" name="password" placeholder="Password"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">

                                <has-error :form="form" field="password"></has-error>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button v-show="editMode" type="submit" class="btn btn-success">Update</button>
                            <button v-show="!editMode" type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                editMode: false,
                users: {},
                form: new Form({
                    id: '',
                    name: '',
                    email: '',
                    password: '',
                    type: '',
                    bio: '',
                    photo: ''
                })
            }
        },
        methods: {
            // Our method to GET results from a Laravel endpoint
            getResults(page = 1) {
                axios.get('/api/user?page=' + page)
                    .then(response => {
                        this.users = response.data;
                    });
            },
            updateUser() {
                this.$Progress.start();
                this.form.put('/api/user/' + this.form.id)
                    .then(() => {
                        Fire.$emit('AfterCreate');

                        $('#addNewModal').modal('hide');

                        Swal.fire(
                            'Updated!',
                            'User has been updated.',
                            'success'
                        )

                        this.$Progress.finish();
                    })
                    .catch(() => {
                        this.$Progress.fail();
                    });
            },
            editModal(user) {
                this.editMode = true;
                this.form.clear();
                this.form.reset();
                $('#addNewModal').modal('show');
                this.form.fill(user);

            },
            newModal() {
                this.editMode = false;
                this.form.reset();
                $('#addNewModal').modal('show');
            },
            loadUsers() {
                if (this.$gate.isAdminOrAuthor()) {
                    axios.get('api/user').then(({data}) => (this.users = data));
                }
            },
            deleteUser(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        this.form.delete('api/user/' + id)
                            .then(() => {
                                Fire.$emit('AfterCreate');

                                Swal.fire(
                                    'Deleted!',
                                    'User has been deleted.',
                                    'success'
                                )

                            }).catch(() => {
                            Swal.fire('Failed!', 'There was something wrong.', 'warning');
                        });
                    }

                })
            },
            createUser() {
                this.$Progress.start()
                this.form.post('/api/user')
                    .then(() => {
                        Fire.$emit('AfterCreate');

                        $('#addNewModal').modal('hide');

                        Toast.fire({
                            type: 'success',
                            title: 'User created successfully'
                        });
                        this.$Progress.finish();
                    })
                    .catch(() => {

                    });


            }
        },
        mounted() {
            this.loadUsers();
            Fire.$on('AfterCreate', () => {
                this.loadUsers();
            });
            Fire.$on('searching', () => {
                let query = this.$parent.search;

                axios.get('api/findUser?q=' + query)
                    .then(({data}) => (this.users = data))
                    .catch(() => {

                    });
            });
        }
    }
</script>
