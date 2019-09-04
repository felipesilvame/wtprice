<template>
  <d-card class="card-small user-activity mb-4">

    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <!-- Activity Items -->
    <d-card-body class="p-0">

      <div class="user-activity__item pr-3 py-3">
        <div class="user-activity__item__icon">
          <i class="material-icons">&#xE7FE;</i>
        </div>
        <div class="user-activity__item__content">
          <span class="text-light">23 Minutes ago</span>
          <p>Assigned himself to the <a href="#">Shards Dashboards</a> project.</p>
        </div>
        <div class="user-activity__item__action ml-auto">
          <button class="ml-auto btn btn-sm btn-white">View Project</button>
        </div>
      </div>

      <div class="user-activity__item pr-3 py-3">
        <div class="user-activity__item__icon">
          <i class="material-icons">&#xE5CA;</i>
        </div>
        <div class="user-activity__item__content">
          <span class="text-light">2 Hours ago</span>
          <p>Marked <a href="#">7 tasks</a> as <d-badge pill outline theme="success">Complete</d-badge> inside the <a href="#">DesignRevision</a> project.</p>
        </div>
      </div>

      <div class="user-activity__item pr-3 py-3">
        <div class="user-activity__item__icon">
          <i class="material-icons">&#xE7FE;</i>
        </div>
        <div class="user-activity__item__content">
          <span class="text-light">3 Hours and 10 minutes ago</span>
          <p>Added <a href="#">Jack Nicholson</a> and <a href="#">3 others</a> to the <a href="#">DesignRevision</a> team.</p>
        </div>
        <div class="user-activity__item__action ml-auto">
          <button class="ml-auto btn btn-sm btn-white">View Team</button>
        </div>
      </div>

      <div class="user-activity__item pr-3 py-3">
        <div class="user-activity__item__icon">
          <i class="material-icons">&#xE868;</i>
        </div>
        <div class="user-activity__item__content">
          <span class="text-light">2 Days ago</span>
          <p>Opened <a href="#">3 issues</a> in <a href="#">2 projects</a>.</p>
        </div>
      </div>

      <div class="user-activity__item pr-3 py-3">
        <div class="user-activity__item__icon">
          <i class="material-icons">&#xE065;</i>
        </div>
        <div class="user-activity__item__content">
          <span class="text-light">2 Days ago</span>
          <p>Added <a href="#">3 new tasks</a> to the <a href="#">DesignRevision</a> project:</p>
          <ul class="user-activity__item__task-list mt-2">
            <li>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="user-activity-task-1" />
                <label class="custom-control-label" for="user-activity-task-1">Fix blog pagination issue.</label>
              </div>
            </li>
            <li>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="user-activity-task-2" />
                <label class="custom-control-label" for="user-activity-task-2">Remove extra padding from blog post container.</label>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="user-activity__item pr-3 py-3">
        <div class="user-activity__item__icon">
          <i class="material-icons">&#xE5CD;</i>
        </div>
        <div class="user-activity__item__content">
          <span class="text-light">2 Days ago</span>
          <p>Marked <a href="#">3 tasks</a> as <d-badge pill outline theme="danger">Invalid</d-badge> inside the <a href="#">Shards Dashboards</a> project.</p>
        </div>
      </div>

    </d-card-body>
    <d-card-footer class="border-top">
      <d-button small class="btn-sm btn-white d-table mx-auto">Load More</d-button>
    </d-card-footer>
  </d-card>
</template>

<script>
export default {
  name: 'user-activity',
  props: {
    /**
     * The component title.
     */
    title: {
      type: String,
      default: 'User Activity',
    },
  },
};
</script>
