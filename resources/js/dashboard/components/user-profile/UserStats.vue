<template>
  <d-card class="card-small user-stats mb-4">

    <!-- Stats Overview -->
    <d-card-body>
      <d-row>
        <d-col v-for="(stat, idx) in statsData.smallStats" :key="idx" lg="3" md="6" sm="3" class="text-center">
          <h4 class="m-0">{{ stat.value }}</h4>
          <span class="text-light text-uppercase">{{ stat.title }}</span>
        </d-col>
      </d-row>
    </d-card-body>

    <d-card-footer class="py-0">
      <d-row>
        <d-col sm="12" md="6" class="border-top pb-3 pt-2 border-right">

          <!-- Progress - Workload -->
          <div class="progress-wrapper">
            <div class="progress-label">Workload</div>
            <d-progress class="progress-sm">
              <span class="progress-value">80%</span>
              <d-progress-bar :max="100" :value="80" striped />
            </d-progress>
          </div>

        </d-col>

        <d-col sm="12" md="6" class="border-top pb-3 pt-2">

          <!-- Progress - Performance -->
          <div class="progress-wrapper">
            <div class="progress-label">Performance</div>
            <d-progress class="progress-sm">
              <span class="progress-value">92%</span>
              <d-progress-bar theme="success" :max="100" :value="80" striped />
            </d-progress>
          </div>

        </d-col>
      </d-row>
    </d-card-footer>
  </d-card>
</template>

<script>
const defaultStatsData = {
  smallStats: [{
    title: 'Tasks',
    value: '1128',
  }, {
    title: 'Completed',
    value: '72.4%',
  }, {
    title: 'Projects',
    value: '4',
  }, {
    title: 'Teams',
    value: '3',
  }],
};

export default {
  name: 'user-stats',
  props: {
    /**
     * The component data.
     */
    statsData: {
      type: Object,
      default() {
        return defaultStatsData;
      },
    },
  },
};
</script>
