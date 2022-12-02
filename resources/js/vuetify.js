import Vue from 'vue'
import Vuetify from 'vuetify'

Vue.use(Vuetify);

export default new Vuetify({
    theme: {
        themes: {
            light: {
                background: '#fafafa',
                navigation: '#f4f4f5',
                primary: '#aa8476',
                primaryDark: '#7e6257',
                secondary: '#aa8476',

                error: '#f24b4f',
                info: '#59a5ee',
                success: '#3dcf59',
                warning: '#ffb448',

                newWord: '#ffdd4b',
                highlightedWord: '#61df6b',
                
            },
        },
    },
})