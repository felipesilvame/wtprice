<template>
  <d-container fluid class="main-content-container px-4">
    <d-row no-gutters class="page-header py-4">
      <!-- Page Header - Title -->
      <d-col col sm="4" class="text-center text-sm-left mb-4 mb-sm-0">
        <span class="text-uppercase page-subtitle">Dashboards</span>
        <h3 class="page-title">Sales Overview</h3>
      </d-col>

      <!-- Page Header - Actions -->
      <d-col col sm="4" class="d-flex align-items-center">
        <d-button-group size="small" class="d-inline-flex mb-3 mb-sm-0 mx-auto">
          <d-link tag="button" class="btn btn-white" :to="{ name: 'analytics' }">Traffic</d-link>
          <d-link tag="button" class="btn btn-white" :to="{ name: 'ecommerce' }">Sales</d-link>
        </d-button-group>
      </d-col>

      <!-- Page Header - Datepicker -->
      <d-col sm="4" class="d-flex">
        <d-input-group size="sm" class="date-range d-flex justify-content-end my-auto">
          <d-datepicker v-model="dateRange.from" :highlighted="{ from: dateRange.from, to: dateRange.to || new Date() }" placeholder="Start Date" typeable small />
          <d-datepicker v-model="dateRange.to" :highlighted="{ from: dateRange.from, to: dateRange.to || new Date() }" placeholder="End Date" typeable small />
          <d-input-group-text slot="append">
            <i class="material-icons">&#xE916;</i>
          </d-input-group-text>
        </d-input-group>
      </d-col>
    </d-row>

    <!-- Small Stats Blocks -->
    <d-row>
      <d-col v-for="(stats, idx) in smallStats" :key="idx" md="6" lg="3" class="mb-4">
        <small-stats :id="`small-stats-${idx}`" :chart-data="stats.datasets" :label="stats.label" :value="stats.value" :percentage="stats.percentage" :increase="stats.increase" :decrease="stats.decrease" />
      </d-col>
    </d-row>

    <d-row>
      <!-- Sales Report -->
      <d-col lg="8" md="12" sm="12" class="mb-4">
        <os-sales-report />
      </d-col>

      <!-- Sales by Category -->
      <d-col lg="4" md="6" sm="6" class="mb-4">
        <os-sales-by-category />
      </d-col>
    </d-row>

    <d-row>
      <!-- Users by Country -->
      <d-col lg="4" class="mb-4">
        <country-reports />
      </d-col>

      <!-- Goals Overview -->
      <d-col lg="8" class="mb-4">
        <os-latest-orders />
      </d-col>
    </d-row>
  </d-container>
</template>

<script>
import SmallStats from '@/components/common/SmallStats.vue';
import CountryReports from '@/components/common/CountryReports.vue';
import SalesReport from '@/components/ecommerce/SalesReport.vue';
import SalesByCategory from '@/components/ecommerce/SalesByCategory.vue';
import LatestOrders from '@/components/ecommerce/LatestOrders.vue';

import colors from '../utils/colors';

export default {
  components: {
    SmallStats,
    CountryReports,
    osSalesReport: SalesReport,
    osSalesByCategory: SalesByCategory,
    osLatestOrders: LatestOrders,
  },
  data() {
    return {
      dateRange: {
        to: null,
        from: null,
      },
    };
  },
  computed: {
    // Small Stats Components Data
    smallStats() {
      return [{
        label: 'Total Revenue',
        value: '$29,219',
        percentage: '2.93%',
        increase: true,
        datasets: [{
          label: 'Today',
          fill: 'start',
          borderWidth: 1.5,
          backgroundColor: colors.primary.toRGBA(0.1),
          borderColor: colors.primary.toRGBA(),
          data: [4, 4, 4, 9, 20],
        }],
      }, {
        label: 'Revenue Today',
        value: '$8,391',
        percentage: '7.21%',
        increase: false,
        decrease: true,
        datasets: [{
          label: 'Today',
          fill: 'start',
          borderWidth: 1.5,
          backgroundColor: colors.success.toRGBA(0.1),
          borderColor: colors.success.toRGBA(),
          data: [1, 9, 1, 9, 9],
        }],
      }, {
        label: 'Total Customers',
        value: '981',
        percentage: '3.71%',
        increase: true,
        datasets: [{
          label: 'Today',
          fill: 'start',
          borderWidth: 1.5,
          backgroundColor: colors.warning.toRGBA(0.1),
          borderColor: colors.warning.toRGBA(),
          data: [9, 9, 3, 9, 9],
        }],
      }, {
        label: 'New Customers',
        value: '29',
        percentage: '2.71%',
        increase: false,
        decrease: true,
        datasets: [{
          label: 'Today',
          fill: 'start',
          borderWidth: 1.5,
          backgroundColor: colors.salmon.toRGBA(0.1),
          borderColor: colors.salmon.toRGBA(),
          data: [3, 3, 4, 9, 4],
        }],
      }];
    },
  },
};
</script>

