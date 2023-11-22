<template>
    <div>
        <v-form ref="libraryLocationForm" v-model="isFormValid">
            <v-alert dark border="left" type="info" color="primary" class="mt-4">
                Your selected chapter name will be suffixed with indexes of the imported chapters.
                For example if you choose the chapter name "Narnia chapter", then your chapter names will be:<br><br>
                <ul class="mb-0">
                    <li>Narnia chapter 1</li>
                    <li>Narnia chapter 2</li>
                    <li>...</li>
                </ul>
            </v-alert>

            <!-- Book selector and chapter name -->
            <label class="font-weight-bold">Library location</label>
            
            <!-- Skeleton loaders for radio buttons -->
            <div class="library-location-skeleton-loader mb-2" v-if="loading">
                <v-skeleton-loader
                    class="rounded-pill mr-1"
                    type="card"
                ></v-skeleton-loader>
                <v-skeleton-loader
                    class="rounded-pill"
                    type="card"
                ></v-skeleton-loader>
            </div>
            <div class="library-location-skeleton-loader longer" v-if="loading">
                <v-skeleton-loader
                    class="rounded-pill mr-1"
                    type="card"
                ></v-skeleton-loader>
                <v-skeleton-loader
                    class="rounded-pill"
                    type="card"
                ></v-skeleton-loader>
            </div>

            <!-- Create or import into existing book radio buttons -->
            <v-radio-group
                v-if="!loading"
                v-model="newOrExistingBook"
                class="mt-0"
                :rules="[rules.newOrExistingBook]"
                @change="formChanged"
            >
                <v-radio
                    label="Create new book"
                    value="new"
                    :disabled="loading"
                ></v-radio>
                <v-radio
                    v-if="books.length > 0"
                    label="Use existing book"
                    value="existing"
                ></v-radio>
            </v-radio-group>
            
            <!-- Book selector for existing book -->
            <template v-if="newOrExistingBook == 'existing'">
                <label class="font-weight-bold">Book</label>
                <v-select
                    v-model="bookId"
                    :items="books"
                    placeholder="Select a book"
                    item-value="id"
                    dense
                    filled
                    rounded
                    :rules="[rules.bookId]"
                    @change="formChanged"
                >
                    <template v-slot:selection="{ item, index }">
                        {{ item.name }}
                    </template>
                    <template v-slot:item="{ item }">
                        {{ item.name }}
                    </template>
                </v-select>
            </template>

            <!-- Book name text field for new book -->
            <template v-if="newOrExistingBook == 'new'">
                <label class="font-weight-bold">Book name</label>
                <v-text-field 
                    v-model="bookName"
                    filled
                    dense
                    rounded
                    placeholder="Book name"
                    maxlength="128"
                    :rules="[rules.bookName]"
                    @keyup="formChanged"
                ></v-text-field>
            </template>
            
            <!-- Chapter name -->
            <template v-if="newOrExistingBook !== ''">
                <label class="font-weight-bold">Chapter name</label>
                <v-text-field 
                    v-model="chapterName"
                    filled
                    dense
                    rounded
                    placeholder="Chapter name"
                    maxlength="120"
                    :rules="[rules.chapterName]"
                    @keyup="formChanged"
                ></v-text-field>
            </template>
        </v-form>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                loading: true,
                books: [{id: -1, name: 'loading'}],
                bookId: -1,
                isFormValid: false,
                newOrExistingBook: '',
                bookName: '',
                chapterName: '',
                rules: {
                    newOrExistingBook: (value) => {
                        if (value === '') {
                            return 'You must select an option.';
                        }

                        return true;
                    },
                    bookId: (value) => {
                        return true;
                    },
                    bookName: (value) => {
                        if (!value.length) {
                            return 'You must type in a book name.';
                        }

                        if (value.length > 128) {
                            return 'The book name must be below 128 characters.';
                        }

                        return true;
                    },
                    chapterName: (value) => {
                        if (!value.length) {
                            return 'You must type in a chapter name.';
                        }

                        if (value.length > 120) {
                            return 'The chapter name must be below 120 characters.';
                        }

                        return true;
                    }
                }
            }
        },
        props: {
        },
        mounted() {
            axios.post('/books').then((response) => {
                this.loading = false;
                this.books = response.data;
                
                if (this.books.length) {
                    this.bookId = this.books[0].id;
                }
            });
        },
        methods: {
            formChanged() {
                this.$nextTick(() => {
                    var valid = true;
                    if (!this.$refs.libraryLocationForm.validate()) {
                        valid = false;
                    }

                    if (this.newOrExistingBook === 'new' && this.bookName === '') {
                        valid = false;
                    }

                    if (this.newOrExistingBook === 'existing' && this.bookId === -1) {
                        valid = false;
                    }

                    this.$emit('input-changed', {
                        isFormValid: valid,
                        newOrExistingBook: this.newOrExistingBook,
                        bookId: this.bookId,
                        bookName: this.bookName,
                        chapterName: this.chapterName
                    });
                });
            }
        }
    }
</script>
