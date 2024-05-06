class FontTypeService {
    constructor(language, cookieHandler, fontTypesLoaded = null) {
        this.cookieHandler = cookieHandler;
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
        if (this.cookieHandler.get(this.language + '-selected-font-type-id') !== null) {
            var selectedFontCookieId = this.cookieHandler.get(this.language + '-selected-font-type-id');
            selectedFontCookieId = parseInt(this.cookieHandler.get(this.language + '-selected-font-type-id'));
            
            // if the font in the cookie does not exist, this function should
            // return null to avoid showing a deleted font as selected
            var selectedFontId = null;
            this.fonts.forEach((font) => {
                if (font.id === selectedFontCookieId) {
                    selectedFontId = font.id;
                }
            });

            return selectedFontId;
        } else if (!this.fonts.length) {
            return null;
        } else {
            return this.fonts[0].id;
        }
    }

    getSelectedFontTypeFileName() {
        var selectedFontFileName = null;
        var selectedFontId = this.getSelectedFontTypeId();
        
        this.fonts.forEach((font) => {
            if (font.id === selectedFontId) {
                selectedFontFileName = font.filename;
            }
        });

        return selectedFontFileName;
    }
    
    selectFontType(fontId) {
        this.cookieHandler.set(this.language + '-selected-font-type-id', fontId, 3650);
    }

    loadSelectedFontTypeIntoDom() {
        var fileName = this.getSelectedFontTypeFileName();

        if (fileName === null) {
            return;
        }

        let fontStyleText = "@font-face { font-family: selectedFont; src: url('/fonts/file/" + fileName + "'); } .selected-font { font-family: selectedFont !important; }";
        document.getElementById('dynamic-selected-font').innerHTML = fontStyleText;
    }

    loadDefaultFontTypeIntoDom() {
        if (!this.fonts.length) {
            return;
        }

        var fileName = this.fonts[0].filename;
        let fontStyleText = "@font-face { font-family: defaultFont; src: url('/fonts/file/" + fileName + "'); } .default-font { font-family: defaultFont !important; }";
        document.getElementById('dynamic-default-font').innerHTML = fontStyleText;
    }
}

export default FontTypeService;
