import { createApp } from 'vue'
import './style.css'
import './tailwindcss.css'
import App from './App.vue'
import router from './router/router';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';

// Create the Vue app
const app = createApp(App);

// Register translation functions globally (if wp.i18n is available)
if (typeof wp !== 'undefined' && wp.i18n) {
    const { __, _x, _n, _nx } = wp.i18n;
    app.config.globalProperties.__ = __;
    app.config.globalProperties._x = _x;
    app.config.globalProperties._n = _n;
    app.config.globalProperties._nx = _nx;
} else {
    // Fallback - just return the string as-is
    app.config.globalProperties.__ = (text) => text;
    app.config.globalProperties._x = (text) => text;
    app.config.globalProperties._n = (text) => text;
    app.config.globalProperties._nx = (text) => text;
}

app.use(router);
app.use(ElementPlus);
app.mount('#giant-checkout-offers');
