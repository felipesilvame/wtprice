<template>
  <d-container fluid class="main-content-container px-4">
    <d-row class="mt-4">
      <d-col lg="4" sm="12">
        <up-user-details />
        <up-user-contact />
        <up-user-teams />
      </d-col>

      <d-col lg="8">
        <up-user-stats />
        <up-user-performance />
        <up-user-activity />
      </d-col>
    </d-row>
  </d-container>
</template>

<script>
import UserDetails from '@/components/user-profile/UserDetails.vue';
import UserContact from '@/components/user-profile/UserContact.vue';
import UserTeams from '@/components/user-profile/UserTeams.vue';
import UserStats from '@/components/user-profile/UserStats.vue';
import UserPerformance from '@/components/user-profile/UserPerformance.vue';
import UserActivity from '@/components/user-profile/UserActivity.vue';

export default {
  name: 'user-profile',
  components: {
    upUserDetails: UserDetails,
    upUserContact: UserContact,
    upUserTeams: UserTeams,
    upUserStats: UserStats,
    upUserPerformance: UserPerformance,
    upUserActivity: UserActivity,
  },
};
</script>
