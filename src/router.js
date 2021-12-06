import Vue from 'vue'
import Router from 'vue-router'
import VueSession from 'vue-session'
import Home from './views/Home.vue'
import AuthLogin from './views/Auth/Login.vue'
import AuthLogout from './views/Auth/Logout.vue'
import CalendarioHome from './views/Calendario/Home.vue'
import CruscottoHome from './views/Cruscotto/Home.vue'

Vue.use(Router);
Vue.use(VueSession, {persist: true});

const router = new Router({
    //mode: 'history',
    routes: [
        {
            path: '/',
            name: 'Home',
            component: Home,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/login',
            name: 'Login',
            component: AuthLogin
        },
        {
            path: '/logout',
            name: 'Logout',
            component: AuthLogout,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/calendario',
            name: 'Calendario Ordini',
            component: CalendarioHome,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/ordine/:action/:orderData',
            name: 'Ordine',
            component: CalendarioHome,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/cruscotto',
            name: 'Cruscotto Ordini',
            component: CruscottoHome,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/cruscotto/ordine/:orderData',
            name: 'Stato Ordine',
            component: CruscottoHome,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/cruscotto/storia/:orderData',
            name: 'Storia Ordine',
            component: CruscottoHome,
            meta: {
                requiresAuth: true
            }
        },
    ]
});

// eslint-disable-next-line no-unused-vars
router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && router.app.$session.exists()) {
        const user = router.app.$session.get('user');
        if (user.role !== 'guest') next();
        else next({ name: 'Login' });
    } else if (to.meta.requiresAuth && !router.app.$session.exists()) {
        next({ name: 'Login' });
    } else {
        next();
    }
});

export default router;
