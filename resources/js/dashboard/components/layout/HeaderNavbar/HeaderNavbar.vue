<template>
  <component :is="tag" :id="id" class="header-navbar d-lg-flex p-0 bg-white border-top">
    <div class="container">
      <div class="row">
        <div class="col">
          <ul class="nav nav-tabs border-0 flex-column flex-lg-row">
            <li v-for="(item, idx) in items" :key="idx" :class="['nav-item', item.items && item.items.length ? 'dropdown' : '']">
              <d-link :to="item.to" class="nav-link text-nowrap" v-d-toggle="`shn-${idx}`">
                <div class="item-icon-wrapper" v-if="item.htmlBefore" v-html="item.htmlBefore" /> {{ item.title }}
              </d-link>
              <d-collapse :id="`shn-${idx}`" v-if="item.items && item.items.length" class="dropdown-menu dropdown-menu-small" accordion="header-navbar-accordion">
                <d-dropdown-item tag="d-link" v-for="(item, idx) in item.items" :key="idx" :to="item.to">{{ item.title }}</d-dropdown-item>
              </d-collapse>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </component>
</template>

<script>
export default {
  name: 'header-navbar',
  props: {
    /**
       * The header menu items.
       */
    items: {
      type: Array,
      required: true,
    },
    /**
       * The component's tag.
       */
    tag: {
      type: String,
      default: 'div',
    },
    /**
       * The component's ID.
       */
    id: {
      type: String,
    },
  },
};
</script>

<style lang="scss">
  .header-navbar {
    .item-icon-wrapper {
      display: inline-block;
    }
    .dropdown-menu {
      display: block;
    }
  }
</style>
