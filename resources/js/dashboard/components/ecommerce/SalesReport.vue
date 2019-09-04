<template>
  <d-card class="card-small h-100">
    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <d-card-body class="pt-0">
      <d-row class="border-bottom py-2 bg-light">
        <!-- Time Frame -->
        <d-col col sm="6" class="d-flex mb-2 mb-sm-0">
          <d-button-group>
            <d-btn class="btn-white" active>Hour</d-btn>
            <d-btn class="btn-white">Day</d-btn>
            <d-btn class="btn-white">Week</d-btn>
            <d-btn class="btn-white">Month</d-btn>
          </d-button-group>
        </d-col>

        <!-- Date Range -->
        <d-col col sm="6">
          <d-input-group size="sm" class="date-range d-flex justify-content-end">
            <d-datepicker
              v-model="dateRange.from"
              :highlighted="{ from: dateRange.from, to: dateRange.to || new Date() }"
              placeholder="Start Date"
              small
              typeable
            />
            <d-datepicker
              v-model="dateRange.to"
              :highlighted="{ from: dateRange.from, to: dateRange.to || new Date() }"
              placeholder="End Date"
              small
              typeable
            />
            <d-input-group-text slot="append">
              <i class="material-icons">&#xE916;</i>
            </d-input-group-text>
          </d-input-group>
        </d-col>
      </d-row>

      <!-- Legend & Chart -->
      <div ref="legend"></div>
      <canvas
        height="120"
        ref="canvas"
        style="max-width: 100% !important;"
        class="sales-overview-sales-report"
      ></canvas>
    </d-card-body>
  </d-card>
</template>

<script>
import Chart from '../../utils/chart';

const defaultSalesData = {
  labels: [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Nov',
    'Dec',
  ],
  datasets: [
    {
      label: 'Profit',
      fill: 'start',
      data: [
        28922,
        25317,
        23182,
        32119,
        11291,
        8199,
        25182,
        22120,
        10219,
        8771,
        12992,
        8221,
      ],
      backgroundColor: 'rgba(0, 123, 255, 1)',
      borderColor: 'rgba(0, 123, 255, 1)',
      pointBackgroundColor: '#FFFFFF',
      pointHoverBackgroundColor: 'rgba(0, 123, 255, 1)',
      borderWidth: 1.5,
    },
    {
      label: 'Shipping',
      fill: 'start',
      data: [
        2892,
        2531,
        2318,
        3211,
        1129,
        819,
        2518,
        2212,
        1021,
        8771,
        1299,
        820,
      ],
      backgroundColor: 'rgba(72, 160, 255, 1)',
      borderColor: 'rgba(72, 160, 255, 1)',
      pointBackgroundColor: '#FFFFFF',
      pointHoverBackgroundColor: 'rgba(0, 123, 255, 1)',
      borderWidth: 1.5,
    },
    {
      label: 'Tax',
      fill: 'start',
      data: [1400, 1250, 1150, 1600, 500, 400, 1250, 1100, 500, 4000, 600, 500],
      backgroundColor: 'rgba(153, 202, 255, 1)',
      borderColor: 'rgba(153, 202, 255, 1)',
      pointBackgroundColor: '#FFFFFF',
      pointHoverBackgroundColor: 'rgba(0, 123, 255, 1)',
      borderWidth: 1.5,
    },
  ],
};

export default {
  name: 'sales-report',
  props: {
    /**
     * The component title.
     */
    title: {
      type: String,
      default: 'Sales Report',
    },
    /**
     * The Chart.js data.
     */
    chartData: {
      type: Object,
      default() {
        return defaultSalesData;
      },
    },
    /**
     * The Chart.js options.
     */
    chartOptions: {
      type: Object,
      default() {
        return Object.create(null);
      },
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
        legend: false,
        // Uncomment the following line in order to disable the animations.
        // animation: false,
        tooltips: {
          enabled: false,
          mode: 'index',
          position: 'nearest',
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
              stacked: true,
              ticks: {
                userCallback(label) {
                  return label > 999 ? `${(label / 1000).toFixed(0)}k` : label;
                },
              },
            },
          ],
        },
      },
      ...this.chartOptions,
    };

    const SalesReportChart = new Chart(this.$refs.canvas, {
      type: 'bar',
      data: this.chartData,
      options: chartOptions,
    });

    // Generate the chart labels.
    this.$refs.legend.innerHTML = SalesReportChart.generateLegend();

    // Hide initially the first and last chart points.
    // They can still be triggered on hover.
    const meta = SalesReportChart.getDatasetMeta(0);
    meta.data[0]._model.radius = 0;
    meta.data[this.chartData.datasets[0].data.length - 1]._model.radius = 0;

    // Render the chart.
    SalesReportChart.render();
  },
};
</script>

