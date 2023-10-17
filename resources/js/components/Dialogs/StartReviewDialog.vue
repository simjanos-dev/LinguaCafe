<template>
    <v-dialog v-model="value" persistent max-width="400px">
        <v-card class="rounded-lg">
            <v-card-title>
                <span class="text-h5">Review</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text>
                <span v-if="bookName !== ''">Book: {{ bookName }}</span><br>
                <span v-if="chapterName !== ''">Chapter: {{ chapterName }}</span>
            </v-card-text>
            <v-card-actions>
                <v-checkbox class="mt-0 pt-0" hide-details v-model="practiceMode" label="Practice mode"></v-checkbox>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>
                <v-btn rounded text :to="'/review/' + practiceMode + '/' + bookId + '/' + chapterId ">Start</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
            bookId: {
                type: Number,
                default: -1
            },
            bookName: {
                type: String,
                default: ''
            },
            chapterId: {
                type: Number,
                default: -1
            },
            chapterName: {
                type: String,
                default: ''
            }
        },
        emits: ['input'],
        data: function() {
            return {
                practiceMode: false,
            };
        },
        mounted: function() {
            console.log(this.bookName);
        },
        methods: {
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
