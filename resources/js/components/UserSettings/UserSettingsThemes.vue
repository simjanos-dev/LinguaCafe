<template>
    <div id="user-settings-themes">
        <text-user-settings @update="updateTextStyling" />

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
                        <td>{{ color.name }}</td>
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
    import defaultThemes from './../../themes';
    import ThemeService from './../../services/ThemeService';
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

                sampleText: 'LinguaCafe is a language learning platform , where you can read foreign texts .',
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
            axios.post('/settings/user/get', {settingNames: ThemeService.themeColorNames}).then((response) => {
                var savedColors = response.data;
                this.loading = false;

                ThemeService.themeColorNames.forEach((value) => {
                    var theme = value.split('-')[0].replace('Theme', '');
                    var colorName = value.split('-')[1];
                    var colorValue = savedColors[value] === undefined ? defaultThemes[theme][colorName] : savedColors[value];

                    if (theme === 'light') {
                        this.lightTheme.push({
                            'name': colorName,
                            'value': colorValue,
                            'opened': false,
                            'hex': colorValue,
                        });
                    } else {
                        this.darkTheme.push({
                            'name': colorName,
                            'value': colorValue,
                            'opened': false,
                            'hex': colorValue,
                        });
                    }

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

                console.log('hex value changed');
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
                var colorSettings = {};

                this.lightTheme.forEach((value, key) => {
                    colorSettings['lightTheme-' + value.name] = value.value;
                });

                this.darkTheme.forEach((value, key) => {
                    colorSettings['darkTheme-' + value.name] = value.value;
                });

                colorSettings['textStyling'] = this.textStyling;
                axios.post('/settings/user/update', {settings: colorSettings}).then((response) => {
    
                    this.saveResult = '';
                    this.saving = false;

                    ThemeService.loadTheme(this.$vuetify);
                }).catch((error) => {
                    this.saveResult = 'error';
                    this.saving = false;
                });

            }
        }
    }
</script>
