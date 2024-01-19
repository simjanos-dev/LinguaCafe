<template>
    <v-container 
        id="media-player" 
            :class="{
                'pt-0': textBlocks.length, 
                'fullscreen-box' : true,
                'fullscreen-mode': fullscreen,
            }"
        >

        <!-- Subtitle selection list -->
        <subtitle-list
            v-if="!textBlocks.length"
            :language="this.$props.language"
            :subtitleLoading="subtitleLoading"
            @subtitle-change="subtitleChange"
        ></subtitle-list>

        <!-- Media controls -->
        <div id="media-controls-box" class="pt-4" :style="{'max-width': maximumTextWidthData[maximumTextWidth]}" v-if="mediaControlsVisible">
            <v-card id="media-controls" outlined class="rounded-lg" v-if="textBlocks.length">
                <v-card-title id="media-controls-title">
                    {{ seriesName }} S{{ ('0' + seriesSeason).slice(-2) }}E{{ ('0' + seriesEpisode).slice(-2) }} - {{ mediaTitle }}
                    <v-spacer></v-spacer>
                    <v-btn icon @click="closeSubtitleReader"><v-icon>mdi-close</v-icon></v-btn>
                </v-card-title>
                <v-card-text class="px-8">
                    <v-progress-linear
                        color="primary"
                        height="36"
                        :value="this.positionTicks / this.runTimeTicks * 100"
                        class="rounded-pill"
                    >
                        <strong>{{ positionMinuteText }}: {{ positionSecondText }}</strong>
                    </v-progress-linear>
                </v-card-text>
                <v-card-actions class="d-flex justify-center mb-4">
                    <v-btn rounded color="primary" class="mx-2 mt-1" @click="pause" :disabled="paused"><v-icon>mdi-pause</v-icon></v-btn>
                    <v-btn rounded color="primary" class="mx-2 mt-1" @click="unpause" :disabled="!paused"><v-icon>mdi-play</v-icon></v-btn>
                </v-card-actions>
            </v-card>
        </div>
        
        <!-- Subtitle reader -->
        <subtitle-reader
            v-if="textBlocks.length"
            :_textBlocks="textBlocks"
            :mediaControlsVisible="mediaControlsVisible"
            :language="this.$props.language"
            @seekTo="seekTo"
            @settingsChanged="updateSettings"
        ></subtitle-reader>
    </v-container>
</template>


<script>
export default {
    data: function () {
        return {
            requestTimer: null,
            maximumTextWidthData: ['800px', '900px', '1000px', '1200px', '1400px', '1600px', '100%'],
            maximumTextWidth: 3,
            userId: '',
            sessionId: '',
            textBlocks: [],
            subtitleLoading: false,
            mediaControlsVisible: true,
            fullscreen: false,

            // selected subtitle info
            mediaTitle: '',
            seriesName: '',
            seriesSeason: '',
            seriesEpisode: '',

            // media states
            paused: false,
            positionTicks: 0,
            positionMinuteText: '',
            positionSecondText: '',
            runTimeTicks: 1,
        }
    },
    props: {
        language: String
    },
    mounted() {
    },
    beforeDestroy () {
        clearInterval(this.requestTimer);
    },
    methods: {
        seekTo: function(position) {
            this.positionTicks = position;
            axios.post('/jellyfin/request', {
                method: 'POST',
                url: '/Sessions/' + this.sessionId + '/Playing/Seek?seekPositionTicks=' + position
            }).then(() => {
                this.positionTicks = position;
                this.updatePlayState();
            });
        },
        pause: function() {
            axios.post('/jellyfin/request', {
                method: 'POST',
                url: '/Sessions/' + this.sessionId + '/Playing/Pause'
            }).then(() => {
                this.updatePlayState();
            });
        },
        unpause: function() {
            axios.post('/jellyfin/request', {
                method: 'POST',
                url: '/Sessions/' + this.sessionId + '/Playing/Unpause'
            }).then(() => {
                this.updatePlayState();
            });
        },
        updatePlayState: function() {
            axios.post('/jellyfin/request', {
                method: 'GET',
                url: '/Sessions'
            }).then(async (result) => {
                var sessions = result.data;
                for (var sessionIndex = 0; sessionIndex < sessions.length; sessionIndex++) {
                    if (sessions[sessionIndex].Id !== this.sessionId) {
                        continue;
                    }

                    // set current position
                    this.positionTicks = sessions[sessionIndex].PlayState.PositionTicks;
                    this.paused = sessions[sessionIndex].PlayState.IsPaused;

                    this.positionMinuteText = parseInt(sessions[sessionIndex].PlayState.PositionTicks / 1000 / 1000 / 10 / 60);
                    this.positionMinuteText = ('0' + this.positionMinuteText).slice(-2);
                    
                    this.positionSecondText = parseInt(sessions[sessionIndex].PlayState.PositionTicks / 1000 / 1000 / 10) % 60;
                    this.positionSecondText = ('0' + this.positionSecondText).slice(-2)

                    break;
                }
            });
        },
        updateSettings: function(settings, fullscreenMode) {
            this.maximumTextWidth = settings.maximumTextWidth;
            this.mediaControlsVisible = settings.mediaControlsVisible;
            this.fullscreen = fullscreenMode;
        },
        subtitleChange: function(selectedSubtitle) {
            clearInterval(this.requestTimer);
            this.subtitleLoading = true;
            this.textBlocks = [];
            this.sessionId = selectedSubtitle.sessionId;
            this.runTimeTicks = selectedSubtitle.runTimeTicks;
            this.userId = selectedSubtitle.userId;
            this.mediaTitle = selectedSubtitle.title;
            this.seriesName = selectedSubtitle.seriesName;
            this.seriesEpisode = selectedSubtitle.seriesEpisode;
            this.seriesSeason = selectedSubtitle.seriesSeason;

            axios.post('/jellyfin/process-subtitles', selectedSubtitle).then((result) => {
                this.subtitleLoading = false;

                // add isActive for lazy loading
                for (let textBlockIndex = 0; textBlockIndex < result.data.length; textBlockIndex++) {
                    result.data[textBlockIndex].isActive = false;
                }

                this.textBlocks = result.data
                
                this.updatePlayState();
                this.requestTimer = setInterval(this.updatePlayState, 1000);
            });
        },
        closeSubtitleReader: function() {
            this.textBlocks = [];
        }
    }
}
</script>