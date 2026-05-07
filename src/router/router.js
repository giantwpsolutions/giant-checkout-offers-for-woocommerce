import { createRouter, createWebHashHistory } from 'vue-router';

import Bumps from '../pages/Bumps.vue';
import Templates from '../pages/Templates.vue';
import AiEngine from '../pages/AiEngine.vue';
import Analytics from '../pages/Analytics.vue';
import Settings from '../pages/Settings.vue';
import NotFound from '../components/NotFound.vue';

const routes = [
    {
        path: '/',
        name: 'Bumps',
        component: Bumps,
    },
    {
        path: '/templates',
        name: 'Templates',
        component: Templates,
    },
    {
        path: '/ai-engine',
        name: 'AiEngine',
        component: AiEngine,
    },
    {
        path: '/analytics',
        name: 'Analytics',
        component: Analytics,
    },
    {
        path: '/settings',
        name: 'Settings',
        component: Settings,
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: NotFound,
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;
