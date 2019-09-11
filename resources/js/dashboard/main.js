/* eslint-disable */
import Vue from 'vue';
import ShardsVue from 'shards-vue';

// Styles
import 'bootstrap/dist/css/bootstrap.css';
import '@/dashboard/assets/shards-dashboard-pro/shards-dashboards.scss';
import '@/dashboard/assets/scss/date-range.scss';

// Core
import App from './App.vue';
import router from './router';

// Layouts
import Default from '@/dashboard/layouts/Default.vue';
import HeaderNavigation from '@/dashboard/layouts/HeaderNavigation.vue';
import IconSidebar from '@/dashboard/layouts/IconSidebar.vue';

const isProd = process.env.NODE_ENV === 'production';

ShardsVue.install(Vue);

Vue.component('default-layout', Default);
Vue.component('header-navigation-layout', HeaderNavigation);
Vue.component('icon-sidebar-layout', IconSidebar);

Vue.config.productionTip = false;
Vue.prototype.$eventHub = new Vue();


new Vue({
  router,
  render: h => h(App),
}).$mount('#app');
