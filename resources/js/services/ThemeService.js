import { DefaultLocalStorageManager } from './LocalStorageManagerService'
import defaultThemes from './../themes';
import defaultTextThemes from './../themes';

const localStorageManager = DefaultLocalStorageManager

class ThemeService {
    constructor() {
        
    }

    loadTheme(vuetifyHandler) {
        // deep copy
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
            settingNames: ['vuetifyThemes']
        }).then((response) => {
            if (!response.data.vuetifyThemes) {
                return
            }

            let themeSettingNames = Object.keys(defaultThemes.light)
            themeSettingNames.forEach((name) => {
                vuetifyHandler.theme.themes['light'][name] = response.data.vuetifyThemes['light'][name];
                vuetifyHandler.theme.themes['dark'][name] = response.data.vuetifyThemes['dark'][name];
            });

            // save into cache
            localStorageManager.saveSetting(themeName + '-theme-colors', JSON.stringify(vuetifyHandler.theme.themes[themeName]));
        }).catch((error) => {

        });
    }

    getCurrentTheme() {
        if (localStorageManager.loadSetting('theme-auto')) {
            return 'auto'
        } else {
            return localStorageManager.loadSetting('theme') || 'light';
        };
    }

    getAutoTheme() {
        let autoTheme;
        if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
            autoTheme = 'dark';
        } else {
            autoTheme = 'light';
        }

        return autoTheme;
    }

    isAuto() {
        return localStorageManager.loadSetting('theme-auto') === true;
    }
}

export default new ThemeService();
