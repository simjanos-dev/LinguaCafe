<template>
    <form method="post" id="create-book-form">
        <div class="form-group">
            <label for="name">Name</label>
            <input class="form-control" type="text" v-model="name" required>
        </div>
        
        <div class="text-box red" v-if="nameError">
            You must type in a name.
        </div>

        <div class="form-group">
            <label for="image">Cover image</label>
            <input type="file" class="form-control-file" accept="image/*" ref="image">
        </div>

        <button type="submit" class="btn btn-primary" @click.prevent="createBook">Create Book</button>
        <router-link class="sidebar-button" to="/books/search"><button id="create-book-button" class="btn btn-primary">Cancel</button></router-link>
        
        <div class="text-box red" v-if="error !== ''">
            {{ error }}
        </div>
    </form>
</template>

<script>
    export default {
        data: function() {
            return {
                name: '',
                nameError: false,
                error: ''
            }
        },
        props: {
            
        },
        mounted() {
        },
        methods: {
            createBook: function() {
                this.resetErrors();
                if (this.name == '') {
                    this.nameError = true;
                    return;
                }

                var form = new FormData();
                form.set('name',this.name);
                if (this.$refs.image.files.length) {
                    form.set('image',this.$refs.image.files[0])
                }

                axios.post('/books/create', form).then(function (response) {
                    if (response.data == 'success') {
                        this.$router.push('/books');
                    } else {
                        this.error = 'Something went wrong. Please try again.';
                    }
                }.bind(this)).catch(function (error) {
                    this.error = 'Something went wrong. Please try again.';
                }).then(function () {});
            },
            resetErrors: function() {
                this.error = '';
                this.nameError = false;
            }
        }
    }
</script>
