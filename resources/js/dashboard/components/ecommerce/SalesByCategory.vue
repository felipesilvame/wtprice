<template>
  <d-card class="sc-stats card-small h-100">

    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <d-card-body class="p-0">
      <d-container fluid>

        <!-- Category Items -->
        <d-row v-for="(item, idx) in categories" :key="idx" class="px-3">
          <d-col class="sc-stats__image">
            <img class="border rounded" :src="item.image" />
          </d-col>
          <d-col class="sc-stats__title">{{ item.title }}</d-col>
          <d-col class="sc-stats__value text-right">{{ item.value }}</d-col>
          <d-col class="sc-stats__percentage text-right">{{ item.percentage }}</d-col>
        </d-row>

      </d-container>
    </d-card-body>

    <d-card-footer class="border-top">
      <d-row>

        <!-- Time Frame -->
        <d-col>
          <d-select size="sm" value="last-week" style="max-width: 130px;">
            <option value="last-week">Last Week</option>
            <option value="today">Today</option>
            <option value="last-month">Last Month</option>
            <option value="last-year">Last Year</option>
          </d-select>
        </d-col>

        <!-- View Full Report -->
        <d-col class="text-right view-report">
          <a href="#">View full report &rarr;</a>
        </d-col>

      </d-row>
    </d-card-footer>

  </d-card>
</template>

<script>
const defaultCategoriesData = [{
  image: require('@/dashboard/assets/images/sales-overview/product-shoes.jpg'),
  title: 'Shoes',
  value: '12,281',
  percentage: '32.4%',
}, {
  image: require('@/dashboard/assets/images/sales-overview/no-product-image.jpg'),
  title: 'Men\'s Jeans',
  value: '8,129',
  percentage: '28.4%',
}, {
  image: require('@/dashboard/assets/images/sales-overview/product-sportswear.jpg'),
  title: 'Sportswear',
  value: '812',
  percentage: '12.2%',
}, {
  image: require('@/dashboard/assets/images/sales-overview/product-basics.jpg'),
  title: 'Basics',
  value: '29',
  percentage: '7.1%',
}, {
  image: require('@/dashboard/assets/images/sales-overview/product-basics.jpg'),
  title: 'Sweaters',
  value: '3',
  percentage: '1.2%',
}];

export default {
  name: 'sales-by-category',
  props: {
    /**
       * The component title.
       */
    title: {
      type: String,
      default: 'Sales by Category',
    },

    /**
       * The data.
       */
    categories: {
      type: Array,
      default() {
        return defaultCategoriesData;
      },
    },
  },
};
</script>
