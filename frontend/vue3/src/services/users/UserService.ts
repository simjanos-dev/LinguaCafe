import axios from 'axios'
import Store from '@store/Store'
import ApiCallService from '@services/ApiCallService'

import type { User } from '@lctypes/User.ts'
import type { ApiCallResult } from '@src/types/apicall/ApiCallResult'

export default class AuthService {
    apiCallService: ApiCallService
    toastService: ReturnType<typeof useToast>

    constructor() {
        this.apiCallService = new ApiCallService()
        this.toastService = useToast()
    }

    async createUser(
        name?: string,
        email?: string,
        password?: string,
        passwordConfirmation?: string,
        isAdmin?: boolean
    ): Promise<ApiCallResult<User>> {
        try {
            const response = await axios<User>({
                method: 'POST',
                url: '/api/users/store',
                data: {
                    name: name,
                    email: email,
                    password: password,
                    password_confirmation: passwordConfirmation,
                    isAdmin: isAdmin,
                },
            })

            Store.hasUser = true
            this.toastService.add({
                title: 'User creation',
                description: `User has been successfully created: ${email}.`,
                icon: 'i-lucide-triangle-alert',
                color: 'success',
                duration: 10000,
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

    async updatePassword(password?: string, passwordConfirmation?: string) {
        try {
            const response = await axios<User>({
                method: 'POST',
                url: '/api/users/update/password',
                data: {
                    password: password,
                    password_confirmation: passwordConfirmation,
                },
            })

            if (Store.user) {
                Store.user.password_changed = true
            }

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
