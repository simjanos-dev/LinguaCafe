import { DefaultLocalStorageManager  } from "./LocalStorageManagerService";

class TextToSpeechService {
    constructor(language, voicesLoaded = null) {
        this.languageCodes = {
            'arabic': 'ar',
            'chinese': 'zh',
            'croatian': 'hr',
            'czech': 'cs',
            'danish': 'da',
            'dutch': 'nl',
            'english': 'en',
            'finnish': 'fi',
            'french': 'fr',
            'german': 'de',
            'greek': 'el',
            'italian': 'it',
            'japanese': 'ja',
            'korean': 'ko',
            'macedonian': 'mk',
            'norwegian': 'no',
            'polish': 'pl',
            'portuguese': 'pt',
            'romanian': 'ro',
            'russian': 'ru',
            'slovak': 'sk',
            'slovenian': 'sl',
            'spanish': 'es',
            'swedish': 'sv',
            'tamil': 'ta',
            'thai': 'th',
            'turkish': 'tr',
            'ukrainian': 'uk',
            'welsh': 'cy',
        };

        this.language = language;
        this.voices = this.getLanguageVoices();

        if (window.speechSynthesis !== undefined) {
            window.speechSynthesis.addEventListener("voiceschanged", () => {
                this.voices = this.getLanguageVoices();

                if (voicesLoaded !== null) {
                    voicesLoaded();
                }
            });
        }
    }

    getLanguageVoices() {
        const languageVoices = [];
        if (window.speechSynthesis === undefined) {
            return languageVoices;
        }

        const allVoices = speechSynthesis.getVoices();

        allVoices.forEach((voice) => {
            const languageCode = voice.lang.split('-')[0];
            if (this.languageCodes[this.language] === languageCode) {
                languageVoices.push(voice);
            }
        });

        return languageVoices;
    }

    getSelectedVoice() {
        if (window.speechSynthesis === undefined) {
            return null;
        }

        const selectedVoiceKey = `${this.language}-text-to-speech-voice`;
        const selectedVoiceName = DefaultLocalStorageManager.loadSetting(selectedVoiceKey);
        if (selectedVoiceName !== null) {
            const selectedVoice = this.voices.find(voice => voice.name === selectedVoiceName);
            return selectedVoice || null;
        } else if (!this.voices.length) {
            return null;
        } else {
            return this.voices[0];
        }
    }

    getVoiceNames() {
        if (window.speechSynthesis === undefined) {
            return [];
        }

        return this.voices.map(voice => voice.name);
    }

    getSpeechRate() {
        let rate = 1;
        const localStorageRate = DefaultLocalStorageManager.loadSetting('text-to-speech-speed')
        if (localStorageRate !== null) {
            rate = localStorageRate
        }

        return rate;
    }

    getSpeechRate() {
        let rate = 1;

        if (this.cookieHandler.get('text-to-speech-speed') !== null) {
            rate = this.cookieHandler.get('text-to-speech-speed');
        }

        return rate;
    }

    speak(text) {
        if (typeof speechSynthesis === "undefined") {
            return false;
        }

        const selectedVoice = this.getSelectedVoice();
        if (!selectedVoice) {
            return false;
        }

        const tts = new SpeechSynthesisUtterance();
        tts.text = text;
        tts.lang = this.languageCodes[this.language];
        tts.voice = selectedVoice;
        tts.rate = this.getSpeechRate();

        window.speechSynthesis.speak(tts);

        return true;
    }
}

export default TextToSpeechService;
