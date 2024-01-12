<template>
    <div id="subtitle-reader-box" :style="{'max-width': maximumTextWidthData[settings.maximumTextWidth]}">
        <!-- Settings -->
        <subtitle-reader-settings
            v-if="loaded && settingsDialog"
            v-model="settingsDialog"
            :_hide-all-highlights="settings.hideAllHighlights"
            :_hide-new-word-highlights="settings.hideNewWordHighlights"
            :_plain-text-mode="settings.plainTextMode"
            :_font-size="settings.fontSize"
            :_line-spacing="settings.lineSpacing"
            :_maximum-text-width="settings.maximumTextWidth"
            :_auto-move-words-to-known="settings.autoMoveWordsToKnown"
            :_media-controls-visible="settings.mediaControlsVisible"
            :_subtitle-block-spacing="settings.subtitleBlockSpacing"
            :_vocab-box-scroll-into-view="settings.vocabBoxScrollIntoView"
            @changed="saveSettings"   
        ></subtitle-reader-settings>

        <!-- Toolbar buttons -->
        <div 
            id="subtitle-reader-toolbar-box" 
            :class="{'media-controls-visible': $props.mediaControlsVisible}"
        >
            <v-card
                outlined 
                id="subtitle-reader-toolbar" 
                class="rounded-lg px-1"
            >
                <v-btn 
                    icon
                    class="mx-1" 
                    @click="toggleFullscreen"
                    title="Fullscreen" 
                    v-if="!settings.fullscreen"
                >
                    <v-icon dark>mdi-arrow-expand-all</v-icon>
                </v-btn>
                <v-btn 
                    icon
                    class="mx-1" 
                    @click="toggleFullscreen"
                    title="Exit fullscreen" v-if="settings.fullscreen" 
                >
                    <v-icon dark>mdi-arrow-collapse-all</v-icon>
                </v-btn>
                <v-btn 
                    icon
                    class="mx-1" 
                    @click="openSettings"
                    title="Settings"
                >
                    <v-icon dark>mdi-cog</v-icon>
                </v-btn>
                <v-btn 
                    icon
                    class="mx-1" 
                    @click="toggleMediaPlayer"
                    title="Toggle media controls"
                >
                    <v-icon :color="settings.mediaControlsVisible ? 'primary' : ''">mdi-movie-play</v-icon>
                </v-btn>
                <v-btn 
                    icon
                    class="mx-1" 
                    @click="increaseFontSize"
                    title="Increase font size"
                >
                    <v-icon dark>mdi-magnify-plus</v-icon>
                </v-btn>
                <v-btn 
                    icon
                    class="mx-1" 
                    @click="decreaseFontSize"
                    title="Decrease font size"
                >
                    <v-icon dark>mdi-magnify-minus</v-icon>
                </v-btn>
            </v-card>
        </div>

        <!-- Subtitle reader -->
        <v-card id="subtitle-reader" outlined class="vocab-box-area rounded-lg pa-4 mx-auto" v-if="textBlocks.length">
            <text-block-group
                :theme="'light'"
                :fullscreen="false"
                :_text-blocks="textBlocks"
                :language="'japanese'"
                :hide-all-highlights="settings.hideAllHighlights"
                :hide-new-word-highlights="settings.hideNewWordHighlights"
                :plain-text-mode="false"
                :font-size="settings.fontSize"
                :line-spacing="settings.lineSpacing"
                :vocab-box-scroll-into-view="settings.vocabBoxScrollIntoView"
                v-slot="slotProps"
            >
                <template v-for="(textBlock, textBlockIndex) in slotProps.textBlocks">
                    <div 
                        class="subtitle d-flex rounded-lg true" 
                        :style="{'padding-bottom': (settings.subtitleBlockSpacing * 16) + 'px'}"
                    >
                        <div class="d-flex flex-column justify-start flex-nowrap">
                            <span 
                                class="subtitle-timestamp d-flex"
                                @click="seekTo(textBlock.start)"
                            >
                                {{ textBlock.startText }}
                                <v-icon>mdi-skip-next</v-icon>
                            </span>
                        </div>
                        <div class="subtitle-content rounded-lg">
                            <text-block
                                :key="textBlock.id"
                                ref="textBlock"
                                :textBlockId="textBlock.id"
                                :_words="textBlock.words"
                                :_phrases="textBlock.phrases"
                                :_uniqueWords="textBlock.uniqueWords"
                                :language="slotProps.language"
                                :hideAllHighlights="slotProps.hideAllHighlights"
                                :hideNewWordHighlights="slotProps.hideNewWordHighlights"
                                :plainTextMode="slotProps.plainTextMode"
                                :fontSize="slotProps.fontSize"
                                :lineSpacing="slotProps.lineSpacing"
                                @textSelected="slotProps.updateSelection"
                                @saveSelectedWord="slotProps.saveSelectedWord"
                                @updateLookupCount="slotProps.updateLookupCount"
                            ></text-block>
                        </div>
                    </div>
                </template>
            </text-block-group>
        </v-card>
    </div>
</template>

<script>
export default {
    data: function () {
        return {
            loaded: false,
            maximumTextWidthData: ['800px', '1000px', '1200px', '1400px', '1600px', '100%'],
            settingsDialog: false,
            settings: {
                maximumTextWidth: 3,
                fontSize: 20,
                lineSpacing: 1,
                subtitleBlockSpacing: 1,
                hideAllHighlights: false,
                hideNewWordHighlights: false,
                plainTextMode: false,
                autoMoveWordsToKnown: false,
                fullscreen: false,
                mediaControlsVisible: this.$props.mediaControlsVisible,
                vocabBoxScrollIntoView: 'scroll-into-view'
            }
        } 
    },
    props: {
        textBlocks: {
            type: Array,
            default: []
        },
        mediaControlsVisible: {
            type: Boolean,
            default: false
        }
    },
    mounted: function() {
        this.loadSetting('hideAllHighlights', 'subtitle-hide-all-highlights', 'boolean', false);
        this.loadSetting('hideNewWordHighlights', 'subtitle-hide-new-word-highlights', 'boolean', false);
        this.loadSetting('plainTextMode', 'subtitle-plain-text-mode', 'boolean', false);
        this.loadSetting('fontSize', 'subtitle-font-size', 'integer', 20);
        this.loadSetting('lineSpacing', 'subtitle-line-spacing', 'integer', 1);
        this.loadSetting('maximumTextWidth', 'subtitle-maximum-text-width', 'integer', 3);
        this.loadSetting('autoMoveWordsToKnown', 'subtitle-auto-move-words-to-known', 'boolean', false);
        this.loadSetting('subtitleBlockSpacing', 'subtitle-block-spacing', 'integer', 1);
        this.loadSetting('vocabBoxScrollIntoView', 'subtitle-vocab-box-scroll-into-view', 'string', 'scroll-into-view');

        this.saveSettings();
        this.loaded = true;
    },
    methods: {
        seekTo: function(position) {
            this.$emit('seekTo', position);
        },
        loadSetting: function(name, cookieName, type, defaultValue) {
            if (this.$cookie.get(cookieName) === null) {
                this.settings[name] = defaultValue;
            } else {
                if (type == 'boolean') {
                    this.settings[name] = this.$cookie.get(cookieName) === 'true';
                }

                if (type == 'integer') {
                    this.settings[name] = parseInt(this.$cookie.get(cookieName));
                }

                if (type == 'string') {
                    this.settings[name] = this.$cookie.get(cookieName);
                }
            }

        },
        saveSettings: function(newSettings = null) {
            if (newSettings !== null) {
                this.settings = newSettings;
            }

            if (this.settings.fontSize < 12) {
                this.settings.fontSize = 12;
            }

            if (this.settings.fontSize > 30) {
                this.settings.fontSize = 30;
            }

            this.$cookie.set('subtitle-hide-all-highlights', this.settings.hideAllHighlights, 3650);
            this.$cookie.set('subtitle-hide-new-word-highlights', this.settings.hideNewWordHighlights, 3650);
            this.$cookie.set('subtitle-plain-text-mode', this.settings.plainTextMode, 3650);
            this.$cookie.set('subtitle-font-size', this.settings.fontSize, 3650);
            this.$cookie.set('subtitle-line-spacing', this.settings.lineSpacing, 3650);
            this.$cookie.set('subtitle-maximum-text-width', this.settings.maximumTextWidth, 3650);
            this.$cookie.set('subtitle-auto-move-words-to-known', this.settings.autoMoveWordsToKnown, 3650);
            this.$cookie.set('subtitle-block-spacing', this.settings.subtitleBlockSpacing, 3650);
            this.$cookie.set('subtitle-vocab-box-scroll-into-view', this.settings.vocabBoxScrollIntoView, 3650);

            this.$emit('settingsChange', this.settings);
        },
        toggleMediaPlayer: function() {
            this.settings.mediaControlsVisible = !this.settings.mediaControlsVisible;
            this.$emit('settingsChange', this.settings);
        },
        toggleFullscreen: function() {
            if (this.settings.fullscreen) {
                document.exitFullscreen();
                this.settings.fullscreen = false;
            } else if (document.fullscreenEnabled) {
                document.getElementsByClassName('fullscreen-box')[0].requestFullscreen();
                this.settings.fullscreen = true;
            }
            
            this.$emit('settingsChange', this.settings);
        },
        openSettings: function() {
            if (this.settings.fullscreen) {
                this.toggleFullscreen();
            }
            
            this.settingsDialog = true;
        },
        increaseFontSize: function() {
            this.settings.fontSize ++;
            this.saveSettings();
        },
        decreaseFontSize: function() {
            this.settings.fontSize --;
            this.saveSettings();
        }
    }
}
</script>