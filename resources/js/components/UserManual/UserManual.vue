<template>
    <!-- Menu -->
    <div id="user-manual">
        <div id="user-manual-menu">
            <v-card outlined class="rounded-lg ma-4 pa-2 pl-0">
                <v-treeview
                    shaped
                    dense
                    hoverable
                    activatable
                    open-on-click
                    color="primary"
                    :items="pages"
                    @update:active="updateSelectedPage"
                >
                    <template #label="{ item }">
                        <span>{{item.name}}</span>
                    </template>
                </v-treeview>
            </v-card>
        </div>

        <!-- Pages -->
        <div id="user-manual-content">
            <user-manual-languages v-if="selectedPage === 'languages'" />
            <user-manual-vocabulary-import v-if="selectedPage === 'vocabulary-import'" />
            <user-manual-reading v-if="selectedPage === 'reading'" />
        </div>

    </div>
</template>

<style scoped>
    #user-manual {
        display: flex;
        flex-wrap: nowrap;
    }

    #user-manual-menu {
        box-sizing: border-box;
        width: 300px;
    }

    #user-manual-content {
        box-sizing: border-box;
        display: flex;
        justify-content: center;
        width: calc(100% - 300px);
        height: 100vh;
        overflow: auto;
    }

    @media only screen and (max-width: 768px) {
        #user-manual {
            flex-wrap: wrap;
        }

        #user-manual-menu {
            width: 100%;
            height: auto;
        }

        #user-manual-content {
            width: 100%;
            height: auto;
        }
    }

</style>

<script>
    export default {
        data: function() {
            return {
                selectedPage: 1,
                pages: [
                    {
                        name: 'Introduction',
                        id: 'introduction',
                    },
                    // {
                    //     id: 2,
                    //     name: 'Installation',
                    //     children: [
                    //         {
                    //             id: 3,
                    //             name: 'Linux'
                    //         },
                    //         {
                    //             id: 4,
                    //             name: 'Windows'
                    //         },
                    //         {
                    //             id: 5,
                    //             name: 'macOS'
                    //         }
                    //     ]
                    // },
                    {
                        name: 'Languages',
                        id: 'languages',
                    },
                    // {
                    //     id: 7,
                    //     name: 'Dictionaries',
                    // },
                    // {
                    //     id: 8,
                    //     name: 'Backup',
                    // },
                    // {
                    //     id: 9,
                    //     name: 'Updating',
                    // },
                    // {
                    //     id: 10,
                    //     name: 'Library',
                    // },
                    {
                        id: 'reading',
                        name: 'Reading',
                    },
                    // {
                    //     id: 12,
                    //     name: 'Reviewing',
                    // },
                    {
                        name: 'Vocabulary',
                        id: 'vocabulary',
                        children: [
                            {
                                name: 'Importing',
                                id: 'vocabulary-import',
                            },
                            // {
                            //     id: 15,
                            //     name: 'Exporting',
                            // }
                        ]
                    }
                ],
            }
        },
        mounted() {
            if (this.$route.params.currentPage !== undefined) {
                this.selectedPage = this.$route.params.currentPage;
            }
        },
        props: {
        },
        methods: {
            updateSelectedPage(event) {
                if (!event.length) {
                    return;
                }
                
                if (this.$router.currentRoute.path !== '/user-manual/' + event[0]) {
                    this.$router.push({ path: '/user-manual/' + event[0], replace: true });
                }
            }
        }
    }
</script>
 
