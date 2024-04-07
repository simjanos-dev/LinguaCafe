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
        vuetifyHandler.theme.themes['light'] = cookieHandler.get('theme') === null ? defaultThemes.light : defaultThemes[cookieHandler.get('theme')];
        vuetifyHandler.theme.themes['dark'] = defaultThemes.dark;
        vuetifyHandler.theme.dark = (themeName == 'dark');


        // load custom theme from cookie (cache) if saved
        var themeName = cookieHandler.get('theme') === null ? 'light' : cookieHandler.get('theme');

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
        })
    }

    getCurrentTheme(cookieHandler) {
        return cookieHandler.get('theme') === null ? 'light' : cookieHandler.get('theme');
    }
}

export default new ThemeService();