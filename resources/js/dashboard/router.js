import Vue from 'vue';
import Router from 'vue-router';

import Analytics from './views/Analytics.vue';
import OnlineStore from './views/OnlineStore.vue';
import HeaderNavigation from './views/HeaderNavigation.vue';
import IconSidebarNav from './views/IconSidebarNav.vue';
import PersonalBlog from './views/PersonalBlog.vue';
import UserProfile from './views/UserProfile.vue';
import UserProfileLite from './views/UserProfileLite.vue';
import EditUserProfile from './views/EditUserProfile.vue';
import Login from './views/Login.vue';
import Register from './views/Register.vue';
import ForgotPassword from './views/ForgotPassword.vue';
import ChangePassword from './views/ChangePassword.vue';
import FileManagerList from './views/FileManagerList.vue';
import FileManagerCards from './views/FileManagerCards.vue';
import TransactionHistory from './views/TransactionHistory.vue';
import AddNewPost from './views/AddNewPost.vue';
import Errors from './views/Errors.vue';
import ComponentsOverview from './views/ComponentsOverview.vue';
import Tables from './views/Tables.vue';
import BlogPosts from './views/BlogPosts.vue';

Vue.use(Router);

export default new Router({
  mode: 'history',
  base: process.env.MIX_BASE_URL,
  linkActiveClass: 'active',
  linkExactActiveClass: 'exact-active',
  scrollBehavior() {
    return { x: 0, y: 0 };
  },
  routes: [
    {
      path: '/',
      redirect: '/analytics',
    },
    {
      path: '/analytics',
      name: 'analytics',
      component: Analytics,
    },
    {
      path: '/ecommerce',
      name: 'ecommerce',
      component: OnlineStore,
    },
    {
      path: '/blog-overview',
      name: 'blog-overview',
      component: PersonalBlog,
    },
    {
      path: '/user-profile',
      name: 'user-profile',
      component: UserProfile,
    },
    {
      path: '/user-profile-lite',
      name: 'user-profile-lite',
      component: UserProfileLite,
    },
    {
      path: '/edit-user-profile',
      name: 'edit-user-profile',
      component: EditUserProfile,
    },
    {
      path: '/login',
      name: 'login',
      meta: { layout: 'icon-sidebar' },
      component: Login,
    },
    {
      path: '/register',
      name: 'register',
      meta: { layout: 'icon-sidebar' },
      component: Register,
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      meta: { layout: 'icon-sidebar' },
      component: ForgotPassword,
    },
    {
      path: '/change-password',
      name: 'change-password',
      meta: { layout: 'icon-sidebar' },
      component: ChangePassword,
    },
    {
      path: '/file-manager-list',
      name: 'file-manager-list',
      component: FileManagerList,
    },
    {
      path: '/file-manager-cards',
      name: 'file-manager-cards',
      component: FileManagerCards,
    },
    {
      path: '/transaction-history',
      name: 'transaction-history',
      component: TransactionHistory,
    },
    {
      path: '/add-new-post',
      name: 'add-new-post',
      component: AddNewPost,
    },
    {
      path: '/errors',
      name: 'errors',
      component: Errors,
    },
    {
      path: '/components-overview',
      name: 'components-overview',
      component: ComponentsOverview,
    },
    {
      path: '/tables',
      name: 'tables',
      component: Tables,
    },
    {
      path: '/blog-posts',
      name: 'blog-posts',
      component: BlogPosts,
    },
    {
      path: '/header-navigation',
      name: 'header-navigation',
      meta: { layout: 'header-navigation' },
      component: HeaderNavigation,
    },
    {
      path: '/icon-sidebar-nav',
      name: 'icon-sidebar-nav',
      meta: { layout: 'icon-sidebar' },
      component: IconSidebarNav,
    }, {
      path: '*',
      redirect: '/errors',
    },
  ],
});
