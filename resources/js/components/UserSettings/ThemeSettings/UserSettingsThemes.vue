<template>
    <div id="user-settings-themes">
        <user-settings-text-styling @update="updateTextStyling" />

        <!-- Color header -->
        <div class="subheader mt-4 d-flex">
            Colors
            <v-spacer />
            <div id="theme-selection">
                <v-select
                    v-model="selectedTheme"
                    rounded
                    dense
                    filled
                    hide-details
                    width="140"
                    :items="themes"
                    item-text="name"
                    item-value="value"
                ></v-select>
            </div>
        </div>

        <!-- Color table -->
        <v-form v-model="isFormValid">
            <v-simple-table class="rounded-lg no-hover border mt-2" v-if="!loading">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Color</th>
                        <th>Hex</th>
                        <th>Reset</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(color, index) in (selectedTheme == 'light' ? lightTheme : darkTheme)" :key="selectedTheme + color.name">
                        <td>
                            {{ color.name }}
                            
                            <v-menu offset-y nudge-top="-12px" v-if="colorInformations[color.name] !== undefined">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-icon class="ml-1" v-bind="attrs" v-on="on">mdi-help-circle</v-icon>
                                </template>
                                <v-card outlined class="rounded-lg pa-4" width="320px">
                                    {{ colorInformations[color.name] }}
                                </v-card>
                            </v-menu>
                        </td>
                        <td>
                            <v-menu
                                v-model="color.opened"
                                width="290px"
                                offset-y
                                nudge-top="-10px"
                                right
                                :close-on-content-click="false"
                            >
                                <template v-slot:activator="{ on, attrs }">
                                    <v-card
                                        class="border mx-auto"
                                        outlined
                                        :color="color.value"
                                        width="48px"
                                        height="26px"
                                        @click="color.opened = true;"
                                    ></v-card>
                                </template>

                                <v-color-picker
                                    mode="hexa"
                                    hide-inputs
                                    v-model="color.value"
                                    @update:color="colorChanged(index, $event)"
                                />
                            </v-menu>
                        </td>
                        <td>
                            <v-text-field
                                class="my-2"
                                v-model="color.hex"
                                ref="colorHex"
                                filled
                                rounded
                                dense
                                hide-details
                                maxlength="7"
                                :rules="[rules.hex]"
                                @keyup="hexValueChanged(index)"
                            ></v-text-field>
                        </td>
                        <td>
                            <v-btn
                                icon
                                title="Restore default"
                                @click="resetColor(index)"
                            >
                                <v-icon>mdi-restore</v-icon>
                            </v-btn>
                        </td>
                    </tr>
                </tbody>
            </v-simple-table>
        </v-form>

        <!-- Save result alerts -->
        <v-alert
            v-if="!saving && saveResult === 'error'"
            class="rounded-lg my-3"
            color="error"
            type="error"
            border="left"
            dark
        >
            An error has occurred while saving color theme settings.
        </v-alert>

        <!-- Save button -->
        <div class="d-flex w-full mt-4 mb-16">
            <v-spacer />
            <v-btn
                depressed
                rounded
                color="primary"
                :loading="saving"
                :disabled="saving || !isFormValid"
                @click="save"
            >
                Save
            </v-btn>
        </div>
    </div>
</template>

<script>
    import defaultThemes from '../../../themes';
    import ThemeService from '../../../services/ThemeService';
    export default {
        data: function() {
            return {
                isFormValid: false,
                loading: true,
                saving: false,
                saveResult: '',
                textStyling: null,
                selectedTheme: ThemeService.getCurrentTheme() === 'dark' ? 'dark' : 'light',
                themes: [
                    {
                        name: 'Light theme',
                        value: 'light'
                    },
                    {
                        name: 'Dark theme',
                        value: 'dark'
                    }
                ],
                lightTheme: [],
                darkTheme: [],
                colorInformations: {
                    newWordBackground: 'Used as background for indicating or displaying a new word outside of the interactive text areas.',
                    highlightedWordBackground: 'Used as background for indicating or displaying a highlighted word outside of the interactive text areas.',
                    highlightedWordText: 'Used as background for indicating or displaying a highlighted word outside of the interactive text areas.',
                },

                wordStyling: {
                    level1: {
                        borderWidth: 0,
                        borderRadius: 0,
                        borderType: 'solid',
                        borderTop: false,
                        borderBottom: false,
                        borderSides: false,
                    },
                    level1: {
                        borderWidth: 0,
                        borderRadius: 0,
                        borderType: 'solid',
                        borderTop: false,
                        borderBottom: false,
                        borderSides: false,
                    },
                    level1: {
                        borderWidth: 0,
                        borderRadius: 0,
                        borderType: 'solid',
                        borderTop: false,
                        borderBottom: false,
                        borderSides: false,
                    },
                    level1: {
                        borderWidth: 0,
                        borderRadius: 0,
                        borderType: 'solid',
                        borderTop: false,
                        borderBottom: false,
                        borderSides: false,
                    },
                    new: {
                        borderWidth: 0,
                        borderRadius: 0,
                        borderType: 'solid',
                        borderTop: false,
                        borderBottom: false,
                        borderSides: false,
                    }
                },

                rules: {
                    hex: value => {
                        if (value.length !== 7) {
                            return 'Invalid hex value.';
                        }

                        if(!value.match(/^#(?:[0-9a-fA-F]{3}){1,2}$/)) {
                            return 'Invalid value.';
                        }

                        return true;
                    }
                }
            }
        },
        props: {
            language: String
        },
        mounted() {
            axios.post('/settings/user/get', {settingNames: ['vuetifyThemes']}).then((response) => {
                let savedColors = response.data.vuetifyThemes
                let themeSettingNames = Object.keys(defaultThemes.light)

                this.loading = false;
                themeSettingNames.forEach((themeSettingName) => {
                    var colorName = themeSettingName;
                    
                    var lightColorValue, darkColorValue
                    if (!savedColors || !savedColors['light'][themeSettingName]) {
                        lightColorValue = defaultThemes['light'][themeSettingName];
                        darkColorValue = defaultThemes['dark'][themeSettingName];
                    } else {
                        lightColorValue = savedColors['light'][themeSettingName];
                        darkColorValue = savedColors['dark'][themeSettingName];
                    }
                    

                    this.lightTheme.push({
                        'name': colorName,
                        'value': lightColorValue,
                        'opened': false,
                        'hex': lightColorValue,
                    });

                    this.darkTheme.push({
                        'name': colorName,
                        'value': darkColorValue,
                        'opened': false,
                        'hex': darkColorValue,
                    });
                });
            });
        },
        methods: {
            updateTextStyling(newTextStyling) {
                this.textStyling = newTextStyling
            },
            colorChanged(index, event) {
                if (this.selectedTheme == 'light') {
                    this.lightTheme[index].hex = event.hex;
                } else {
                    this.darkTheme[index].hex = event.hex;
                }
            },
            hexValueChanged(index) {
                if (!this.$refs.colorHex[index].validate()) {
                    return;
                }

                if (this.selectedTheme == 'light') {
                    this.lightTheme[index].value = this.lightTheme[index].hex;
                } else {
                    this.darkTheme[index].value = this.darkTheme[index].hex;
                }
            },
            resetColor(index) {
                var name = this.selectedTheme === 'light' ? this.lightTheme[index].name : this.darkTheme[index].name;
                var defaultValue = defaultThemes[this.selectedTheme][name];

                if (this.selectedTheme == 'light') {
                    this.lightTheme[index].value = defaultValue;
                    this.lightTheme[index].hex = defaultValue;
                } else {
                    this.darkTheme[index].value = defaultValue;
                    this.darkTheme[index].hex = defaultValue;
                }
            },
            save() {
                this.saving = true;
                var colorSettings = {
                    textStyling: this.textStyling,
                    vuetifyThemes: {
                        light: {},
                        dark: {}
                    },
                };

                this.lightTheme.forEach((value, key) => {
                    colorSettings['vuetifyThemes']['light'][value.name] = value.value;
                });

                this.darkTheme.forEach((value, key) => {
                    colorSettings['vuetifyThemes']['dark'][value.name] = value.value;
                });

                axios.post('/settings/user/update', {settings: colorSettings}).then((response) => {
                    this.saveResult = '';
                    this.saving = false;

                    this.$store.commit('shared/setVuetifyThemeSettings', colorSettings.vuetifyThemes)
                    this.$store.commit('shared/setTextStylingSettings', colorSettings.textStyling)
                    ThemeService.setVuetifyTheme(this.$vuetify, this.$store);
                }).catch((error) => {
                    console.log('error', error)
                    this.saveResult = 'error';
                    this.saving = false;
                });

            }
        }
    }
</script>
