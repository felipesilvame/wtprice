<template>
  <d-card class="card-small mb-4">
    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <!-- Chart -->
    <d-card-body class="pt-0">
      <canvas ref="canvas" height="130" class="user-profile-weekly-performance mt-3"></canvas>
    </d-card-body>
  </d-card>
</template>

<script>
import Chart from '../../utils/chart';

const defaultChartData = {
  labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
  datasets: [
    {
      label: 'Hours',
      fill: 'start',
      data: [5, 6.4, 7.2, 6, 9, 4.7, 7],
      backgroundColor: 'rgba(0, 123, 255, 1)',
      borderColor: 'rgba(0, 123, 255, 1)',
      pointBackgroundColor: '#FFFFFF',
      pointHoverBackgroundColor: 'rgba(0, 123, 255, 1)',
      borderWidth: 0,
    },
  ],
};

export default {
  name: 'weekly-performance',
  props: {
    /**
     * The title.
     */
    title: {
      type: String,
      default: 'Weekly Performance Report',
    },
    /**
     * The data.
     */
    chartData: {
      type: Object,
      default() {
        return defaultChartData;
      },
    },
    /**
     * The chart options.
     */
    chartOptions: Object,
  },
  mounted() {
    const defaultOptions = {
      responsive: true,
      scaleBeginsAtZero: true,
      legend: false,
      tooltips: {
        enabled: false,
        mode: 'index',
        position: 'nearest',
      },
      elements: {
        line: {
          tension: 0,
        },
      },
      scales: {
        xAxes: [
          {
            stacked: true,
            gridLines: false,
          },
        ],
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
            },
          },
        ],
      },
    };

    const chartConfig = {
      type: 'bar',
      data: this.chartData,
      options: {
        ...defaultOptions,
        ...this.chartOptions,
      },
    };

    new Chart(this.$refs.canvas, chartConfig);
  },
};
</script>
