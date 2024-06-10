import "./bootstrap";
import { createApp } from "vue/dist/vue.esm-bundler.js";

import Component from "./components/Component.vue";
import Medicine from "./components/Medicines/Medicines.vue";
import Doctors from "./components/DoctorsInformation/Doctors.vue";
import Appointments from "./components/Appointments/Appointments.vue";
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg'
import "vuetify/styles";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";

const vuetify = createVuetify({
    components,
    directives,

//and this
    icons: {
        defaultSet: "mdi",
        aliases,
        sets: {
            mdi,
        },
    },
//

});
const app = createApp({
    components: {
        Component,
        Doctors,
        Medicine,
        Appointments,
    },
});

app.use(vuetify).mount('#app');