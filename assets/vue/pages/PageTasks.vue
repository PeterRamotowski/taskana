<template>
  <v-dialog v-model="addMode" width="auto">
    <form-task @closeForm="closeForm"></form-task>
  </v-dialog>

  <v-card class="py-5" contained-text>
    <v-card-title class="text-h3" v-doc-title>
      Tasks assigned to {{ userStore.username }}
    </v-card-title>
  </v-card>

  <p class="text-h6 mt-7 mb-7">
    <app-chip type="add" @click="addMode = true">
      New Task
    </app-chip>
  </p>

  <v-row dense class="my-1">
    <v-col v-for="(task, index) in tasks" :key="index" cols="12" md="6">
      <card-task :task="task" :key="task.id"></card-task>
    </v-col>
  </v-row>
</template>
<script setup>
  import { ref } from "vue";
  import { loadAssignedTasks } from '../api.js';
  import { useStore } from '../store.js';
  import formTask from '../forms/FormTask.vue';
  import CardTask from '../cards/CardTask.vue';
  import AppChip from '../components/AppChip.vue';
  import { VRow, VCol, VCard, VCardTitle, VDialog } from 'vuetify/components';

  const { userStore } = useStore();

  const tasks = ref(null);

  const addMode = ref(false);

  async function loadData() {
    ({ data: tasks.value } = await loadAssignedTasks(userStore.value.id));
  }

  function closeForm() {
    addMode.value = false;
    loadData();
  }

  loadData();
</script>
