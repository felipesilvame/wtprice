<template>
  <d-card class="ubd-stats card-small h-100">

    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <d-card-body class="d-flex flex-column">
      <!-- Chart -->
      <canvas width="100" ref="canvas" class="analytics-users-by-device mt-3 mb-4" />

      <!-- Legend -->
      <div class="ubd-stats__legend w-75 m-auto pb-4">
        <div v-for="(label, idx) in computedLabels" :key="idx" class="ubd-stats__item">
          <div v-if="label.icon" v-html="label.icon" :style="{ color: label.iconColor }" />
          <span class="ubd-stats__category">{{ label.title }}</span>
          <span class="ubd-stats__value">{{ label.value }}%</span>
        </div>
      </div>

    </d-card-body>

    <!-- Footer -->
    <d-card-footer class="border-top">
      <d-row>

        <!-- Time Span -->
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
import colors from '../../utils/colors';
import Chart from '../../utils/chart';

const defaultChartData = {
  labels: ['Desktop', 'Tablet', 'Mobile'],
  datasets: [{
    hoverBorderColor: colors.white.toRGBA(1),
    data: [68.3, 24.2, 7.5],
    icons: [
      '<i class="material-icons">&#xE30B;</i>',
      '<i class="material-icons">&#xE32F;</i>',
      '<i class="material-icons">&#xE325;</i>',
    ],
    backgroundColor: [
      colors.primary.toRGBA(0.9),
      colors.primary.toRGBA(0.5),
      colors.primary.toRGBA(0.3),
    ],
  }],
};

export default {
  name: 'users-by-device',
  props: {
    /**
       * The Chart.js configuration object.
       */
    chartConfig: {
      type: Object,
      default() {
        return {};
      },
    },
    /**
       * The Chart.js options object.
       */
    chartOptions: {
      type: Object,
      default() {
        return {};
      },
    },
    /**
       * The Chart.js data.
       */
    chartData: {
      type: Object,
      default() {
        return defaultChartData;
      },
    },
    /**
       * The component's title.
       */
    title: {
      type: String,
      default: 'Users by Device',
    },
  },
  computed: {
    computedLabels() {
      if (!this.chartData || typeof this.chartData.labels === 'undefined') {
        return [];
      }

      const parsedChartData = this.chartData.labels.map((label, idx) => {
        const dataset = this.chartData.datasets[0];

        return {
          title: label,
          icon: dataset.icons[idx],
          iconColor: dataset.backgroundColor[idx],
          value: dataset.data[idx],
        };
      });

      return parsedChartData;
    },
  },
  mounted() {
    const chartConfig = {
      type: 'doughnut',
      options: {
        ...{
          legend: false,
          cutoutPercentage: 80,
          tooltips: {
            enabled: false,
            mode: 'index',
            position: 'nearest',
          },
        },
        ...this.chartOptions,
      },
      data: this.chartData,
    };

    new Chart(this.$refs.canvas, chartConfig);
  },
};
</script>
