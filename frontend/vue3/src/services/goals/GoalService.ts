import axios from 'axios'
import ApiCallService from '@services/ApiCallService'

import type { ApiCallResult } from '@src/types/apicall/ApiCallResult'
import type { Goal, GoalType } from '@lctypes/goals/Goal'

export default class GoalService {
    apiCallService: ApiCallService
    toastService: ReturnType<typeof useToast>

    constructor() {
        this.apiCallService = new ApiCallService()
        this.toastService = useToast()
    }

    async updateGoal(goalId: number, newGoalQuantity: number): Promise<ApiCallResult<Goal[]>> {
        try {
            const response = await axios({
                method: 'POST',
                url: `/api/goals/${goalId}`,
                data: {
                    newGoalQuantity: newGoalQuantity,
                },
            })

            this.toastService.add({
                title: 'Goal editing',
                description: `A goal quantity has been successfully edited.`,
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

    async updateGoalAchievement(
        goalAchievementId: number,
        goalType: GoalType,
        day: string,
        quantity: number
    ): Promise<ApiCallResult<null>> {
        try {
            const response = await axios({
                method: 'POST',
                url: `/api/goals/achievements/${goalAchievementId}`,
                data: {
                    goalType: goalType,
                    day: day,
                    quantity: quantity,
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
}
