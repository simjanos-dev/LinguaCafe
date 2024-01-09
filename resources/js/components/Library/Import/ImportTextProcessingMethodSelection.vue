<template>
    <div>
        <v-alert dark border="left" type="error" color="primary">
            Simple processing method splits text into sentences and words very quickly, however 
            does not provide additional information for them like dictionary form, gender or reading.<br><br>
            Simple processing is only available for languages that use spaces between words.
        </v-alert>
        <label class="font-weight-bold mt-2">Text processing method</label>
        <v-radio-group
            v-model="processingMethod"
            @change="textProcessingMethodChanged"
            class="mt-0"
        >
            <v-radio
                label="Simple"
                value="simple"
                :disabled="!simpleProcessingMethodEnabled"
            ></v-radio>
            <v-radio
                value="detailed"
            >
                <template v-slot:label>
                    <div>Detailed <strong class="error--text">(can take more than 2 minutes)</strong></div>
                </template>
            </v-radio>
        </v-radio-group>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                processingMethod: 'detailed',
                simpleProcessingMethodEnabled: this.$props.language == 'norwegian' || this.$props.language == 'german'
            }
        },
        props: {
            language: String
        },
        mounted() {
        },
        methods: {
            textProcessingMethodChanged(method) {
                this.$emit('text-processing-method-changed', method);
            }
        }
    }
</script>
