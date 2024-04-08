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
            'lightTheme-highlightedWord',
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
            'darkTheme-highlightedWord',
            'darkTheme-readerWordSelection',
            'darkTheme-highlightedWordText',
        ];
    }

    loadTheme(defaultThemes, cookieHandler, vuetifyHandler) {
        // deep copy
        defaultThemes = JSON.parse(JSON.stringify(defaultThemes))

        var themeName = cookieHandler.get('theme') === null ? 'light' : cookieHandler.get('theme');
        vuetifyHandler.theme.dark = (themeName == 'dark');


        // load custom theme from cookie (cache) if saved
        var colors = cookieHandler.get(themeName + '-theme-colors');

        if (colors !== null && ['light', 'dark'].includes(themeName)) {
            vuetifyHandler.theme.themes[themeName] = JSON.parse(colors);
        } else {
            vuetifyHandler.theme.themes['light'] = cookieHandler.get('theme') === null ? defaultThemes.light : defaultThemes[cookieHandler.get('theme')];
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

            // save into cookie cache
            cookieHandler.set(themeName + '-theme-colors', JSON.stringify(vuetifyHandler.theme.themes[themeName]), 3650);
        }).catch((error) => {

        });
    }

    getCurrentTheme(cookieHandler) {
        return cookieHandler.get('theme') === null ? 'light' : cookieHandler.get('theme');
    }
}

export default new ThemeService();