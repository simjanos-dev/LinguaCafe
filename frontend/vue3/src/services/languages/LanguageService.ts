import axios from 'axios'
import ApiCallService from '@services/ApiCallService'

import type { ApiCallResult } from '@src/types/apicall/ApiCallResult'
import type { Language } from '@lctypes/Language'

export default class LanguageService {
    apiCallService: ApiCallService

    constructor() {
        this.apiCallService = new ApiCallService()
    }

    async deleteLanguageData(language: Language | null): Promise<ApiCallResult<null>> {
        if (language === null) {
            return {
                ok: false,
                status: null,
            }
        }

        try {
            const response = await axios({
                method: 'DELETE',
                url: `/api/languages/` + language.name,
            })

            return {
                ok: true,
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
