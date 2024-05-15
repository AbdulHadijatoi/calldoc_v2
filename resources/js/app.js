import "./bootstrap";
import { createApp } from "vue/dist/vue.esm-bundler.js";
import vuetify from "./vuetify";
import Example from "./components/Component1.vue";
import Component from "./components/Component2.vue";

const app = createApp({
    components: {
        Example,
        Component,
    },
});

app.use(vuetify).mount('#app');