<script setup lang="ts">
import { ref, computed, reactive, watch } from 'vue'
import * as zod from 'zod'
import GoalService from '@services/goals/GoalService'

import type { FormError } from '@nuxt/ui'
import type { Goal } from '@lctypes/goals/Goal'

const goalService = new GoalService()

// modal dialog
type Props = {
    modelValue: boolean
    goal: Goal
}

const { modelValue, goal } = defineProps<Props>()
const modalOpened = computed({
    get: () => modelValue,
    set: value => emit('update:modelValue', value),
})

const emit = defineEmits(['update:modelValue', 'updated'])

// reset form values on opening dialog
watch(modalOpened, value => {
    if (!value) {
        return
    }

    resetForm()
})

watch(
    () => goal,
    value => {
        if (!value) {
            return
        }

        resetForm()
    }
)

const resetForm = () => {
    finished.value = false
    editGoalErrors.value = []
    editGoalFormState.quantity = goal.quantity
}

// form
const finished = ref<boolean>(false)
const editGoalErrors = ref<FormError[]>([])
const editGoalFormSchema = zod.object({
    quantity: zod.number('Must be a number').gte(0, { message: 'Must be greater or equal to 0' }),
})

type Schema = zod.output<typeof editGoalFormSchema>

const editGoalFormState = reactive<Partial<Schema>>({
    quantity: goal.quantity,
})

const updateGoalQuantity = async function () {
    const goalSericeUpdateResult = await goalService.updateGoal(
        goal.id,
        editGoalFormState.quantity as number
    )

    if (goalSericeUpdateResult.ok) {
        emit('updated')
        finished.value = true
    }

    if (!goalSericeUpdateResult.ok && goalSericeUpdateResult.errorMessages) {
        editGoalErrors.value = goalSericeUpdateResult.errorMessages
    }
}
</script>

<template>
    <UForm
        id="edit-goal-form"
        :schema="editGoalFormSchema"
        :state="editGoalFormState"
        :validate-on="['change', 'input']"
        @submit="updateGoalQuantity"
        v-slot="{ loading }"
    >
        <UModal
            class="w-full max-w-lg"
            variant=""
            title="Edit goal"
            v-model:open="modalOpened"
            :close="!loading"
        >
            <template #body>
                <div class="p-4" v-if="!loading && !finished">
                    <UAlert
                        class="mb-4"
                        color="primary"
                        icon="i-lucide-circle-alert"
                        description="This setting will only affect today's and upcoming days' goal. Past days' goals can be modified by clicking on the calendar day."
                    >
                    </UAlert>

                    <UFormField label="Goal quantity" name="quantity" required>
                        <UInputNumber
                            v-model="editGoalFormState.quantity"
                            size="lg"
                            variant="subtle"
                            class="w-full"
                            autofocus
                            required
                        />
                    </UFormField>
                </div>

                <!-- Saving -->
                <div v-if="loading" class="flex flex-wrap justify-center">
                    <div class="w-full flex justify-center mb-4">Updating...</div>
                    <UIcon
                        name="i-lucide-loader-circle"
                        class="size-12 animate-spin text-primary"
                    />
                </div>

                <!-- Updated -->
                <div v-if="finished" class="flex flex-wrap justify-center">
                    <div class="w-full flex justify-center mb-4">Successful update</div>
                    <UIcon name="i-lucide-circle-check" class="size-12 text-success" />
                </div>

                <FormResponseErrorAlert
                    v-if="!finished && !loading"
                    class="mt-4"
                    :title="'Error'"
                    :error-messages="editGoalErrors"
                />
            </template>
            <template #footer>
                <div class="w-full mt-4 flex justify-end" v-if="!finished">
                    <UButton
                        class="justify-center font-normal mr-2"
                        label="Cancel"
                        type="button"
                        variant="link"
                        color="neutral"
                        @click="modalOpened = false"
                        :disabled="loading"
                    />

                    <UButton
                        class="justify-center font-normal"
                        label="Save"
                        type="submit"
                        form="edit-goal-form"
                        loading-icon="i-lucide-loader-circle"
                        loading-auto
                    />
                </div>

                <div class="w-full mt-4 flex justify-end" v-if="finished">
                    <UButton
                        class="justify-center font-normal mr-2"
                        label="Close"
                        type="button"
                        @click="modalOpened = false"
                    />
                </div>
            </template>
        </UModal>
    </UForm>
</template>
