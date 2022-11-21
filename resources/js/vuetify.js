import Vue from 'vue'
import Vuetify from 'vuetify'

Vue.use(Vuetify);

export default new Vuetify({
    theme: {
        themes: {
            light: {
                background: '#F2F5F6',
                primary: '#aa8476',
                secondary: '#eb4d62',
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