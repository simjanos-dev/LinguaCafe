import { DefaultLocalStorageManager  } from "./LocalStorageManagerService";

class FontTypeService {
    constructor(language, fontTypesLoaded = null) {
        this.language = language;
        this.fonts = [];

        axios.get('/fonts/get-fonts-for-language/' + this.language).then((response) => {
            this.fonts = response.data;

            if (fontTypesLoaded !== null) {
                fontTypesLoaded();
            }
        });
    }

    getSelectedFontTypeId() {
        const selectedFontIdKey = `${this.language}-selected-font-type-id`;
        const selectedFontCookieId = DefaultLocalStorageManager.loadSetting(selectedFontIdKey);
        if (selectedFontCookieId !== null) {
            const selectedFontId = parseInt(selectedFontCookieId);

            // if the font in the localStorage does not exist, return null to avoid showing a deleted font as selected
            let fontExists = this.fonts.some(font => font.id === selectedFontId);

            return fontExists ? selectedFontId : null;
        } else if (!this.fonts.length) {
            return null;
        } else {
            return this.fonts[0].id;
        }
    }

    getSelectedFontTypeFileName() {
        const selectedFontId = this.getSelectedFontTypeId();
        if (!selectedFontId) return null;

        const selectedFont = this.fonts.find(font => font.id === selectedFontId);
        return selectedFont ? selectedFont.filename : null;
    }

    selectFontType(fontId) {
        const selectedFontIdKey = `${this.language}-selected-font-type-id`;
        DefaultLocalStorageManager.saveSetting(selectedFontIdKey, fontId);
    }

    loadSelectedFontTypeIntoDom() {
        const fileName = this.getSelectedFontTypeFileName();
        if (!fileName) return;

        let fontStyleText = `@font-face { font-family: selectedFont; src: url('/fonts/file/${fileName}'); } .selected-font { font-family: selectedFont !important; }`;
        document.getElementById('dynamic-selected-font').innerHTML = fontStyleText;
    }

    loadDefaultFontTypeIntoDom() {
        if (!this.fonts.length) return;

        const fileName = this.fonts[0].filename;
        let fontStyleText = `@font-face { font-family: defaultFont; src: url('/fonts/file/${fileName}'); } .default-font, .default-font * { font-family: defaultFont !important; }`;
        document.getElementById('dynamic-default-font').innerHTML = fontStyleText;
    }
}

export default FontTypeService;
