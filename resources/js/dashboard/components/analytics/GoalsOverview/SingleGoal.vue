<template>
  <d-list-group-item class="d-flex row px-0">
    <d-col lg="6" md="8" sm="8">
      <h6 class="go-stats__label mb-1">{{ goal.title }}</h6>
      <div class="go-stats__meta">
        <span class="mr-2">
          <strong>{{ goal.completions }}</strong> Completions</span>
        <span class="d-block d-sm-inline">
          <strong class="text-success">{{ goal.value }}</strong> Value</span>
      </div>
    </d-col>
    <d-col lg="6" md="4" sm="4" class="d-flex">
      <div class="go-stats__value text-right ml-auto">
        <h6 class="go-stats__label mb-1">{{ goal.conversionRate }}</h6>
        <span class="go-stats__meta">Conversion Rate</span>
      </div>
      <div class="go-stats__chart d-flex ml-auto">
        <canvas ref="canvas" style="width: 45px; height: 45px;" class="my-auto"></canvas>
      </div>
    </d-col>
  </d-list-group-item>
</template>

<script>
import Chart from '../../../utils/chart';

export default {
  props: {
    /**
       * The goal data.
       */
    goal: {
      type: Object,
      required: true,
    },
  },
  mounted() {
    new Chart(this.$refs.canvas, {
      type: 'doughnut',
      data: this.goal.data,
      options: {
        legend: false,
        responsive: false,
        cutoutPercentage: 70,
        animation: false,
        tooltips: false,
      },
    });
  },
};
</script>

