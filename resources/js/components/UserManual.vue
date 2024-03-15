<template>
    <!-- Menu -->
    <div id="user-manual">
        <v-card outlined class="rounded-lg ma-4 pa-2 pl-0" width="200px">
            <v-treeview
                shaped
                dense
                hoverable
                activatable
                open-on-click
                color="primary"
                :items="pages"
                @update:active="test"
            >
                <template #label="{ item }">
                    <span>{{item.name}}</span>
                </template>
            </v-treeview>
        </v-card>

        <!-- Introduction -->
        <div v-if="selectedPage === 1">
            introduction
        </div>

        <!-- Library -->
        <div v-if="selectedPage === 10">
            Library
        </div>

    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                selectedPage: [],
                pages: [
                    {
                        id: 1,
                        name: 'Introduction',
                    },
                    {
                        id: 2,
                        name: 'Installation',
                        children: [
                            {
                                id: 3,
                                name: 'Linux'
                            },
                            {
                                id: 4,
                                name: 'Windows'
                            },
                            {
                                id: 5,
                                name: 'macOS'
                            }
                        ]
                    },
                    {
                        id: 6,
                        name: 'Languages',
                    },
                    {
                        id: 7,
                        name: 'Dictionaries',
                    },
                    {
                        id: 8,
                        name: 'Backup',
                    },
                    {
                        id: 9,
                        name: 'Updating',
                    },
                    {
                        id: 10,
                        name: 'Library',
                    },
                    {
                        id: 11,
                        name: 'Reading',
                    },
                    {
                        id: 12,
                        name: 'Reviewing',
                    },
                    {
                        id: 13,
                        name: 'Vocabulary',
                        children: [
                            {
                                id: 14,
                                name: 'Importing',
                            },
                            {
                                id: 15,
                                name: 'Exporting',
                            }
                        ]
                    }
                ],
            }
        },
        props: {
        },
        methods: {
            test(event) {
                this.selectedPage = event[0];
            }
        }
    }
</script>
 
