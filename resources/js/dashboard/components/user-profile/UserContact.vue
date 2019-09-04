<template>
  <d-card class="card-small mb-4">

    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <d-card-body>
      <d-form>
        <d-textarea cols="30" rows="6" class="mb-3" style="min-height: 150px;"></d-textarea>
        <d-button size="sm" type="submit" class="btn-accent">Send Message</d-button>
      </d-form>
    </d-card-body>

  </d-card>
</template>

<script>
export default {
  name: 'user-contact',
  props: {
    /**
       * The component's title.
       */
    title: {
      type: String,
      default: 'Send Message',
    },
  },
};
</script>
