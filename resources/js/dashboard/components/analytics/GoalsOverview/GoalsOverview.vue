<template>
  <d-card class="card-small go-stats">

    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <d-card-body class="py-0">
      <!-- Goals Overview List Group -->
      <d-list-group flush class="list-group-small">
        <ao-single-goal v-for="(goal, idx) in goalsOverviewData" :key="idx" :goal="goal" />
      </d-list-group>
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
import colors from '../../../utils/colors';
import SingleGoal from './SingleGoal.vue';

const defaultGoalsOverviewData = [{
  title: 'Newsletter Signups',
  completions: '291',
  value: '$192.00',
  conversionRate: '57.2%',
  data: {
    datasets: [{
      hoverBorderColor: '#fff',
      data: [57.2, 42.8],
      backgroundColor: [
        colors.primary.toRGBA(0.9),
        colors.athensGray.toRGBA(0.8),
      ],
    }],
    labels: ['Label 1', 'Label 2'],
  },
}, {
  title: 'Social Shares',
  completions: '451',
  value: '$0.00',
  conversionRate: '45.5%',
  data: {
    datasets: [{
      hoverBorderColor: '#fff',
      data: [45.5, 54.5],
      backgroundColor: [
        colors.success.toRGBA(0.9),
        colors.athensGray.toRGBA(0.8),
      ],
    }],
    labels: ['Label 1', 'Label 2'],
  },
}, {
  title: 'eBook Downloads',
  completions: '12',
  value: '$128.11',
  conversionRate: '5.2%',
  data: {
    datasets: [{
      hoverBorderColor: '#fff',
      data: [5.2, 94.8],
      backgroundColor: [
        colors.salmon.toRGBA(0.9),
        colors.athensGray.toRGBA(0.8),
      ],
    }],
    labels: ['Label 1', 'Label 2'],
  },
}, {
  title: 'Account Creations',
  completions: '281',
  value: '$218.12',
  conversionRate: '30.2%',
  data: {
    datasets: [{
      hoverBorderColor: '#fff',
      data: [30.2, 69.8],
      backgroundColor: [
        colors.warning.toRGBA(0.9),
        colors.athensGray.toRGBA(0.8),
      ],
    }],
    labels: ['Label 1', 'Label 2'],
  },
}];

export default {
  name: 'ao-goals-overview',
  components: {
    aoSingleGoal: SingleGoal,
  },
  props: {
    /**
       * The component title.
       */
    title: {
      type: String,
      default: 'Goals Overview',
    },
    /**
       * The goals overview data.
       */
    goalsOverviewData: {
      type: Array,
      default() {
        return defaultGoalsOverviewData;
      },
    },
  },
};
</script>

