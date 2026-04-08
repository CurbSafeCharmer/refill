/* eslint-disable no-undef */
import "core-js/stable";

import { createApp } from 'vue';

// == Configurations ==
import VueConfig from 'vue-config';

// == Imports ==
import { createRouter, createWebHistory } from 'vue-router';

import { createVuetify } from 'vuetify';
import 'vuetify/styles';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import './fonts/material-icons.css';
/*
Vue.material.registerTheme('default', {
  primary: 'blue',
  accent: 'indigo',
  warn: 'red',
  background: 'white'
});
*/

// == I18N ==
import Banana from 'banana-i18n/dist/cjs/banana-i18n.cjs';
const banana = new Banana('en');

let req = require.context('../../../../../../messages', false, /\.json$/);
req.keys().forEach(function(key){
  banana.load(req(key), key.replace(/\.[^/.]+$/, '').slice(2));
});
// We'll install a global mixin on the app instance below so `msg()` is available in components.

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
  { path: '/:pathMatch(.*)*', component: PageNotFound }
];

const router = createRouter({
  history: createWebHistory(staticConfig.publicPath),
  routes: routes
});

const app = createApp(App);
app.use(VueConfig, staticConfig);
app.use(router);
const vuetify = createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#1976D2',
          secondary: '#424242',
          accent: '#82B1FF',
          error: '#FF5252',
          info: '#2196F3',
          success: '#4CAF50',
          warning: '#FB8C00'
        }
      }
    }
  }
});
app.use(vuetify);
app.mixin({
  methods: {
    msg(...args) {
      return banana.i18n(...args);
    }
  }
});

// expose globally like before
window.app = app.mount('#app');

