import Vue from 'vue'
import Vuetify from 'vuetify'
import themes from './themes'

Vue.use(Vuetify);

export default new Vuetify({
    theme: {
        options: { 
            customProperties: true,
            variations: true
        },
    },
})