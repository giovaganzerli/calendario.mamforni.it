/* eslint-disable */

import Vue from 'vue'
import App from './App.vue'
import router from './router'
import './registerServiceWorker'

import VueResource from 'vue-resource'
Vue.use(VueResource)

import moment from 'moment'
import VueMoment from 'vue-moment'
import VueMomentTimezone from 'moment-timezone'
import MomentLocateIT from 'moment/locale/it'

Vue.use(VueMoment, {
    moment,
    VueMomentTimezone,
    MomentLocateIT
})

// IMPORT MuseUI (Material Deisgn with VueJS)
import MuseUI from 'muse-ui'
import MuseUITheme from 'muse-ui/lib/theme'

Vue.use(MuseUI)

MuseUITheme.add('mamforni', {
    primary: 'var(--color-red)',
    secondary: 'var(--color-gray)',
    success: '#4caf50',
    warning: 'var(--color-yellow)',
    text: {
        primary: 'var(--color-gray2)',
        secondary: 'var(--color-red)',
        disabled: 'var(--color-gray2)',
        hint: 'var(--color-red)'
    }
})

MuseUITheme.use('mamforni')

import VueMq from 'vue-mq'

import VueSession from 'vue-session'

Vue.use(VueSession, {
    persist: true
})

import Vue2Editor from "vue2-editor"
Vue.use(Vue2Editor)

// CALENDARIO.MAMFORNI.NET
// home     >> https://calendario.mamforni.it
// dist     >> https://calendario.mamforni.it
// api      >> https://calendario.mamforni.it/wp

// NUVOLA SERVER
// home     >> http://qqcxg8t9wwdo2vmd.myfritz.net:12080/calendario
// dist     >> http://qqcxg8t9wwdo2vmd.myfritz.net:12080/calendario
// api     >> http://qqcxg8t9wwdo2vmd.myfritz.net:12080/calendario/wp/index.php

Vue.prototype.$app = {
    urls: {
        home: 'https://calendario.mamforni.it',
        dist: 'https://calendario.mamforni.it'
    },
    user: {
        username: 'guest',
        role: 'guest'
    },
    api: {
        host: 'https://calendario.mamforni.it/wp',
        usr: 'api',
        pw: 'api',
        //token: 'YXBpOmhjMG5YR3NLOUxuRkZhJnVKN29MSEtkZg=='
        token: ''
    },
    editor: {
        api: 'tcvhozniza5pf78v3c6wvptr1jy0v9bz8wbcpc62gccb9kgi'
    }
}

Vue.use(VueMq, {
    breakpoints: {
        mobile_small: 421,
        mobile: 741,
        tablet_small: 941,
        tablet: 1025,
        desktop: Infinity,
    }
})

import VueLocalForage from 'vue-localforage'
Vue.use(VueLocalForage);

Vue.config.productionTip = true

new Vue({
    router,
    render: h => h(App)
}).$mount('#app')
