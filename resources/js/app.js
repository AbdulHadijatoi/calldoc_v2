import "./bootstrap";
import { createApp } from "vue";
import component1 from "./components/Component1.vue";
import component2 from "./components/Component2.vue";
import vuetify from "./vuetify";

let selectedComponent = null;

if (window.selectedComponent === 'component1') {
    selectedComponent = component1;
} else if (window.selectedComponent === 'component2') {
    selectedComponent = component2;
}

let app = createApp(selectedComponent);

if (app) {
    app.use(vuetify).mount("#app");
}
