<template>
    <div id="user-manual">
        <!-- Menu -->
        <div id="user-manual-menu">
            <v-card outlined class="rounded-lg ma-4 pa-2 pl-0">
                <v-treeview
                    v-if="pages"
                    shaped
                    dense
                    hoverable
                    activatable
                    open-on-click
                    return-object
                    open-all
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
            <VueShowdown
                v-if="userManualFile"
                :markdown="userManualFile"
                :options="{ 
                    emoji: true,
                    tables: true
                }"
            />
        </div>
    </div>
</template>

<script>
import axios from 'axios';

    export default {
        data: function() {
            return {
                userManualFile: null,
                selectedPage: 'Home',
                pages: null
            }
        },
        mounted() {
            axios.get('/manual/get-menu-tree').then((response) => {
                this.pages = response.data;
            });
            
            
            if (this.$route.params.currentPage !== undefined) {
                this.selectedPage = this.$route.params.currentPage;
            }
            
            console.log('manual mounted', this.$route.params.currentPage);
            this.loadManualFile(this.selectedPage);
        },
        props: {
        },
        methods: {
            replaceElements(dom) {
                // admonitions
                dom = dom.replaceAll('[!NOTE]', '<admonition class="note"><i aria-hidden="true" class="v-icon notranslate mdi mdi-information-outline"></i> <span>Note</span></admonition>');
                dom = dom.replaceAll('[!TIP]', '<admonition class="tip"><i aria-hidden="true" class="v-icon notranslate mdi mdi-lightbulb-outline"></i> <span>Tip</span></admonition>');
                dom = dom.replaceAll('[!IMPORTANT]', '<admonition class="important-note"><i aria-hidden="true" class="v-icon notranslate mdi mdi-message-alert-outline"></i> <span>Important</span></admonition>');
                dom = dom.replaceAll('[!WARNING]', '<admonition class="warning"><i aria-hidden="true" class="v-icon notranslate mdi mdi-alert-outline"></i> <span>Warning</span></admonition>');
                dom = dom.replaceAll('[!CAUTION]', '<admonition class="caution"><i aria-hidden="true" class="v-icon notranslate mdi mdi-alert-circle-outline"></i> <span>Caution</span></admonition>');

                // flag images
                dom = dom.replaceAll('images/flags/', '/images/flags/');
                return dom;
            },
            updateSelectedPage(event) {
                if (!event.length) {
                    return;
                }
                
                var hash = '';
                if (window.location.hash) {
                    hash = window.location.hash;
                }

                var currentPath = '' + this.$router.currentRoute.path + hash;
                var newPath = '' + '/user-manual/' + event[0].fileName;
                if (currentPath !== newPath) {
                    this.$router.push({ path: '/user-manual/' + event[0].fileName, replace: true });
                }
                
            },
            loadManualFile(fileName) {
                this.userManualFile = null;
                axios.get('/manual/get-manual-file/' + fileName).then((response) => {
                    this.userManualFile = this.replaceElements(response.data);
                    
                    
                    if (window.location.hash) {
                        var hash = window.location.hash;
                        hash = decodeURI(hash);
                        hash = hash.toLowerCase().replaceAll(' ', '-').replaceAll('?', '').replaceAll(',', '').replaceAll('.', '');

                        this.$nextTick(() => {
                            document.querySelector(hash).scrollIntoView(hash);
                        });
                    }
                });
            }
        }
    }
</script>
 
