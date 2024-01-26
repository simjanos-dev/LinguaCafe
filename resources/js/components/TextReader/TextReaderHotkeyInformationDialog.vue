<template>
    <v-dialog v-model="value" persistent scrollable width="460px" max-height="500px">
        <v-card id="text-reader-hotkeys" class="rounded-lg">
            <!-- Title -->
            <v-card-title>
                <v-icon class="mr-1">mdi-keyboard-outline</v-icon>Text reader hotkeys
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            
            <!-- hotkey info -->
            <v-card-text class="px-8">
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">x</span><span>Set level to ignored</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">0</span><span>Set level to known</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">1</span><span>Set level to 1</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">2</span><span>Set level to 2</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">3</span><span>Set level to 3</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">4</span><span>Set level to 4</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">5</span><span>Set level to 5</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">6</span><span>Set level to 6</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">7</span><span>Set level to 7</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Up arrow</span><span>Increase font size</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">w</span><span>Increase font size</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Up Down</span><span>Decrease font size</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">s</span><span>Decrease font size</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">f</span><span>Send word or phrase to anki</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Esc</span><span>Unselect word or phrase</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Left arrow</span><span>Select previous word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Right arrow</span><span>Select next word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Shift + Left arrow</span><span>Select previous highlighted word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Shift + Right arrow</span><span>Select next highlighted word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Ctrl + Left arrow</span><span>Select previous new word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Ctrl + Right arrow</span><span>Select next new word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">a</span><span>Select previous word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">d</span><span>Select next word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Shift + a</span><span>Select previous highlighted word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Shift + d</span><span>Select next highlighted word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Ctrl + a</span><span>Select previous new word</span>
                </div>
                <div class="hotkey d-flex">
                    <span class="primary--text" style="width: 170px;">Ctrl + d</span><span>Select next new word</span>
                </div>

            </v-card-text>
            
            <!-- Close button -->
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
        },
        emits: ['input'],
        data: function() {
            return {
            };
        },
        mounted: function() {
        },
        methods: {
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
