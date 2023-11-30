/* eslint-disable no-undef */
import "core-js/stable";

import Vue from 'vue';

// == Configurations ==
import VueConfig from 'vue-config';
Vue.use(VueConfig, staticConfig);

// == Imports ==
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Vuetify from 'vuetify';
import 'vuetify/dist/vuetify.min.css';
Vue.use(Vuetify);
/*
Vue.material.registerTheme('default', {
  primary: 'blue',
  accent: 'indigo',
  warn: 'red',
  background: 'white'
});
*/

import VueResource from 'vue-resource';
Vue.use(VueResource);

// == I18N ==
import Banana from 'banana-i18n';
const banana = new Banana('en');

let req = require.context('../../messages', false, /\.json$/);
req.keys().forEach(function(key){
  banana.load(req(key), key.replace(/\.[^/.]+$/, '').slice(2));
});
Vue.mixin({
  methods: {
    msg(...args) {
      return banana.i18n(...args);
    }
  }
});

import Cookies from 'js-cookie';
let ts = Cookies.get('TsIntuition_userlang');
if (ts) {
  banana.setLocale(ts);
}

// == Routing ==
import App from './App.vue';
import Index from './pages/Index.vue';
import Result from './pages/Result.vue';
import LegacyBridge from './pages/LegacyBridge.vue';
import PageNotFound from './pages/PageNotFound.vue';

const routes = [
  { path: '/', component: Index },
  { path: '/result/:taskName/:taskId', component: Result },
  { path: '/result.php', component: LegacyBridge },
  { path: '*', component: PageNotFound }
];

const router = new VueRouter({
  routes: routes,
  mode: 'history',
  base: staticConfig.publicPath
});

window.app = new Vue({
  el: '#app',
  vuetify : new Vuetify(),
  render: h => h(App),
  router: router
});

