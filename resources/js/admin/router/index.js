import { createRouter, createWebHashHistory } from 'vue-router';
import InfoPage from '../components/InfoPage.vue';
import ListViewPage from '../components/ListViewPage.vue';

const routes = [
    { path: '/', component: InfoPage },
    { path: '/list', component: ListViewPage },
    { path: '/:pathMatch(.*)*', redirect: '/' },
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