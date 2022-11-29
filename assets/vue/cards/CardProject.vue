<template>
  <v-card class="h-100 d-flex flex-column">
    <v-card-text class="text-body-1 text-white">
      <div class="d-inline-block text-h4 text-decoration-none">
        {{ project.title }}
      </div>

      <div class="text--primary mt-3">
        {{ project.description }}
      </div>

      <p v-if="project.tasksCount > 0" class="mt-3 text-lighten">
        ({{ project.tasksCount }} tasks)
      </p>
    </v-card-text>
    <v-card-actions class="px-4">
      <slot></slot>
      <app-chip type="edit" :to="{ name: 'projectEdit', params: { id: project.id } }">
        edit
      </app-chip>
    </v-card-actions>
  </v-card>
</template>
<script setup>
  import AppChip from '../components/AppChip.vue';
  import { VCard, VCardText, VCardActions } from 'vuetify/components';

  const props = defineProps({
    project: Object
  });

  const project = props.project;
</script>
  