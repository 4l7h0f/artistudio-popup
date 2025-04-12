import { createRouter, createWebHashHistory } from 'vue-router';
import InfoPage from '../components/InfoPage.vue';

const routes = [
    { path: '/', component: InfoPage },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

// Handle initial navigation based on the hash
const hash = window.location.hash;
if (hash) {
  router.push(hash.replace('#', ''));
}

export default router;