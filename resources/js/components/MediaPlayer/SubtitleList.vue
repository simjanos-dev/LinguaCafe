<template>
    <v-container class="d-flex flex-column justify-center flex-nowrap">
        <v-alert
            id="jellyfin-subtitle-info"
            class="mt-12 mb-6 rounded-lg"
            type="info"
            text
        >
            Choose a subtitle from the media currently being played on your Jellyfin server. You can import them seamlessly into LinguaCafe for later reading and also conveniently control your media player within LinguaCafe, allowing you to jump to specific subtitles or stop the movie.
        </v-alert>

        <v-card outlined id="subtitle-list" class="rounded-lg pa-4">
            <div id="subtitles-card-title">
                Subtitles
            </div>
            <div class="regular-list-height subtitle rounded-pill my-2">
                <div class="subtitle-language">Language</div>
                <div class="subtitle-user">User</div>
                <div class="subtitle-client">Client</div>
                <div class="subtitle-media">Media</div>
            </div>

            <template v-if="loading">
                <v-skeleton-loader
                    v-for="index in 3"
                    :key="index"
                    class="regular-list-height d-block skeleton rounded-pill my-2"
                    type="image"
                ></v-skeleton-loader>
            </template>

            <template v-for="(session, sessionIndex) in sessions" v-if="!loading">
                <div 
                    class="regular-list-height subtitle rounded-pill my-2" 
                    @click="selectSubtitle(sessionIndex, subtitleIndex)"
                    v-for="(subtitle, subtitleIndex) in session.subtitles"
                    :key="sessionIndex + '-' + subtitleIndex"
                >
                    <div class="subtitle-language">
                        <v-img class="border mx-auto" :src="'/images/flags/' + subtitle.language" max-width="43" height="28"></v-img> 
                    </div>
                    <div class="subtitle-user">{{ session.userName }}</div>
                    <div class="subtitle-client">{{ session.client }}</div>
                    <div class="subtitle-media">{{ session.seriesName }} {{ session.title }}</div>
                </div>
            </template>
        </v-card>
    </v-container>
</template>


<script>
const moment = require('moment');
export default {
    data: function () {
        return {
            loading: false,
            sessions: []
        }
    },
    props: {

    },
    mounted() {
        this.makeRequest();
    },
    methods: {
        makeRequest: function () {
            this.loading = true;
            this.sessions = [];
            
            axios.get('/jellyfin/subtitles').then(async (result) => {
                this.sessions = result.data;
                this.loading = false;
            });
        },
        selectSubtitle: function(selectedSession, selectedSubtitle) {
            this.$emit('subtitle-change', {
                subtitle: this.sessions[selectedSession].subtitles[selectedSubtitle].text,
                language: this.sessions[selectedSession].subtitles[selectedSubtitle].language,
                client: this.sessions[selectedSession].client,
                userName: this.sessions[selectedSession].userName,
                title: this.sessions[selectedSession].title,
                seriesName: this.sessions[selectedSession].seriesName,
                episode: this.sessions[selectedSession].episode,
                nowPlayingItemId: this.sessions[selectedSession].nowPlayingItemId,
                mediaSourceId: this.sessions[selectedSession].mediaSourceId
            });
        }
    }
}
</script>