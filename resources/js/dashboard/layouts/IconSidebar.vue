<template>
  <d-container fluid class="icon-sidebar-nav">
    <d-row>
      <!-- Main Sidebar -->
      <main-sidebar :items="sidebarItems" hide-logo-text />
      <main class="main-content col">
        <!-- Main Navbar -->
        <main-navbar />

        <!-- Content -->
        <slot />

        <!-- Main Footer -->
        <main-footer />
      </main>
    </d-row>
  </d-container>
</template>

<script>
import getSidebarItems from '@/data/sidebar-nav-items';

import MainNavbar from '@/components/layout/MainNavbar/MainNavbar.vue';
import MainSidebar from '@/components/layout/MainSidebar/MainSidebar.vue';
import MainFooter from '@/components/layout/MainFooter/MainFooter.vue';

export default {
  components: {
    MainNavbar,
    MainSidebar,
    MainFooter,
  },
  data() {
    return {
      sidebarItems: getSidebarItems(),
    };
  },
};
</script>

