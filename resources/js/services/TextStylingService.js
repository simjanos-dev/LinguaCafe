class TextStylingService {
    
    constructor() {
        
    }

    getTextStylingSettingsObject(textStylingSettings) {
        const themes = ['light', 'dark', 'eink']
        const levels = [
            'Ignored word',
            'New word',
            'Known word',
            'Level 1 word',
            'Level 2 word',
            'Level 3 word',
            'Level 4 word',
            'Level 5 word',
            'Level 6 word',
            'Level 7 word',
            'Known phrase',
            'Level 1 phrase',
            'Level 2 phrase',
            'Level 3 phrase',
            'Level 4 phrase',
            'Level 5 phrase',
            'Level 6 phrase',
            'Level 7 phrase',
        ]

        let settings = {}
        themes.forEach((theme) => {
            settings[theme] = {}
            levels.forEach((level) => {
                Object.assign(settings[theme], this.getCssSettingObject(textStylingSettings, theme, level))
            })
        })

        return settings
    }

    // returns an object with css styling for a single theme/word level combination
    getCssSettingObject(textStylingSettings, theme, level) {
        console.log('getCssSettingObject start')
        const levelMapping = {
            'Level 1 word': 'wordLevel-1',
            'Level 2 word': 'wordLevel-2',
            'Level 3 word': 'wordLevel-3',
            'Level 4 word': 'wordLevel-4',
            'Level 5 word': 'wordLevel-5',
            'Level 6 word': 'wordLevel-6',
            'Level 7 word': 'wordLevel-7',
            'Known word': 'wordLevel0',
            'Ignored word': 'wordLevel1',
            'New word': 'wordLevel2',
            'Selected word': 'wordLevelSelected',
            'Known phrase': 'phraseLevel0',
            'Level 1 phrase': 'phraseLevel-1',
            'Level 2 phrase': 'phraseLevel-2',
            'Level 3 phrase': 'phraseLevel-3',
            'Level 4 phrase': 'phraseLevel-4',
            'Level 5 phrase': 'phraseLevel-5',
            'Level 6 phrase': 'phraseLevel-6',
            'Level 7 phrase': 'phraseLevel-7',
            'Selected pharse': 'phraseLevelSelected',
        }

        let cssVariables = {}
        
        cssVariables[`--interactive-text-${levelMapping[level]}-color`] = textStylingSettings[theme][level].textColor;
        cssVariables[`--interactive-text-${levelMapping[level]}-border-color`] = textStylingSettings[theme][level].borderColor;
        cssVariables[`--interactive-text-${levelMapping[level]}-border-style`] = textStylingSettings[theme][level].borderStyle;
        cssVariables[`--interactive-text-${levelMapping[level]}-border-radius`] = textStylingSettings[theme][level].borderRadius + 'px';
        
        
        // padding
        cssVariables[`--interactive-text-${levelMapping[level]}-padding-top`] = textStylingSettings[theme][level].paddingTop + 'px';
        cssVariables[`--interactive-text-${levelMapping[level]}-padding-bottom`] = textStylingSettings[theme][level].paddingBottom + 'px';
        cssVariables[`--interactive-text-${levelMapping[level]}-padding-left`] = textStylingSettings[theme][level].paddingHorizontal + 'px';
        cssVariables[`--interactive-text-${levelMapping[level]}-padding-right`] = textStylingSettings[theme][level].paddingHorizontal + 'px';
        cssVariables[`--interactive-text-${levelMapping[level]}-spaceless-language-padding-left`] = textStylingSettings[theme][level].paddingHorizontal + 'px';
        cssVariables[`--interactive-text-${levelMapping[level]}-spaceless-language-padding-right`] = textStylingSettings[theme][level].paddingHorizontal + 'px';
        
        

        // horizontal padding for spaceless languages only
        if (textStylingSettings[theme][level].horizontalPaddingSpacelessLanguagesOnly) {
            cssVariables[`--interactive-text-${levelMapping[level]}-padding-left`] = '0px';
            cssVariables[`--interactive-text-${levelMapping[level]}-padding-right`] = '0px';
        }
        
        // add colors 
        cssVariables[`--interactive-text-${levelMapping[level]}-background-transparency`] = textStylingSettings[theme][level].backgroundTransparency + '%';
        cssVariables[`--interactive-text-${levelMapping[level]}-border-color`] = textStylingSettings[theme][level].borderColor;
        cssVariables[`--interactive-text-${levelMapping[level]}-background-color`] = textStylingSettings[theme][level].backgroundColor;
        cssVariables[`--interactive-text-${levelMapping[level]}-color`] = textStylingSettings[theme][level].textColor;

        // set top border
        if (!textStylingSettings[theme][level].borderTop || !textStylingSettings[theme][level].borderWidth) {
            cssVariables[`--interactive-text-${levelMapping[level]}-border-top-width`] = '0px'
        } else {
            cssVariables[`--interactive-text-${levelMapping[level]}-border-top-width`] = textStylingSettings[theme][level].borderWidth + 'px';
        }

        // set bottom border
        if (!textStylingSettings[theme][level].borderBottom || !textStylingSettings[theme][level].borderWidth) {
            cssVariables[`--interactive-text-${levelMapping[level]}-border-bottom-width`] = '0px'
        } else {
            cssVariables[`--interactive-text-${levelMapping[level]}-border-bottom-width`] = textStylingSettings[theme][level].borderWidth + 'px';
        }

        // set side borders
        if (!textStylingSettings[theme][level].borderSides || !textStylingSettings[theme][level].borderWidth) {
            cssVariables[`--interactive-text-${levelMapping[level]}-border-left-width`] = '0px'
            cssVariables[`--interactive-text-${levelMapping[level]}-border-right-width`] = '0px'
        } else {
            cssVariables[`--interactive-text-${levelMapping[level]}-border-left-width`] = textStylingSettings[theme][level].borderWidth + 'px';
            cssVariables[`--interactive-text-${levelMapping[level]}-border-right-width`] = textStylingSettings[theme][level].borderWidth + 'px';
        }

        // add bold styling
        if (textStylingSettings[theme][level].bold) {
            cssVariables[`--interactive-text-${levelMapping[level]}-weight`] = 'bold'
        } else {
            cssVariables[`--interactive-text-${levelMapping[level]}-weight`] = 'normal'
        }

        // add italic styling
        if (textStylingSettings[theme][level].italic) {
            cssVariables[`--interactive-text-${levelMapping[level]}-style`] = 'italic'
        } else {
            cssVariables[`--interactive-text-${levelMapping[level]}-style`] = 'normal'
        }

        // add wavy underline
        if (textStylingSettings[theme][level].wavyUnderline) {
            cssVariables[`--interactive-text-${levelMapping[level]}-text-decoration`] = 'underline'
            cssVariables[`--interactive-text-${levelMapping[level]}-wave-width`] = textStylingSettings[theme][level].borderWidth + 'px'
            cssVariables[`--interactive-text-${levelMapping[level]}-border-bottom-width`] = '0px'
            cssVariables[`--interactive-text-${levelMapping[level]}-border-top-width`] = '0px'
            cssVariables[`--interactive-text-${levelMapping[level]}-border-left-width`] = '0px'
            cssVariables[`--interactive-text-${levelMapping[level]}-border-right-width`] = '0px'
        } else {
            cssVariables[`--interactive-text-${levelMapping[level]}-text-decoration`] = 'none'
        }
        

        console.log('getCssSettingObject end')
        return cssVariables
    }
}

export default new TextStylingService();
