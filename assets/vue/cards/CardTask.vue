<template>
  <v-card class="h-100 d-flex flex-column">
    <v-card-text class="text-body-1 text-white h-100">
      <p class="text-body-2" v-if="task.projectTitle">
        {{ task.projectTitle }} <span class="text-lighten text-smaller">project</span>
      </p>
      
      <router-link :to="{ name: 'taskDetails', params: { id: task.id }}"
        class="d-inline-block text-h4 text-decoration-none"
      >
        {{ task.title }}
      </router-link>

      <app-chip type="priority_high" v-if="task.priority == 'high'">
        high priority
      </app-chip>

      <app-chip type="priority_low" v-if="task.priority == 'low'">
        low priority
      </app-chip>

      <app-chip :type="`status_${task.status}`">
        {{ task.status }}
      </app-chip>

      <p v-if="task.commentsCount > 0" class="mt-3 text-lighten">
        ({{ task.commentsCount }} comments)
      </p>
    </v-card-text>
    <v-card-actions class="mt-auto px-4" v-if="task.updatedDate">
      <app-chip type="schedule">
        <template v-if="!task.completionDate">
          Updated: {{ formatDate(task.updatedDate) }}
        </template>
        <template v-else>
          Completed on: {{ formatDate(task.completionDate) }}
        </template>
      </app-chip>

      <slot></slot>

      <app-chip :to="{ name: 'taskDetails', params: { id: task.id }}">
        details
      </app-chip>
    </v-card-actions>
  </v-card>
</template>
<script setup>
  import { useStore } from '../store.js';
  import { formatDate } from '../helpers/date.js';
  import AppChip from '../components/AppChip.vue';
  import { VCard, VCardText, VCardActions } from 'vuetify/components';

  const { userStore } = useStore();

  const props = defineProps({
    task: Object,
    mode: {
      type: String,
      required: false,
    },
  });

  function isAuthor() {
    return userStore.value.id == task.creator;
  }

  function isWorker() {
    return userStore.value.id == task.worker;
  }

  const task = props.task;
</script>
