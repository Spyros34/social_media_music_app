import '../css/app.css';
import './bootstrap';
// import Vuetify core styles
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
// import your Vuetify plugin
import vuetify from '../js/plugins/vuetify'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';

import { createInertiaApp, Link, Head } from '@inertiajs/vue3'
import Vue3Marquee from 'vue3-marquee'


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(vuetify)  
            .use(Vue3Marquee)
            .component('InertiaLink', Link)
            .component('InertiaHead', Head)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
