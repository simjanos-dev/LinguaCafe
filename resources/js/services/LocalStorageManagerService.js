export const settingNames = {
    hideAllHighlights: 'hide-all-highlights',
    hideNewWordHighlights: 'hide-new-word-highlights',
    plainTextMode: 'plain-text-mode',
    fontSize: 'font-size',
    lineSpacing: 'line-spacing',
    maximumTextWidth: 'maximum-text-width',
    autoMoveWordsToKnown: 'auto-move-words-to-known',
    vocabBoxScrollIntoView: 'vocab-box-scroll-into-view',
    verticalText: 'vertical-text',
    furiganaOnHighlightedWords: 'furigana-on-highlighted-words',
    furiganaOnNewWords: 'furigana-on-new-words',
    vocabularySidebar: 'vocabulary-sidebar',
    vocabularyBottomSheet: 'vocabulary-bottom-sheet',
    vocabularyHoverBox: 'vocabulary-hover-box',
    vocabularyHoverBoxSearch: 'vocabulary-hover-box-search',
    vocabularyHoverBoxDelay: 'vocabulary-hover-delay',
    vocabularyHoverBoxPreferredPosition: 'vocabulary-hover-box-preferred-position',
    autoHighlightWords: 'auto-highlight-words',
    autoLevelUpWords: 'auto-level-up-words',
    showSubtitleTimestamps: 'show-subtitle-timestamps',
    spaceBetweenSubtitles: 'space-between-subtitles',
    mediaControlsVisible: 'media-controls-visible',
    subtitleBlockSpacing: 'subtitle-block-spacing',
    theme: 'theme',
    libraryLayout: 'library-layout',
    wordCountDisplayType: 'word-count-display-type'
};


export const defaultSettings = {
     // General Settings
     hideAllHighlights: false,
     hideNewWordHighlights: false,
     plainTextMode: false,
     fontSize: 20,
     lineSpacing: 1,
     maximumTextWidth: 3,
     autoMoveWordsToKnown: false,
     subtitleBlockSpacing: 1,
     vocabBoxScrollIntoView: 'scroll-into-view',
     verticalText: false,
     furiganaOnHighlightedWords: false,
     furiganaOnNewWords: false,
     mediaControlsVisible: true,
     vocabularySidebar: true,
     vocabularyBottomSheet: true,
     vocabularyHoverBox: true,
     vocabularyHoverBoxSearch: true,
     vocabularyHoverBoxDelay: 300,
     vocabularyHoverBoxPreferredPosition: 'bottom',
     autoHighlightWords: true,
     autoLevelUpWords: false,
     showSubtitleTimestamps: true,
     spaceBetweenSubtitles: 20,

     // Review Settings
     reviewSentenceMode: 'plain-text',

     // Theme Settings
     theme: 'light',

     // Library Layout
     libraryLayout: 'table',

     // Word Count Display Type
     wordCountDisplayType: 0,

     // Example Sentence Settings
     exampleSentence: [
         {
             id: -1,
             words: [],
             phrases: [],
             uniqueWords: [],
         }
     ],

     // Language Settings
     language: '',
     languageSpaces: false,

     // Review Specific
     practiceMode: false,
     readWords: 0,
     finishedReviews: -1,
     finished: false,
 };

export class LocalStorageManagerService {
    constructor(settingNames) {
        this.settingNames = settingNames;
    }

    saveSetting(key, value) {
        if (this.settingNames[key]) {
            localStorage.setItem(this.settingNames[key], JSON.stringify(value));
        } else {
            localStorage.setItem(key, JSON.stringify(value));
        }
    }

    loadSetting(key) {
        const value = localStorage.getItem(this.settingNames[key]) || localStorage.getItem(key);
        return value ? JSON.parse(value) : null;
    }

    loadAndParseSetting(settings, key, defaultValue) {
        const value = this.loadSetting(key);
        if (value === null) {
            settings[key] = defaultValue;
        } else {
            let type = typeof defaultValue;
            if (!Number.isInteger(defaultValue) && typeof defaultValue === 'number') {
                type = 'float';
            }
            if (type === 'boolean') {
                settings[key] = value === true;
            }

            if (type === 'number') {
                settings[key] = parseInt(value);
            }

            if (type === 'float') {
                settings[key] = parseFloat(value);
            }

            if (type === 'string') {
                settings[key] = value;
            }
        }
    }
    loadAndParseSettings(settings = defaultSettings) {
        const parsedSettings = {};
        for (const key in settings) {
            if (settings.hasOwnProperty(key)) {
                this.loadAndParseSetting(parsedSettings, key, settings[key]);
            }
        }
        return parsedSettings;
    }

    updateSetting(key, value) {
        this.saveSetting(key, value);
    }

    clearSetting(key) {
        if (this.settingNames[key]) {
            localStorage.removeItem(this.settingNames[key]);
        } else {
            localStorage.removeItem(key);
        }
    }

    clearAllSettings() {
        for (const key in this.settingNames) {
            if (this.settingNames.hasOwnProperty(key)) {
                localStorage.removeItem(this.settingNames[key]);
            }
        }
    }

    saveSettings(settings = defaultSettings) {
        for (const key in settings) {
            if (settings.hasOwnProperty(key)) {
                this.saveSetting(key, settings[key]);
            }
        }
    }
}

export const DefaultLocalStorageManager = new LocalStorageManagerService(settingNames);
