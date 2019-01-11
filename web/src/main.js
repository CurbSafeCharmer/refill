import "@babel/polyfill";

import Vue from 'vue';

// == Configurations ==
import VueConfig from 'vue-config';
Vue.use(VueConfig, staticConfig);

// == Helpers ==
function requireAll(r) { r.keys().forEach(r); }

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
// It's waste of space to import the whole jQuery that we
// won't use in other places
import $ from 'jquery/src/core';
import 'jquery/src/selector';
import 'jquery/src/deferred';
import 'jquery/src/data';
import 'jquery/src/attributes';

import '@wikimedia/jquery.i18n/src/jquery.i18n';
import '@wikimedia/jquery.i18n/src/jquery.i18n.messagestore';
import '@wikimedia/jquery.i18n/src/jquery.i18n.fallbacks';
import '@wikimedia/jquery.i18n/src/jquery.i18n.parser';
import '@wikimedia/jquery.i18n/src/jquery.i18n.emitter';
import '@wikimedia/jquery.i18n/src/jquery.i18n.language';

let req = require.context('../../messages', false, /\.json$/);
req.keys().forEach(function(key){
  $.i18n().load(req(key), key.replace(/\.[^/.]+$/, '').slice(2));
});
Vue.mixin({
  methods: {
    msg(...args) {
      return $.i18n(...args);
    }
  }
});

import Cookies from 'js-cookie';
let ts = Cookies.get('TsIntuition_userlang');
if (ts) {
  $.i18n().locale = ts;
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
  render: h => h(App),
  router: router
});

