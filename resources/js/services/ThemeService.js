import { DefaultLocalStorageManager } from './LocalStorageManagerService'
import defaultThemes from './../themes';

const localStorageManager = DefaultLocalStorageManager

class ThemeService {
    constructor() {
        
    }

    setDefaultVuetifyTheme(vuetifyHandler) {
        // set vuetify theme
        var themeName = localStorageManager.loadSetting('theme') || 'light';
        vuetifyHandler.theme.dark = (themeName == 'dark');

        // set default theme
        vuetifyHandler.theme.themes['light'] = JSON.parse(JSON.stringify(defaultThemes.light));
        vuetifyHandler.theme.themes['dark'] = JSON.parse(JSON.stringify(defaultThemes.dark));
    }


    // applies the vuetify theme stored in the vuex store
    setVuetifyTheme(vuetifyHandler, storeHandler) {
        const vuetifyThemeSettings = storeHandler.state.shared.vuetifyThemeSettings

        if (vuetifyThemeSettings === null) {
            return
        }
        
        let themeSettingNames = Object.keys(defaultThemes.light)
        themeSettingNames.forEach((name) => {
            if (localStorageManager.loadSetting('theme') === 'eink') {
                console.log('defaultThemes eink', name, defaultThemes)
                vuetifyHandler.theme.themes['light'][name] = JSON.parse(JSON.stringify(defaultThemes['eink'][name]));
            } else {
                vuetifyHandler.theme.themes['light'][name] = vuetifyThemeSettings['light'][name];
            }
            vuetifyHandler.theme.themes['dark'][name] = vuetifyThemeSettings['dark'][name];
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
