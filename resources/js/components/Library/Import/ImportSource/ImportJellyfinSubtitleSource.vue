<template>
    <div>
        <!-- Subtitle selection list -->
        <jellyfin-subtitle-list
            v-if="!text.length"
            :language="this.$props.language"
            :subtitleLoading="false"
            @subtitle-change="subtitleSelected"
        ></jellyfin-subtitle-list>

        <!-- Selected subtitle text -->
        <div 
            v-if="text.length"
            id="selected-jellyfin-subtitle"
            class="rounded-xl pa-6 mt-2"
            v-html="text"
        ></div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                text: '',
                subtitles: []
            }
        },
        props: {
            language: String,
        },
        mounted() {
        },
        methods: {
            subtitleSelected(selectedSubtitle) {
                for (let i = 0; i < selectedSubtitle.subtitle.length; i++) {
                    this.text += selectedSubtitle.subtitle[i].Text + '\n    ';
                    
                    var startMinutes = parseInt(Math.round(selectedSubtitle.subtitle[i].StartPositionTicks / 1000 / 1000) / 10 / 60).toString();
                    var startSeconds = parseInt((Math.round(selectedSubtitle.subtitle[i].StartPositionTicks / 1000 / 1000) / 10 ) % 60).toString();
                    var endMinutes = parseInt(Math.round(selectedSubtitle.subtitle[i].EndPositionTicks / 1000 / 1000) / 10 / 60).toString();
                    var endSeconds = parseInt((Math.round(selectedSubtitle.subtitle[i].EndPositionTicks / 1000 / 1000) / 10 ) % 60).toString();

                    // add leading zeroes
                    if (startMinutes.length == 1) {
                        startMinutes = '0' + startMinutes;
                    }

                    if (startSeconds.length == 1) {
                        startSeconds = '0' + startSeconds;
                    }

                    if (endMinutes.length == 1) {
                        endMinutes = '0' + endMinutes;
                    }

                    if (endSeconds.length == 1) {
                        endSeconds = '0' + endSeconds;
                    }

                    this.subtitles.push({
                        start: startMinutes + ':' + startSeconds,
                        end: endMinutes + ':' + endSeconds,
                        text: selectedSubtitle.subtitle[i].Text
                    });
                }
                
                this.text = this.text.replaceAll("\n", "<br><br>");
                this.$emit('subtitle-selected', {
                    subtitles: this.subtitles,
                    isImportSourceValid: true
                });
            }
        }
    }
</script>
