import { createRouter, createWebHistory } from 'vue-router';
import PopupList from './components/PopupList.vue';
import PopupForm from './components/PopupForm.vue';

const routes = [
  { path: '/', component: PopupList },
  { path: '/add', component: PopupForm },
  { path: '/edit/:id', component: PopupForm },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;