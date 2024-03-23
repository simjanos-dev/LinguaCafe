import { createApp } from 'vue';
import Layout from './Components/Layout.vue';

// primevue
import PrimeVue from 'primevue/config';
import Wind from "./presets/wind";
import Dropdown from 'primevue/dropdown';
import Card from 'primevue/card';
import OverlayPanel from 'primevue/overlaypanel';

const app = createApp({
});

app.use(PrimeVue, {
    unstyled: false,
    pt: Wind,
});

app.component('layout', Layout);
app.component('dropdown', Dropdown);
app.component('card', Card);
app.component('popover', OverlayPanel);


app.mount('#app');

