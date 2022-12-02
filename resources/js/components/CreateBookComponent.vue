<template>
    <v-container>
        <v-form ref="form" id="create-book-form" class="mx-auto mt-6">
            <v-text-field 
                filled
                dense
                label="Name"
                v-model="name"
                :rules="[rules.required]"
            ></v-text-field>
            
            <v-file-input
                ref="image"
                :rules="[rules.required]"
                accept="image/*"
                placeholder="Cover image"
                prepend-icon="mdi-image"
                v-model="image"
            ></v-file-input>

            <v-btn 
                class="mx-0"
                rounded 
                :disabled="saving"
                :loading="saving"
                :color="saving ? '' : 'secondary'"
                @click="createBook"
            >Save</v-btn>
            
            <v-btn 
                class="mx-0"
                rounded
                color="secondary"
                @click="$router.push('/books')"
            >Cancel</v-btn>
            
            <v-alert
                class="my-3" 
                border="left"
                type="error"
                v-if="error"
                >
                Something went wrong. Please try again.
            </v-alert>
        </v-form>
    </v-container>
</template>

<script>
    export default {
        data: function() {
            return {
                rules: {
                    required: (value) => {
                        return !!value || 'Required.';
                    },
                },
                saving: false,
                error: false,
                name: '',
                image: null,
            }
        },
        props: {
            
        },
        mounted() {
        },
        methods: {
            createBook: function() {
                if (!this.$refs.form.validate()) {
                    return;
                }

                this.saving = true;
                
                var form = new FormData();
                form.set('name',this.name);
                if (!!this.image) {
                    form.set('image',this.image)
                }

                axios.post('/books/create', form).then((response) => {
                    this.saving = false;
                    if (response.data == 'success') {
                        this.$router.push('/books');
                    } else {
                        this.error = true;
                    }
                }).catch((error) => {
                    this.saving = false;
                    this.error = true;
                }).then( () => {
                    this.saving = false;
                });
            }
        }
    }
</script>
