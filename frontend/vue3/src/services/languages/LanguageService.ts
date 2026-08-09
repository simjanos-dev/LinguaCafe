import axios from 'axios'
import ApiCallService from '@services/ApiCallService'

import type { ApiCallResult } from '@src/types/apicall/ApiCallResult'
import type { Language } from '@lctypes/Language'
import type { LaravelResource } from '@lctypes/apicall/LaravelResource'

export default class LanguageService {
    apiCallService: ApiCallService

    constructor() {
        this.apiCallService = new ApiCallService()
    }

    async getInstallRequiredLanguages(): Promise<ApiCallResult<Language[]>> {
        try {
            const response = await axios<LaravelResource<Language[]>>({
                method: 'GET',
                url: '/api/admin/languages',
            })

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

    async getInstalledLanguages(): Promise<ApiCallResult<Language[]>> {
        try {
            const response = await axios<LaravelResource<Language[]>>({
                method: 'GET',
                url: '/api/admin/languages/installed',
            })

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

    async installLanguage(language: Language): Promise<ApiCallResult<null>> {
        try {
            const response = await axios({
                method: 'POST',
                url: `/api/admin/languages/install`,
                data: {
                    language: language.name,
                },
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

    async uninstallLanguages(): Promise<ApiCallResult<null>> {
        try {
            const response = await axios({
                method: 'DELETE',
                url: `/api/admin/languages`,
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
