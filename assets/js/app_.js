import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import PopupList from './components/PopupList.vue';
import PopupDetail from './components/PopupDetail.vue';

Vue.use(Vuex);
Vue.use(VueRouter);

// Vuex Store
const store = new Vuex.Store({
    state: {
        popups: []
    },
    mutations: {
        SET_POPUPS(state, popups) {
            state.popups = popups;
        }
    },
    actions: {
        fetchPopups({ commit }) {
            fetch('/wp-json/artistudio/v1/popup')
                .then(response => response.json())
                .then(data => commit('SET_POPUPS', data));
        }
    }
});

// Vue Router
const router = new VueRouter({
    routes: [
        { path: '/', component: PopupList },
        { path: '/popup/:id', component: PopupDetail }
    ]
});

// Vue Instance
new Vue({
    el: '#artistudio-popup-app',
    store,
    router,
    created() {
        this.$store.dispatch('fetchPopups');
    }
});