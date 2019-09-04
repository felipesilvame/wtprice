<template>
  <d-card class="card-small h-100">
    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <d-card-body class="pt-0">
      <d-row class="border-bottom py-2 bg-light">
        <!-- Time Interval -->
        <d-col col sm="6" class="d-flex mb-2 mb-sm-0">
          <d-button-group>
            <d-btn class="btn-white" active>Hour</d-btn>
            <d-btn class="btn-white">Day</d-btn>
            <d-btn class="btn-white">Week</d-btn>
            <d-btn class="btn-white">Month</d-btn>
          </d-button-group>
        </d-col>

        <!-- Datepicker -->
        <d-col col sm="6">
          <d-input-group size="sm" class="date-range d-flex justify-content-end">
            <d-datepicker
              v-model="dateRange.from"
              :highlighted="{ from: dateRange.from, to: dateRange.to || new Date() }"
              placeholder="Start date"
              small
              typeable
            />
            <d-datepicker
              v-model="dateRange.to"
              :highlighted="{ from: dateRange.from, to: dateRange.to || new Date() }"
              placeholder="End date"
              small
              typeable
            />
            <d-input-group-text slot="append">
              <i class="material-icons">&#xE916;</i>
            </d-input-group-text>
          </d-input-group>
        </d-col>
      </d-row>

      <!-- Chart -->
      <div ref="legend"></div>
      <canvas
        height="120"
        ref="canvas"
        style="max-width: 100% !important;"
        class="analytics-overview-sessions"
      ></canvas>
    </d-card-body>
  </d-card>
</template>

<script>
import colors from '../../utils/colors';
import Chart from '../../utils/chart';

const defaultChartData = {
  labels: [
    '09:00 PM',
    '10:00 PM',
    '11:00 PM',
    '12:00 PM',
    '13:00 PM',
    '14:00 PM',
    '15:00 PM',
    '16:00 PM',
    '17:00 PM',
  ],
  datasets: [
    {
      label: 'Today',
      fill: 'start',
      data: [5, 5, 10, 30, 10, 42, 5, 15, 5],
      backgroundColor: colors.primary.toRGBA(0.1),
      borderColor: colors.primary.toRGBA(1),
      pointBackgroundColor: colors.white.toHex(),
      pointHoverBackgroundColor: colors.primary.toRGBA(1),
      borderWidth: 1.5,
    },
    {
      label: 'Yesterday',
      fill: 'start',
      data: ['', 23, 5, 10, 5, 5, 30, 2, 10],
      backgroundColor: colors.salmon.toRGBA(0.1),
      borderColor: colors.salmon.toRGBA(1),
      pointBackgroundColor: colors.white.toHex(),
      pointHoverBackgroundColor: colors.salmon.toRGBA(1),
      borderDash: [5, 5],
      borderWidth: 1.5,
      pointRadius: 0,
      pointBorderColor: colors.salmon.toRGBA(1),
    },
  ],
};

export default {
  name: 'sessions',
  props: {
    /**
     * The component title.
     */
    title: {
      type: String,
      default: 'Sessions',
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
     * The Chart.js options.
     */
    chartOptions: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      dateRange: {
        from: null,
        to: null,
      },
    };
  },
  mounted() {
    const chartOptions = {
      ...{
        responsive: true,
        legend: false,
        elements: {
          line: {
            // A higher value makes the line look skewed at this ratio.
            tension: 0.38,
          },
        },
        scales: {
          xAxes: [
            {
              gridLines: false,
              ticks: {
                callback(tick, index) {
                  return index % 2 === 0 ? '' : tick;
                },
              },
            },
          ],
          yAxes: [
            {
              ticks: {
                suggestedMax: 45,
              },
            },
          ],
        },
        tooltips: {
          enabled: false,
          mode: 'index',
          position: 'nearest',
        },
      },
      ...this.chartOptions,
    };

    const AnalyticsOverviewChart = new Chart(this.$refs.canvas, {
      type: 'line',
      data: this.chartData,
      options: chartOptions,
    });

    // Generate the analytics overview chart labels.
    this.$refs.legend.innerHTML = AnalyticsOverviewChart.generateLegend();

    // Hide initially the first and last analytics overview chart points.
    // They can still be triggered on hover.
    const meta = AnalyticsOverviewChart.getDatasetMeta(0);
    meta.data[0]._model.radius = 0;
    meta.data[this.chartData.datasets[0].data.length - 1]._model.radius = 0;

    // Render the chart.
    AnalyticsOverviewChart.render();
  },
};
</script>

