import { DefaultLocalStorageManager } from './LocalStorageManagerService'

const localStorageManager = DefaultLocalStorageManager

class ThemeService {
    constructor() {
        this.themeColorNames = [
            'lightTheme-background',
            'lightTheme-foreground',
            'lightTheme-navigation',
            'lightTheme-primary',
            'lightTheme-gray',
            'lightTheme-gray2',
            'lightTheme-gray3',
            'lightTheme-customBorder',
            'lightTheme-error',
            'lightTheme-info',
            'lightTheme-success',
            'lightTheme-warning',
            'lightTheme-text',
            'lightTheme-textDark',
            'lightTheme-newWord',
            'lightTheme-highlightedWordLevel1',
            'lightTheme-highlightedWordLevel2',
            'lightTheme-highlightedWordLevel3',
            'lightTheme-highlightedWordLevel4',
            'lightTheme-highlightedWordLevel5',
            'lightTheme-highlightedWordLevel6',
            'lightTheme-highlightedWordLevel7',
            'lightTheme-ignoredWordTextColor',
            'lightTheme-readerWordSelection',
            'lightTheme-highlightedWordText',

            'darkTheme-background',
            'darkTheme-foreground',
            'darkTheme-navigation',
            'darkTheme-primary',
            'darkTheme-gray',
            'darkTheme-gray2',
            'darkTheme-gray3',
            'darkTheme-customBorder',
            'darkTheme-error',
            'darkTheme-info',
            'darkTheme-success',
            'darkTheme-warning',
            'darkTheme-text',
            'darkTheme-textDark',
            'darkTheme-newWord',
            'darkTheme-highlightedWordLevel1',
            'darkTheme-highlightedWordLevel2',
            'darkTheme-highlightedWordLevel3',
            'darkTheme-highlightedWordLevel4',
            'darkTheme-highlightedWordLevel5',
            'darkTheme-highlightedWordLevel6',
            'darkTheme-highlightedWordLevel7',
            'darkTheme-ignoredWordTextColor',
            'darkTheme-readerWordSelection',
            'darkTheme-highlightedWordText',
        ];
    }

    loadTheme(defaultThemes, vuetifyHandler) {
        // deep copy
        defaultThemes = JSON.parse(JSON.stringify(defaultThemes))

        var themeName = localStorageManager.loadSetting('theme') || 'light';
        vuetifyHandler.theme.dark = (themeName == 'dark');


        // load custom theme from (cache) if saved
        var colors = localStorageManager.loadSetting(themeName + '-theme-colors');

        if (colors !== null && ['light', 'dark'].includes(themeName)) {
            vuetifyHandler.theme.themes[themeName] = JSON.parse(colors);
        } else {
            vuetifyHandler.theme.themes['light'] = localStorageManager.loadSetting('theme') === null ? defaultThemes.light : defaultThemes[localStorageManager.loadSetting('theme')];
            vuetifyHandler.theme.themes['dark'] = defaultThemes.dark;
        }

        if (!['light', 'dark'].includes(themeName)) {
            return;
        }

        // load custom theme from backend
        axios.post('/settings/user/get', {
            settingNames: this.themeColorNames
        }).then((response) => {
            var data = response.data;
            this.themeColorNames.forEach((value) => {
                if (data[value] !== undefined) {
                    var theme = value.split('-')[0].replace('Theme', '');
                    var colorName = value.split('-')[1];

                    vuetifyHandler.theme.themes[theme][colorName] = data[value];
                }
            });

            // save into cache
            localStorageManager.saveSetting(themeName + '-theme-colors', JSON.stringify(vuetifyHandler.theme.themes[themeName]));
        }).catch((error) => {

        });
    }

    getCurrentTheme() {
        return localStorageManager.loadSetting('theme') || 'light';
    }
}

export default new ThemeService();
