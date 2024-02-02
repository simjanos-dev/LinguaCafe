<template>
    <div>
        <!-- Subtitle selection list -->
        <subtitle-list
            v-if="!text.length"
            :language="this.$props.language"
            :subtitleLoading="false"
            @subtitle-change="subtitleSelected"
        ></subtitle-list>

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
                    this.subtitles.push({
                        start: Math.round(selectedSubtitle.subtitle[i].StartPositionTicks / 1000 / 1000) / 10,
                        end: Math.round(selectedSubtitle.subtitle[i].EndPositionTicks / 1000 / 1000) / 10,
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
