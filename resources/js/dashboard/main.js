/* eslint-disable */
import Vue from 'vue';
import ShardsVue from 'shards-vue';
import VueAnalytics from 'vue-analytics';

// Styles
import 'bootstrap/dist/css/bootstrap.css';
import '@/assets/shards-dashboard-pro/shards-dashboards.scss';
import '@/assets/scss/date-range.scss';

// Core
import App from './App.vue';
import router from './router';

// Layouts
import Default from '@/layouts/Default.vue';
import HeaderNavigation from '@/layouts/HeaderNavigation.vue';
import IconSidebar from '@/layouts/IconSidebar.vue';

const isProd = process.env.NODE_ENV === 'production';

ShardsVue.install(Vue);

Vue.component('default-layout', Default);
Vue.component('header-navigation-layout', HeaderNavigation);
Vue.component('icon-sidebar-layout', IconSidebar);

Vue.config.productionTip = false;
Vue.prototype.$eventHub = new Vue();

// Analytics
Vue.use(VueAnalytics, {
  id: isProd ? 'UA-115105611-1' : 'UA-115105611-2',
  router
});

new Vue({
  router,
  render: h => h(App),
}).$mount('#app');
