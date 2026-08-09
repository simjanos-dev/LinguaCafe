import axios from 'axios'
import Store from '@store/Store'
import ApiCallService from '@services/ApiCallService'

import type { User } from '@lctypes/User.ts'
import type { ApiCallResult } from '@src/types/apicall/ApiCallResult'
import type { LaravelResource } from '@lctypes/apicall/LaravelResource'

export default class AuthService {
    apiCallService: ApiCallService
    toastService: ReturnType<typeof useToast>

    constructor() {
        this.apiCallService = new ApiCallService()
        this.toastService = useToast()
    }

    async getUsers(): Promise<ApiCallResult<User[]>> {
        try {
            const response = await axios<LaravelResource<User[]>>({
                method: 'GET',
                url: '/api/admin/users',
            })

            console.log('users response.data', response.data)
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

    async updateUser(
        id: number,
        name: string,
        email: string,
        isAdmin: boolean
    ): Promise<ApiCallResult<User>> {
        try {
            const response = await axios<User>({
                method: 'POST',
                url: `/api/admin/users/${id}`,
                data: {
                    name: name,
                    email: email,
                    isAdmin: isAdmin,
                },
            })

            if (Store.user && id === Store.user?.id) {
                Store.user.name = name
                Store.user.email = email
                Store.user.is_admin = isAdmin
                console.log('logged in user updated in store', Store.user)
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
