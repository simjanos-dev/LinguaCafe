<template>
    <div>
        <v-card outlined class="mx-auto my-8 pa-6 rounded-xl" width="1500px">
            <!-- Method -->
            <v-label class="font-text-weight">Methods:</v-label>
            <v-radio-group
                v-model="method"
                class="mt-0"
                @change="save"
            >
                <v-radio
                    label="GET"
                    value="get"
                ></v-radio>
                <v-radio
                    label="POST"
                    value="post"
                ></v-radio>
            </v-radio-group>

            <!-- Url -->
            <v-label class="font-text-weight mt-4">Url:</v-label>
            <v-text-field
                v-model="url"
                filled
                rounded
                dense
                hide-details
                @keyup.enter="makeRequest"
                @keyup="save"
            ></v-text-field>

            <!-- Post data -->
            <v-label class="font-text-weight mt-4">Post data:</v-label>
            <v-textarea 
                v-model="postData" 
                filled 
                rounded
                no-resize
                @keydown.enter.shift.prevent="makeRequest"
                @keydown.enter.ctrl.prevent="makeRequest"
                @keyup="save"
            ></v-textarea>

            <v-card-actions>
                <v-btn 
                    rounded
                    depressed
                    color="primary"
                    @click="makeRequest"
                >Make request</v-btn>
            </v-card-actions>
        </v-card>

        <!-- Response -->
        <v-card 
            outlined 
            class="mx-auto mt-8 pa-6 pt-0 rounded-xl" 
            width="1500px"
            min-height="100px"
            :loading="loading"
            v-if="responseData !== 'init' || loading"
        >
            <template v-if="!loading">
                <v-label class="font-text-weight pt-6">Status: {{ responseStatus }}</v-label><br>

                <v-label class="font-text-weight mt-4">Response:</v-label>
                <pre class="text--text rounded-xl pa-6 mt-2" style="background-color: var(--v-gray3-base);"><!--
                    -->{{ responseData }}<!--
                --></pre>
            </template>
        </v-card>
    </div>
</template>

<script>
import axios from 'axios';

    export default {
        props: {
        },
        data: function() {
            return {
                loading: false,
                method: 'post',
                url: '/',
                postData: '{\r\n\r\n}',
                responseStatus: -1,
                responseData: 'init'
            };
        },
        mounted: function() {
            this.load();
        },
        methods: {
            makeRequest() {
                this.loading = true;

                axios({
                    method: this.method,
                    url: this.url,
                    data: this.method == 'get' ? '' : JSON.parse(this.postData)
                }).catch((error) => {
                    this.loading = false;
                    this.responseStatus = error.response.status;
                    this.responseData = error.response.data;
                }).then((response) => {
                    if (response === undefined) {
                        return;
                    }
                    
                    this.loading = false;
                    this.responseStatus = response.status;
                    this.responseData = response.data;
                });
            },
            save() {
                this.$cookie.set('development-tools', JSON.stringify({
                    method: this.method,
                    url: this.url,
                    postData: this.postData
                }), 3650);
            },
            load() {
                if (this.$cookie.get('development-tools') !== null) {
                    var data = JSON.parse(this.$cookie.get('development-tools'));
                    this.method = data.method;
                    this.url = data.url;
                    this.postData = data.postData;
                }

                if (this.postData === '') {
                    this.postData = '{\r\n\r\n}';
                }
            }
        }
    }
</script>
