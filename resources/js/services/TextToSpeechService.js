class TextToSpeechService {
    constructor(language, cookieHandler, voicesLoaded = null) {
        // BCP 47 language codes
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
            'thai': 'th',
            'turkish': 'tr',
            'ukrainian': 'uk',
            'welsh': 'cy',
        };

        this.cookieHandler = cookieHandler;
        this.language = language;
        this.voices = this.getLanguageVoices();
        
        window.speechSynthesis.addEventListener("voiceschanged", (event) => {
            this.voices = this.getLanguageVoices();

            if (voicesLoaded !== null) {
                voicesLoaded();
            }
        });
    }

    getLanguageVoices() {
        var languageVoices = [];
        var allVoices = speechSynthesis.getVoices();

        allVoices.forEach((voice) => {
            var languageCode = voice.lang.split('-')[0];
            if (this.languageCodes[this.language] === languageCode) {
                languageVoices.push(voice);
            }
        });

        return languageVoices;
    }

    getSelectedVoice() {
        if (this.cookieHandler.get(this.language + '-text-to-speech-voice') !== null) {
            var selectedVoiceName = this.cookieHandler.get(this.language + '-text-to-speech-voice');
            var selectedVoice = null;

            this.voices.forEach((voice) => {
                if (voice.name === selectedVoiceName) {
                    selectedVoice = voice;
                }
            });

            return selectedVoice;
        } else if (!this.voices.length) {
            return null;
        } else {
            return this.voices[0];
        }
    }

    getVoiceNames() {
        var voiceNames = [];

        this.voices.forEach((voice) => {
            voiceNames.push(voice.name);
        });

        return voiceNames;
    }

    speak(text) {
        if (typeof speechSynthesis === "undefined") {
            return false;
        }

        // create tts object
        var selectedVoice = this.getSelectedVoice();
        var tts = new SpeechSynthesisUtterance();
        tts.text = text;
        tts.lang = this.languageCodes[this.language];
        
        if (selectedVoice !== null) {
            tts.voice = selectedVoice;
        } else {
            return false;
        }
        
        // speak
        window.speechSynthesis.speak(tts);

        return true;
    }
}

export default TextToSpeechService;