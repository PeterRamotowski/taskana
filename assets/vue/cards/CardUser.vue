<template>
  <v-card class="h-100 d-flex flex-column">
    <v-card-text class="text-body-1 text-white">
      <router-link :to="{ name: 'userDetails', params: { id: user.id } }"
        class="d-inline-block text-h4 text-decoration-none"
      >
        {{ user.name }}
      </router-link>

      <p v-if="user.createdTasksCount > 0" class="mt-3 text-lighten">
        ({{ user.createdTasksCount }} created tasks)
      </p>

      <p v-if="user.assignedTasksCount > 0" class="mt-3 text-lighten">
        ({{ user.assignedTasksCount }} assigned tasks)
      </p>

      <p v-if="user.commentsCount > 0" class="mt-3 text-lighten">
        ({{ user.commentsCount }} comments authored)
      </p>
    </v-card-text>
    <v-card-actions class="px-4">
      <app-chip :to="{ name: 'userDetails', params: { id: user.id } }">
        details
      </app-chip>
    </v-card-actions>
  </v-card>
</template>
<script setup>
  import AppChip from '../components/AppChip.vue';
  import { VCard, VCardText, VCardActions } from 'vuetify/components';

  const props = defineProps({
    user: Object
  });

  const user = props.user;
</script>
