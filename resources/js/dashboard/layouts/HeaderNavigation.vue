<template>
  <d-container fluid>
    <d-row>
      <main class="main-content col-lg-12 col-md-12 col-sm-12 p-0">
        <!-- Main Navbar -->
        <div :class="['main-navbar', 'bg-white']">
          <d-container class="p-0">
            <d-navbar type="light" class="align-items-stretch flex-md-nowrap p-0">
              <a class="navbar-brand" href="#" style="line-height: 25px;">
                <div class="d-table m-auto">
                  <img id="main-logo" class="d-inline-block align-top mr-1 ml-3" style="max-width: 25px;" src="@/assets/images/shards-dashboards-logo.svg" alt="Shards Dashboard">
                  <span class="d-none d-md-inline ml-1">Shards Dashboard</span>
                </div>
              </a>
              <navbar-search />
              <navbar-nav class="d-flex ml-auto" />
              <navbar-toggle class="border-left" v-d-toggle.nav1 />
            </d-navbar>
          </d-container>
        </div>

        <!-- Header Navbar -->
        <header-navbar tag="d-collapse" id="nav1" :items="headerNavbarItems" />

        <!-- Content -->
        <slot />

        <!-- Main Footer -->
        <main-footer contained />
      </main>
    </d-row>
  </d-container>
</template>

<script>
import getHeaderNavbarItems from '@/data/header-nav-items';

import MainNavbar from '@/components/layout/MainNavbar/MainNavbar.vue';
import NavbarSearch from '@/components/layout/MainNavbar/NavbarSearch.vue';
import NavbarNav from '@/components/layout/MainNavbar/NavbarNav.vue';
import NavbarToggle from '@/components/layout/MainNavbar/NavbarToggle.vue';
import HeaderNavbar from '@/components/layout/HeaderNavbar/HeaderNavbar.vue';
import MainFooter from '@/components/layout/MainFooter/MainFooter.vue';

export default {
  components: {
    MainNavbar,
    NavbarSearch,
    NavbarNav,
    NavbarToggle,
    HeaderNavbar,
    MainFooter,
  },
  data() {
    return {
      headerNavbarItems: getHeaderNavbarItems(),
    };
  },
};
</script>
