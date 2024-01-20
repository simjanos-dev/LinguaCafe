import Vue from 'vue'
import Vuetify from 'vuetify'
import themes from './themes'
import '@mdi/font/css/materialdesignicons.css'

Vue.use(Vuetify);

export default new Vuetify({
    icons: {
        defaultSet: 'mdi'
    },
    theme: {    
        options: { 
            customProperties: true,
            variations: true
        },
    },
})