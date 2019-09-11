<template>
  <d-container class="main-content-container px-4">

    <!-- Page Header -->
    <d-row no-gutters class="page-header py-4">

      <!-- Page Header - Title -->
      <d-col col sm="4" class="text-center text-sm-left mb-4 mb-sm-0">
        <span class="text-uppercase page-subtitle">Overview</span>
        <h3 class="page-title">Analytics</h3>
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
          <d-datepicker v-model="dateRange.from" :highlighted="{ from: dateRange.from, to: dateRange.to || new Date() }" small typeable placeholder="Start date" />
          <d-datepicker v-model="dateRange.to" :highlighted="{ from: dateRange.from, to: dateRange.to || new Date() }" small typeable placeholder="End date" />
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
      <!-- Sessions -->
      <d-col lg="8" md="12" sm="12" class="mb-4">
        <ao-sessions />
      </d-col>

      <!-- Users by Device -->
      <d-col lg="4" md="6" sm="6" class="mb-4">
        <ao-users-by-device />
      </d-col>

      <!-- Top Referrals -->
      <d-col lg="3" sm="6" class="mb-4">
        <ao-top-referrals />
      </d-col>

      <!-- Goals Overview -->
      <d-col lg="5" class="mb-4">
        <ao-goals-overview />
      </d-col>

      <!-- Users by Country -->
      <d-col lg="4" class="mb-4">
        <country-reports />
      </d-col>
    </d-row>
  </d-container>
</template>

<script>
import SmallStats from '@/dashboard/components/common/SmallStats.vue';
import CountryReports from '@/dashboard/components/common/CountryReports.vue';
import TopReferrals from '@/dashboard/components/common/TopReferrals.vue';
import Sessions from '@/dashboard/components/analytics/Sessions.vue';
import UsersByDevice from '@/dashboard/components/analytics/UsersByDevice.vue';
import GoalsOverview from '@/dashboard/components/analytics/GoalsOverview/GoalsOverview.vue';

import colors from '../utils/colors';

export default {
  components: {
    SmallStats,
    CountryReports,
    aoSessions: Sessions,
    aoUsersByDevice: UsersByDevice,
    aoTopReferrals: TopReferrals,
    aoGoalsOverview: GoalsOverview,
  },
  data() {
    return {
      dateRange: {
        from: null,
        to: null,
      },
    };
  },
  computed: {
    // Small Stats Components Data
    smallStats() {
      return [{
        label: 'Users',
        value: '2,390',
        percentage: '12.4%',
        increase: true,
        decrease: false,
        datasets: [{
          label: 'Today',
          fill: 'start',
          borderWidth: 1.5,
          backgroundColor: colors.primary.toRGBA(0.1),
          borderColor: colors.primary.toRGBA(),
          data: [9, 3, 3, 9, 9],
        }],
      }, {
        label: 'Sessions',
        value: '8,391',
        percentage: '7.21%',
        increase: false,
        decrease: true,
        datasets: [{
          label: 'Today',
          fill: 'start',
          borderWidth: 1.5,
          backgroundColor: colors.success.toRGBA(0.1),
          borderColor: colors.success.toRGBA(),
          data: [3.9, 4, 4, 9, 4],
        }],
      }, {
        label: 'Pageviews',
        value: '21,293',
        percentage: '3.71%',
        increase: true,
        decrease: false,
        datasets: [{
          label: 'Today',
          fill: 'start',
          borderWidth: 1.5,
          backgroundColor: colors.warning.toRGBA(0.1),
          borderColor: colors.warning.toRGBA(),
          data: [6, 6, 9, 3, 3],
        }],
      }, {
        label: 'Pages/Session',
        value: '6.43',
        percentage: '2.71%',
        increase: false,
        decrease: true,
        datasets: [{
          label: 'Today',
          fill: 'start',
          borderWidth: 1.5,
          backgroundColor: colors.salmon.toRGBA(0.1),
          borderColor: colors.salmon.toRGBA(),
          data: [0, 9, 3, 3, 3],
        }],
      }];
    },
  },
};
</script>
