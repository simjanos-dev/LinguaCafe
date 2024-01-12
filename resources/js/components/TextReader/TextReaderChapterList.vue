<template>
    <v-dialog v-model="value" scrollable persistent max-width="820">
        <v-card 
            id="text-reader-chapter-list"
            outlined
            class="rounded-lg"
        >
            <v-card-title>Chapters</v-card-title>
            <v-card-text class="pt-6 px-0">
                    <v-simple-table class="book-info-table no-hover pb-4 mx-auto">
                        <thead>
                            <tr>
                                <th class="text-center">Name</th>
                                <th class="text-center">Words</th>
                                <th class="text-center">Unique</th>
                                <th class="text-center">Highlighted</th>
                                <th class="text-center">New</th>
                                <th class="text-center">Read</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(chapter, index) in chapters" :key="index">
                                <td>{{ chapter.name }}</td>
                                <td class="text-center">{{ chapter.wordCount.total }}</td>
                                <td class="text-center">{{ chapter.wordCount.unique }}</td>
                                <td class="text-center"><span class="rounded-pill highlighted">{{ chapter.wordCount.highlighted }}</span></td>
                                <td class="text-center"><span class="rounded-pill new">{{ chapter.wordCount.new }}</span></td>
                                <td class="text-center">
                                    <v-btn depressed rounded small color="primary" width="80px" :to="'/chapters/read/' + chapter.id" v-if="chapter.id != currentChapterId">Read</v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </v-simple-table>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded color="primary" @click="close">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {    
        emits: ['input'],   
        data: function() {
            return {
            }
        },
        props: {
            value : Boolean,
            chapters: Array,
            currentChapterId: Number
        },
        mounted() {
        },
        methods: {
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
