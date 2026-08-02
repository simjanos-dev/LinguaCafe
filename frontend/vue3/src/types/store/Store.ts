import type { User } from '@lctypes/User'
import type { Language } from '@lctypes/Language'
import type { Settings } from '@lctypes/settings/Settings'
import type { WindowData } from '@lctypes/store/WindowData'
import type { Calendar } from '@lctypes/calendar/Calendar'
import type { Goal } from '@lctypes/goals/Goal'

export type Store = {
    appDataInitialized: boolean
    user: null | User
    hasUser: boolean
    language: null | Language
    sidebarCollapsed: boolean
    window: WindowData
    settings: Settings
    calendar: null | Calendar
    dailyGoals: null | Goal[]
}
