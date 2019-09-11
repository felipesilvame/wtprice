<template>
  <d-card class="card-small user-details mb-4">

    <!-- Card Header -->
    <d-card-header class="p-0">
      <div class="user-details__bg">
        <img :src="userData.coverImg" :alt="userData.name">
      </div>
    </d-card-header>

    <d-card-body class="p-0">

      <!-- User Avatar -->
      <div class="user-details__avatar mx-auto">
        <img :src="userData.avatarImg" :alt="userData.name">
      </div>

      <!-- User Name -->
      <h4 class="text-center m-0 mt-2">{{ userData.name }}</h4>

      <!-- User Bio -->
      <p class="text-center text-light m-0 mb-2">{{ userData.bio }}</p>

      <!-- User Social -->
      <ul class="user-details__social user-details__social--primary d-table mx-auto mb-4">
        <li v-if="userData.social.facebook" class="mx-1"><a :href="userData.social.facebook"><i class="fab fa-facebook-f"></i></a></li>
        <li v-if="userData.social.twitter" class="mx-1"><a :href="userData.social.twitter"><i class="fab fa-twitter"></i></a></li>
        <li v-if="userData.social.github" class="mx-1"><a :href="userData.social.github"><i class="fab fa-github"></i></a></li>
        <li v-if="userData.social.slack" class="mx-1"><a :href="userData.social.slack"><i class="fab fa-slack"></i></a></li>
      </ul>

      <!-- User Data -->
      <div class="user-details__user-data border-top border-bottom p-4">
        <div class="row mb-3">
          <div class="col w-50">
            <span>Email</span>
            <span>{{ userData.email }}</span>
          </div>
          <div class="col w-50">
            <span>Location</span>
            <span>{{ userData.location }}</span>
          </div>
        </div>
        <div class="row">
          <div class="col w-50">
            <span>Phone</span>
            <span>{{ userData.phone }}</span>
          </div>
          <div class="col w-50">
            <span>Account Number</span>
            <span>{{ userData.accNumber }}</span>
          </div>
        </div>
      </div>

      <!-- User Tags -->
      <div class="user-details__tags p-4">
        <span v-for="(tag, idx) in userData.tags" :key="idx" class="badge badge-pill badge-light text-light text-uppercase mb-2 border">{{ tag }}</span>
      </div>

    </d-card-body>
  </d-card>
</template>

<script>
const defaultUserData = {
  coverImg: require('@/dashboard/assets/images/user-profile/up-user-details-background.jpg'),
  avatarImg: require('@/dashboard/assets/images/avatars/0.jpg'),
  name: 'Sierra Brooks',
  bio: 'I\'m a design focused engineer.',
  email: 'sierra@example.com',
  location: 'Remote',
  phone: '+40 1234 567 890',
  accNumber: '123456789',
  social: {
    facebook: '#',
    twitter: '#',
    github: '#',
    slack: '#',
  },
  tags: [
    'User Experience',
    'UI Design',
    'React JS',
    'HTML & CSS',
    'JavaScript',
    'Bootstrap 4',
  ],
};

export default {
  name: 'user-details',
  props: {
    /**
       * The user data.
       */
    userData: {
      type: Object,
      default() {
        return defaultUserData;
      },
    },
  },
};
</script>
