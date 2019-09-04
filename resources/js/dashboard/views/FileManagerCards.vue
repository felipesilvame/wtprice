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

    <!-- File Manager - Cards -->
    <div class="file-manager file-manager-cards">
      <d-card class="card-small mb-3">

        <!-- Dropzone -->
        <d-row no-gutters class="border-bottom">
          <div class="file-manager-cards__dropzone w-100 p-2">
            <vue-dropzone id="fm-cards-dropzone" class="dropzone" :options="dropzoneOptions"></vue-dropzone>
          </div>
        </d-row>

        <!-- Meta Actions -->
        <d-row no-gutters class="p-2">
          <d-col lg="3" class="mb-2 mb-lg-0">
            <d-form action="POST">
              <d-input-group seamless>
                <d-input-group-text slot="prepend">
                  <i class="material-icons">&#xE8B6;</i>
                </d-input-group-text>
                <d-input size="sm" class="file-manager-cards__search" placeholder="Search files" />
              </d-input-group>
            </d-form>
          </d-col>
          <d-col>
            <div class="d-flex ml-lg-auto my-auto">
              <d-button-group size="small" class="mr-2 ml-lg-auto">
                <d-button class="btn-white">
                  <i class="material-icons">&#xE5CA;</i>
                </d-button>
                <d-button type="button" class="btn-white">
                  <i class="material-icons">&#xE870;</i>
                </d-button>
                <d-button type="button" class="btn-white">
                  <i class="material-icons">&#xE254;</i>
                </d-button>
                <d-button type="button" class="btn-white">
                  <i class="material-icons">&#xE872;</i>
                </d-button>
              </d-button-group>
              <d-button class="btn-sm btn-accent d-inline-block ml-auto ml-lg-0">
                <i class="material-icons">&#xE145;</i> Add New
              </d-button>
            </div>
          </d-col>
        </d-row>
      </d-card>

      <!-- Section Title - Directories -->
      <d-row>
        <d-col>
          <span class="file-manager__group-title text-uppercase text-light">Directories</span>
        </d-col>
      </d-row>

      <!-- Directories List -->
      <d-row>
        <d-col lg="3" v-for="(dir, idx) in filesData.directories" :key="idx">
          <div @click="handleItemClick('directories', dir)" :class="[ dir.selected ? 'file-manager__item--selected' : '', 'file-manager__item', 'file-manager__item--directory', 'card', 'card-small', 'mb-3']">
            <d-card-footer>
              <span class="file-manager__item-icon"><i class="material-icons">&#xE2C7;</i> </span>
              <h6 class="file-manager__item-title">{{ dir.title }}</h6>
            </d-card-footer>
          </div>
        </d-col>
      </d-row>

      <!-- Section Title - Documents -->
      <d-row>
        <d-col>
          <span class="file-manager__group-title text-uppercase text-light">Documents</span>
        </d-col>
      </d-row>

      <!-- Documents List -->
      <d-row>
        <d-col lg="3" sm="6" v-for="(file, idx) in filesData.files" :key="idx">
          <div :class="[file.selected ? 'file-manager__item--selected' : '', 'file-manager__item', 'card', 'card-small','mb-3']" @click="handleItemClick('files', file)">
            <d-card-body class="file-manager__item-preview px-0 pb-0 pt-4">
              <img :src="file.image" :alt="file.title">
            </d-card-body>
            <d-card-footer class="border-top">
              <span class="file-manager__item-icon"><i class="material-icons">&#xE24D;</i></span>
              <h6 class="file-manager__item-title">{{ file.title }}</h6>
              <span class="file-manager__item-size ml-auto">{{ file.size }}</span>
            </d-card-footer>
          </div>
        </d-col>
      </d-row>
    </div>
  </d-container>
</template>

<script>
import VueDropzone from 'vue2-dropzone';

export default {
  components: {
    VueDropzone,
  },
  data() {
    return {
      columns: ['icon', 'name', 'size', 'type', 'actions'],
      filesData: {
        directories: [{
          id: 1,
          title: 'Projects',
          selected: true,
        }, {
          id: 2,
          title: 'Movies',
          selected: false,
        }, {
          id: 3,
          title: 'Backups',
          selected: false,
        }, {
          id: 4,
          title: 'Photos',
          selected: false,
        }, {
          id: 5,
          title: 'Old Files',
          selected: false,
        }, {
          id: 6,
          title: 'New Folder with Extremely Long Title',
          selected: false,
        }],
        files: [{
          id: 1,
          title: 'Lorem Ipsum Document',
          size: '12kb',
          image: require('@/assets/images/file-manager/document-preview-1.jpg'),
          selected: false,
        }, {
          id: 2,
          title: 'Lorem Ipsum Document',
          size: '12kb',
          image: require('@/assets/images/file-manager/document-preview-1.jpg'),
          selected: false,
        }, {
          id: 3,
          title: 'Lorem Ipsum Document',
          size: '12kb',
          image: require('@/assets/images/file-manager/document-preview-1.jpg'),
          selected: false,
        }, {
          id: 4,
          title: 'Lorem Ipsum Document',
          size: '12kb',
          image: require('@/assets/images/file-manager/document-preview-1.jpg'),
          selected: false,
        }],
      },

      /**
         * Dropzone configuration options.
         * @see https://github.com/rowanwins/vue-dropzone
         */
      dropzoneOptions: {
        url: 'https://your-api-here/post',
        thumbnailWidth: 150,
      },
    };
  },
  methods: {
    handleItemClick(type, item) {
      const idx = this.filesData[type].indexOf(item);
      this.filesData[type][idx].selected = !this.filesData[type][idx].selected;
    },
  },
};
</script>
