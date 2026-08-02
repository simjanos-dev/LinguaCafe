import axios from 'axios'
import ApiCallService from '@services/ApiCallService'
import Store from '@src/store/Store'

import type { ApiCallResult } from '@src/types/apicall/ApiCallResult'
import type { LaravelResource } from '@lctypes/apicall/LaravelResource'
import type { Calendar } from '@lctypes/calendar/Calendar'

export default class GoalService {
    apiCallService: ApiCallService
    toastService: ReturnType<typeof useToast>

    constructor() {
        this.apiCallService = new ApiCallService()
        this.toastService = useToast()
    }

    async loadCalendarData(): Promise<ApiCallResult<Calendar>> {
        try {
            const response = await axios<LaravelResource<Calendar>>({
                method: 'GET',
                url: '/api/goals/calendar',
            })

            Store.calendar = response.data.data

            return {
                ok: true,
                data: response.data.data,
                status: response.status,
            }
        } catch (error: any) {
            return {
                ok: false,
                error: error ?? null,
                errorMessages: this.apiCallService.getErrorMessages(error),
                status: error?.response?.status ?? null,
            }
        }
    }
}
