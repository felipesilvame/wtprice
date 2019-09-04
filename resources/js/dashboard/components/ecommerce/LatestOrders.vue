<template>
  <d-card class="card-small lo-stats h-100">

    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <d-card-body class="p-0">
      <d-container fluid class="px-0">

        <!-- Latest Orders List -->
        <table class="table mb-0">
          <thead class="py-2 bg-light text-semibold border-bottom">
            <tr>
              <th>Details</th>
              <th></th>
              <th class="text-center">Status</th>
              <th class="text-center">Items</th>
              <th class="text-center">Total</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in latestOrdersData" :key="idx">
              <td class="lo-stats__image">
                <img class="border rounded" :src="item.image" alt="Alternative title">
              </td>
              <td class="lo-stats__order-details">
                <span>{{ item.id }}</span>
                <span>{{ item.date }}</span>
              </td>
              <td class="lo-stats__status">
                <div class="d-table mx-auto">
                  <d-badge pill :class="[getBadgeType(item.status)]">{{ item.status }}</d-badge>
                </div>
              </td>
              <td class="lo-stats__items text-center">{{ item.items }}</td>
              <td class="lo-stats__total text-center text-success">{{ item.total }}</td>
              <td class="lo-stats__actions">
                <d-button-group class="d-table ml-auto">
                  <d-button size="sm" type="button" class="btn-white">Cancel</d-button>
                  <d-button size="sm" type="button" class="btn-white">Edit</d-button>
                </d-button-group>
              </td>
            </tr>
          </tbody>
        </table>

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
const defaultOrdersData = [{
  id: '#19280',
  date: '21 February 2018 20:32',
  image: require('@/assets/images/sales-overview/product-sweaters.jpg'),
  status: 'Complete',
  items: '7',
  total: '$199',
},
{
  id: '#19279',
  date: '21 February 2018 20:32',
  image: require('@/assets/images/sales-overview/product-order-1.jpg'),
  status: 'Pending',
  items: '7',
  total: '$612',
},
{
  id: '#19278',
  date: '21 February 2018 20:32',
  image: require('@/assets/images/sales-overview/product-order-2.jpg'),
  status: 'Canceled',
  items: '18',
  total: '$1211',
},
{
  id: '#19277',
  date: '21 February 2018 20:32',
  image: require('@/assets/images/sales-overview/product-order-3.jpg'),
  status: 'Pending',
  items: '7',
  total: '$612',
},
];

export default {
  name: 'latest-orders',
  props: {
    /**
       * The latest orders data.
       */
    latestOrdersData: {
      type: Array,
      default() {
        return defaultOrdersData;
      },
    },
    /**
       * The component's title.
       */
    title: {
      type: String,
      default: 'Latest Orders',
    },
  },
  methods: {
    getBadgeType(status) {
      const statusMap = {
        Complete: 'success',
        Pending: 'warning',
        Canceled: 'danger',
      };

      return `badge-${statusMap[status]}`;
    },
  },
};
</script>

