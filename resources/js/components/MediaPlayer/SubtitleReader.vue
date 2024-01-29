<template>
    <div id="subtitle-reader-box" :style="{'max-width': maximumTextWidthData[settings.maximumTextWidth]}">
        <!-- Settings -->
        <text-reader-settings
            v-show="settingsDialog"
            v-model="settingsDialog"
            ref="textReaderSettings"
            :enabledSettings="[
                'hideAllHighlights', 
                'hideNewWordHighlights', 
                'fontSize', 
                'lineSpacing', 
                'maximumTextWidth', 
                'vocabBoxScrollIntoView', 
                'furiganaOnHighlightedWords', 
                'furiganaOnNewWords', 
                'mediaControlsVisible',
                'vocabularySidebar',
                'vocabularyHoverBox'
            ]"
            @changed="updateSettings"
        ></text-reader-settings>

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
                    v-if="!fullscreenMode"
                >
                    <v-icon dark>mdi-arrow-expand-all</v-icon>
                </v-btn>
                <v-btn 
                    icon
                    class="mx-1" 
                    @click="toggleFullscreen"
                    title="Exit fullscreen" v-if="fullscreenMode" 
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
        <v-card 
            v-if="textBlocks.length"
            outlined 
            id="subtitle-reader" 
            class="rounded-lg pa-4 mx-auto" 
            :style="{
                'height': height,
                'padding-right': settings.vocabularySidebar && vocabularySidebarFits ? '400px !important' : '0px'
            }"
        >

            <div id="subtitle-reader-content" class="vocab-box-area">
                <text-block-group
                    ref="textBlockGroup"
                    :theme="'light'"
                    :fullscreen="false"
                    :_text-blocks="textBlocks"
                    :language="this.$props.language"
                    :hide-all-highlights="settings.hideAllHighlights"
                    :hide-new-word-highlights="settings.hideNewWordHighlights"
                    :plain-text-mode="false"
                    :font-size="settings.fontSize"
                    :line-spacing="settings.lineSpacing"
                    :vocab-box-scroll-into-view="settings.vocabBoxScrollIntoView"
                    :furigana-on-highlighted-words="settings.furiganaOnHighlightedWords"
                    :furigana-on-new-words="settings.furiganaOnNewWords"
                    :vocabulary-hover-box="settings.vocabularyHoverBox"
                    :vocabulary-sidebar="settings.vocabularySidebar"
                    :vocabulary-sidebar-fits="vocabularySidebarFits"
                    v-slot="slotProps"
                >
                    <template v-for="(textBlock, textBlockIndex) in slotProps.textBlocks">
                        
                            <div 
                                class="subtitle d-flex rounded-lg true" 
                                style="min-height: 200px"
                            >
                                <v-lazy
                                    v-model="slotProps.textBlocks[textBlockIndex].isActive"
                                    :options="{
                                        threshold: .5
                                    }"
                                    
                                >
                                    <div class="d-flex">
                                        <div class="subtitle-timestamp-box d-flex flex-column justify-start flex-nowrap">
                                            <span 
                                                class="subtitle-timestamp d-flex"
                                                @click="seekTo(slotProps.textBlocks[textBlockIndex].start)"
                                            >
                                                {{ slotProps.textBlocks[textBlockIndex].startText }}
                                                <v-icon>mdi-skip-next</v-icon>
                                            </span>
                                        </div>
                                        <div class="subtitle-content rounded-lg">
                                            <text-block
                                                :key="slotProps.textBlocks[textBlockIndex].id"
                                                ref="textBlock"
                                                :textBlockId="slotProps.textBlocks[textBlockIndex].id"
                                                :_words="slotProps.textBlocks[textBlockIndex].words"
                                                :_phrases="slotProps.textBlocks[textBlockIndex].phrases"
                                                :_uniqueWords="slotProps.textBlocks[textBlockIndex].uniqueWords"
                                                :language="slotProps.language"
                                                :hideAllHighlights="slotProps.hideAllHighlights"
                                                :hideNewWordHighlights="slotProps.hideNewWordHighlights"
                                                :fontSize="slotProps.fontSize"
                                                :lineSpacing="slotProps.lineSpacing"
                                                :furiganaOnHighlightedWords="slotProps.furiganaOnHighlightedWords"
                                                :furiganaOnNewWords="slotProps.furiganaOnNewWords"
                                                @textSelected="slotProps.updateSelection"
                                                @unselectAllWords="slotProps.unselectAllWords"
                                                @updateLookupCount="slotProps.updateLookupCount"
                                                @startSelection="slotProps.startSelection"
                                                @updateHoveredWords="slotProps.updateHoveredWords"
                                            ></text-block>
                                        </div>
                                    </div>
                                </v-lazy>
                            </div>
                    </template>
                </text-block-group>
            </div>
        </v-card>
    </div>
</template>

<script>
export default {
    data: function () {
        return {
            height: '0px',
            maximumTextWidthData: ['800px', '900px', '1000px', '1200px', '1400px', '1600px', '100%'],
            settingsDialog: false,
            fullscreenMode: false,
            vocabularySidebarFits: true,
            settings: {
                furiganaOnHighlightedWords: false,
                furiganaOnNewWords: false,
                maximumTextWidth: 3,
                fontSize: 20,
                lineSpacing: 1,
                hideAllHighlights: false,
                hideNewWordHighlights: false,
                autoMoveWordsToKnown: false,
                mediaControlsVisible: this.$props.mediaControlsVisible,
                vocabBoxScrollIntoView: 'scroll-into-view',
                vocabularyHoverBox: true
            },
            textBlocks: this.$props._textBlocks,
            unloadInvisibleTextBlocksInterval: null,
            scrollEvent: null,
        } 
    },
    props: {
        language: String,
        _textBlocks: {
            type: Array,
            default: []
        },
        mediaControlsVisible: {
            type: Boolean,
            default: false
        }
    },
    mounted: function() {
        this.unloadInvisibleTextBlocksInterval = setInterval(this.unloadInvisibleTextBlocks, 5000);
        window.addEventListener('scroll', this.updateVocabBoxPosition);
        window.addEventListener('resize', this.updateVocabBoxPosition);
        window.addEventListener('resize', this.updateVocabularySidebar);

        this.$nextTick(this.updateVocabularySidebar);
    },
    beforeDestroy() {
        clearTimeout(this.unloadInvisibleTextBlocksInterval);
        window.removeEventListener('scroll', this.updateVocabBoxPosition);
        window.removeEventListener('resize', this.updateVocabBoxPosition);
        window.removeEventListener('resize', this.updateVocabularySidebar);
    },
    methods: {
        updateVocabularySidebar() {
            this.vocabularySidebarFits = window.innerWidth >= 960;
            
            // sidebar does not have enough space if media controls are visible
            if (this.settings.mediaControlsVisible) {
                this.vocabularySidebarFits = false;
            }

            // set subtitle reader height
            if (this.$vuetify.breakpoint.mdAndUp && this.settings.mediaControlsVisible) {
                this.height = 'calc(100% - 24px - 24px - 240px)';
            }

            if (this.$vuetify.breakpoint.mdAndUp && !this.settings.mediaControlsVisible) {
                this.height = 'calc(100% - 24px - 24px - 30px)';
            }

            if (!this.$vuetify.breakpoint.mdAndUp && this.settings.mediaControlsVisible) {
                this.height = 'calc(100% - 24px - 24px - 240px - 56px)';
            }

            if (!this.$vuetify.breakpoint.mdAndUp && !this.settings.mediaControlsVisible) {
                this.height = 'calc(100% - 24px - 24px - 30px - 56px)';
            }
            //  : 'calc(100% - 24px - 24px)'
        },
        updateVocabBoxPosition() {
            setTimeout(() => {
                this.$refs.textBlockGroup.updateVocabBoxPosition();
            }, 200);
        },
        unloadInvisibleTextBlocks() {
            var visibleCount = 0;
            var subtitleDoms = document.getElementsByClassName('subtitle');
            for(let i = 0; i < subtitleDoms.length; i++) {
                var rect = subtitleDoms[i].getBoundingClientRect();
                var subtitleTop = rect.top;
                var subtitleBottom = rect.bottom;

                var isVisible = (subtitleTop >= -500) && (subtitleBottom <= window.innerHeight + 500);
                this.textBlocks[i].isActive = isVisible;
                if (isVisible) {
                    visibleCount++;
                }
            }

            this.updateVocabBoxPosition();
        },
        seekTo: function(position) {
            this.$emit('seekTo', position);
        },
        updateSettings(settings) {
            this.settings = settings;
            this.$emit('settingsChanged', settings, this.fullscreenMode);
            this.updateVocabularySidebar();
            this.updateVocabBoxPosition();
            this.$forceUpdate();
        },
        toggleFullscreen: function() {
            if (this.fullscreenMode) {
                document.exitFullscreen();
                this.fullscreenMode = false;
            } else if (document.fullscreenEnabled) {
                document.getElementsByClassName('fullscreen-box')[0].requestFullscreen();
                this.fullscreenMode = true;
            }
        },
        openSettings: function() {
            if (this.fullscreenMode) {
                this.toggleFullscreen();
            }
            
            this.settingsDialog = true;
        },
        increaseFontSize: function() {
            this.settings.fontSize ++;
            this.toolbarSettingChanged();
        },
        decreaseFontSize: function() {
            this.settings.fontSize --;
            this.toolbarSettingChanged();
        },
        toggleMediaPlayer: function() {
            this.settings.mediaControlsVisible = !this.settings.mediaControlsVisible;
            this.toolbarSettingChanged();
        },
        toolbarSettingChanged() {
            this.$refs.textReaderSettings.changeSetting('fontSize', this.settings.fontSize);
            this.$refs.textReaderSettings.changeSetting('mediaControlsVisible', this.settings.mediaControlsVisible, true);
        }
    }
}
</script>