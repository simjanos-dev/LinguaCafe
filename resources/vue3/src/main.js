import "./Assets/main.css";

import { createApp } from "vue";

// import components
import Layout from "@/Components/Layout.vue";
import Navbar from "@/Components/Navbar.vue";

// import PrimeVue
import aura from "@/PrimeVueAura";
import PrimeVue from "primevue/config";
import Drawer from 'primevue/drawer';
import Button from "primevue/button";
import InputText from "primevue/inputtext";

// create app
const app = createApp(Layout);
app.use(PrimeVue, {
    unstyled: true,
    pt: aura,
    options: {
        darkModeSelector: '.dark',
    }
});

// add components
app.component("Layout", Layout);
app.component("Navbar", Navbar);

// add PrimeVue components
app.component("Button", Button);
app.component("Drawer", Drawer);
app.component("InputText", InputText);

app.mount("#app");
