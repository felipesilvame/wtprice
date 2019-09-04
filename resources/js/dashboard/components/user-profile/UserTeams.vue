<template>
  <d-card class="card-small user-teams mb-4">
    <!-- Card Header -->
    <d-card-header class="border-bottom">
      <h6 class="m-0">{{ title }}</h6>
      <div class="block-handle"></div>
    </d-card-header>

    <d-card-body class="p-0">
      <d-container fluid>
        <!-- Teams List -->
        <d-row v-for="(team, idx) in teams" :key="idx" class="px-3">
          <d-col lg="12" sm="1" class="user-teams__image my-auto p-0">
            <img class="rounded" :src="team.image" :alt="team.name">
          </d-col>
          <d-col class="user-teams__info pl-3">
            <h6 class="m-0">{{ team.name }}</h6>
            <span class="text-light">{{ team.members }}</span>
          </d-col>
        </d-row>
      </d-container>
    </d-card-body>
  </d-card>
</template>

<script>
const defaultTeamsData = [
  {
    image: require('@/assets/images/user-profile/team-thumb-1.png'),
    name: 'Team Edison',
    members: '21 Members',
  },
  {
    image: require('@/assets/images/user-profile/team-thumb-2.png'),
    name: 'Team Shelby',
    members: '21 Members',
  },
  {
    image: require('@/assets/images/user-profile/team-thumb-3.png'),
    name: 'Team Dante',
    members: '21 Members',
  },
];

export default {
  name: 'user-teams',
  props: {
    /**
     * Component title.
     */
    title: {
      type: String,
      default: 'Teams',
    },
    /**
     * Teams data.
     */
    teams: {
      type: Array,
      default() {
        return defaultTeamsData;
      },
    },
  },
};
</script>
