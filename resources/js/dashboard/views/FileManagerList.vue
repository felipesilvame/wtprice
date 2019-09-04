<template>
  <d-container fluid class="main-content-container px-4 pb-4">

    <!-- Page Header -->
    <d-row no-gutters class="page-header py-4">

      <!-- Page Title -->
      <d-col col sm="4" class="text-center text-sm-left mb-4 mb-sm-0">
        <span class="text-uppercase page-subtitle">Dashboards</span>
        <h3 class="page-title">File Manager</h3>
      </d-col>

      <!-- List / Cards View Switcher -->
      <d-col class="d-flex">
        <d-button-group size="small" class="d-inline-flex ml-auto my-auto">
          <d-link tag="button" class="btn btn-white" :to="{ name: 'file-manager-list' }">
            <i class="material-icons">&#xE8EF;</i>
          </d-link>
          <d-link tag="button" class="btn btn-white" :to="{ name: 'file-manager-cards' }">
            <i class="material-icons">&#xE8F0;</i>
          </d-link>
        </d-button-group>
      </d-col>
    </d-row>

    <!-- List File Manager -->
    <v-client-table :data="tableData" :columns="columns" :options="clientTableOptions" class="dataTables_wrapper file-manager file-manager-list">

      <!-- File Uploader -->
      <template slot="beforeTable">
          <div class="dropzone-wrapper">
            <vue-dropzone id="file-dropzone-1" class="dropzone" :options="dropzoneOptions" />
          </div>
      </template>

      <!-- Item Icon Slot -->
      <div slot="icon" slot-scope="props" class="file-manager__item-icon">
        <div>
          <i v-if="props.row.type === 'Directory'" class="material-icons">&#xE2C7;</i>
          <i v-else class="material-icons">&#xE24D;</i>
        </div>
      </div>

      <!-- Item Name Slot -->
      <div slot="name" slot-scope="props" class="text-left">
        <h5 class="file-manager__item-title">{{ props.row.name }}</h5>
        <span class="file-manager__item-meta">Last opened {{ props.row.lastOpened }} ago.</span>
      </div>

      <!-- Actions Column Slot -->
      <d-button-group slot="actions" slot-scope="props" size="small" class="d-flex justify-content-end">
        <d-button theme="primary" class="btn-white" @click="handleActionClick('edit', props.row.id)">
          <i class="material-icons">&#xE254;</i>
        </d-button>
        <d-button theme="danger" @click="handleActionClick('delete', props.row.id)">
          <i class="material-icons">&#xE872;</i>
        </d-button>
      </d-button-group>
    </v-client-table>
  </d-container>
</template>

<script>
import Vue from 'vue';
import { ClientTable } from 'vue-tables-2';
import VueDropzone from 'vue2-dropzone';

import '@/assets/scss/vue-tables.scss';
import '@/assets/scss/file-manager-list.scss';

Vue.use(ClientTable);

export default {
  components: {
    ClientTable,
    VueDropzone,
  },
  data() {
    return {
      columns: ['icon', 'name', 'size', 'type', 'actions'],
      tableData: [{
        id: 1,
        name: 'Projects',
        size: '7 GB',
        type: 'Directory',
        lastOpened: '2 days',
      }, {
        id: 2,
        name: 'Movies',
        size: '7 GB',
        type: 'Directory',
        lastOpened: '3 days',
      }, {
        id: 3,
        name: 'Backups',
        size: '7 GB',
        type: 'Directory',
        lastOpened: '7 days',
      }, {
        id: 4,
        name: 'Photos',
        size: '7 GB',
        type: 'Directory',
        lastOpened: '18 days',
      }, {
        id: 5,
        name: 'Old Files',
        size: '7 GB',
        type: 'Directory',
        lastOpened: '3 days',
      }, {
        id: 6,
        name: 'New Folder',
        size: '7 GB',
        type: 'Directory',
        lastOpened: '10 minutes',
      }, {
        id: 7,
        name: '01_project_description.doc',
        size: '23 KB',
        type: 'File',
        lastOpened: '1 day',
      }, {
        id: 8,
        name: 'client_feedback.doc',
        size: '120 KB',
        type: 'File',
        lastOpened: '2 months',
      }, {
        id: 9,
        name: 'wedding_photos_001.zip',
        size: '1.2 GB',
        type: 'File',
        lastOpened: '1 year',
      }, {
        id: 10,
        name: 'old_files.zip',
        size: '24.2 GB',
        type: 'File',
        lastOpened: '2 years',
      }, {
        id: 11,
        name: '2010_birthday_photos.zip',
        size: '3.2 GB',
        type: 'File',
        lastOpened: '3 years',
      }, {
        id: 12,
        name: '2011_work_projects.zip',
        size: '820 MB',
        type: 'File',
        lastOpened: '3 years',
      }, {
        id: 13,
        name: 'filename.txt',
        size: '29 B',
        type: 'File',
        lastOpened: '1 day',
      },
      ],

      /**
       * Vue Tables Configuration Options
       * @see https://github.com/matfish2/vue-tables-2
       */
      clientTableOptions: {
        perPage: 10,
        recordsPerPage: [10, 25, 50, 100],
        skin: 'file-manager file-manager-list table dataTable',
        sortIcon: {
          base: 'fas float-right mt-1 text-muted',
          up: 'fa-caret-up',
          down: 'fa-caret-down',
        },
        texts: {
          filterPlaceholder: '',
          limit: 'Show',
        },
        pagination: {
          edge: true,
          nav: 'scroll',
        },
      },

      /**
       * Dropzone configuration options.
       * @see https://github.com/rowanwins/vue-dropzone
       */
      dropzoneOptions: {
        url: 'https://yout-api-here/post',
        thumbnailWidth: 150,
      },
    };
  },
  methods: {
    handleActionClick(action, id) {
      const message = action === 'edit' ? 'Editing' : 'Deleting';
      alert(`${message} item ${id}`); // eslint-disable-line no-alert
    },
  },
};
</script>
