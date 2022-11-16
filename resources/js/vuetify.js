import Vue from 'vue'
import Vuetify from 'vuetify'
import 'vuetify/dist/vuetify.min.css'
Vue.use(Vuetify)

export default new Vuetify({
    theme: {
        options: { variations: true },
        themes: {
            light: {
                background: '#f6f6f6',
                navigation: '#f6f6f6',
                primary: '#7b4cfd',
                secondary: '#78655f',

                green: '#00c853',
                orange: '#ffb400',
                blue: '#16b1ff',
                red: '#ff4c51',

                newWord: '#f5dd63',
                highlightedWord: '#63ee6d',
                
            },
        },
    },
})