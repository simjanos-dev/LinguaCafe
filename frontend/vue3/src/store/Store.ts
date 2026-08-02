import { reactive } from 'vue'
import shortcutSettings from '@config/Shortcuts'

import type { Store } from '@lctypes/store/Store.ts'

export default reactive<Store>({
    appDataInitialized: false,

    // user data
    user: null,
    hasUser: true,
    language: null,

    // window and layout
    sidebarCollapsed: false,
    window: {
        width: 0,
        widthWithoutSidebar: 0,
        height: 0,
    },

    // settings
    settings: {
        shortcuts: shortcutSettings,
    },

    calendar: null,
    dailyGoals: null,
})
