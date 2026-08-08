import type { RouteRecordRaw } from 'vue-router'

import Home from '@components/pages/home/HomePage.vue'
import Attributions from '@components/pages/home/Attributions.vue'
import Login from '@components/pages/auth/Login.vue'
import UpdateHistory from '@components/pages/home/updates/UpdateHistory.vue'
import UpdateNote from '@components/pages/home/updates/UpdateNote.vue'
import UserSettings from '@components/pages/settings/UserSettings.vue'

export const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'Home',
        component: Home,
    },
    {
        path: '/attributions',
        name: 'Attributions',
        component: Attributions,
    },
    {
        path: '/updates/history',
        name: 'Update history',
        component: UpdateHistory,
    },
    {
        path: '/updates/:version',
        name: 'Update note',
        component: UpdateNote,
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
    },
    {
        path: '/settings',
        name: 'UserSettings',
        component: UserSettings,
    },
]
