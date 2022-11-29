<template>
  <v-card class="py-5" contained-text v-if="userData" v-doc-title="`${userData.name || ''} details`">
    <v-card-title class="text-h3">
      {{ userData.name }}
    </v-card-title>
  </v-card>

  <p class="text-h6 mt-4 mb-3" v-if="assignedTasks?.length">
    Assigned tasks:
  </p>

  <v-row dense class="my-1">
    <v-col v-for="(task, index) in assignedTasks" :key="index" cols="12" md="6">
      <card-task :task="task"></card-task>
    </v-col>
  </v-row>

  <p class="text-h6 mt-4 mb-3" v-if="createdTasks?.length">
    Created tasks:
  </p>

  <v-row dense class="my-1">
    <v-col v-for="(task, index) in createdTasks" :key="index" cols="12" md="6">
      <card-task :task="task">
        <template v-if="userStore.id === task.creator">
          <app-chip type="delete" v-if="task.editMode == false" @click="askDeleteTask(task.id)">
            delete
          </app-chip>

          <app-chip type="delete" v-if="task.editMode == true" @click="confirmDeleteTask(task.id)">
            are you sure to delete?
          </app-chip>

          <app-chip type="edit" :to="{ name: 'taskEdit', params: { id: task.id }}">
            edit
          </app-chip>
        </template>
      </card-task>
    </v-col>
  </v-row>

  <p class="text-h6 mt-4 mb-3" v-if="createdComments?.length">
    Created comments:
  </p>

  <v-row dense class="my-1">
    <v-col v-for="(item, index) in createdComments" :key="index" cols="12">
      <v-card class="h-100 d-flex flex-column">
        <v-card-text class="text-body-1">
          {{ item.description }}
        </v-card-text>
        <v-card-text class="text-body-2 pt-0">
          <p class="mt-3 text-lighten">
            In task:
            <router-link :to="{ name: 'taskDetails', params: { id: item.task }}">
              {{ item.taskTitle }}
            </router-link>
          </p>
        </v-card-text>
        <v-card-actions class="mt-auto px-4">
          <app-chip type="schedule">
            {{ formatDate(item.createdDate) }}
          </app-chip>

          <app-chip type="delete" v-if="item.deleteMode == false" @click="askDeleteComment(item.id)">
            delete
          </app-chip>

          <app-chip type="delete" v-if="item.deleteMode == true" @click="confirmDeleteComment(item.id)">
            are you sure to delete?
          </app-chip>
        </v-card-actions>
      </v-card>
    </v-col>
  </v-row>
</template>
<script setup>
  import { ref } from "vue";
  import { useRoute } from 'vue-router';
  import { useStore } from '../store.js';
  import { formatDate } from '../helpers/date.js';
  import { loadUser, loadAssignedTasks, loadCreatedTasks, removeTask, loadUserComments, removeComment } from '../api.js';
  import AppChip from '../components/AppChip.vue';
  import CardTask from '../cards/CardTask.vue';
  import { VCard, VCardTitle, VCardText, VCardActions, VRow, VCol } from 'vuetify/components';

  const { userStore } = useStore();

  const route = useRoute();

  const userData = ref({
    name: null,
    id: route.params.id ?? userStore.value.id
  });

  const assignedTasks = ref(null);
  const createdTasks = ref(null);
  const createdComments = ref(null);

  async function loadData() {
    ({ data: userData.value } = await loadUser(userData.value.id));

    if (!userData.value.id) {
      return;
    }

    ({ data: assignedTasks.value } = await loadAssignedTasks(userData.value.id));

    ({ data: createdTasks.value } = await loadCreatedTasks(userData.value.id));

    createdTasks.value.forEach(function(task){
      task.editMode = false;
    });

    ({ data: createdComments.value } = await loadUserComments(userData.value.id));

    createdComments.value.forEach(function(comment){
      comment.deleteMode = false;
    });
  }

  function askDeleteTask(id) {
    createdTasks.value.forEach(function(task) {
      task.editMode = false;
      if (task.id == id) {
        task.editMode = true;
      }
    });
  }

  async function confirmDeleteTask(id) {
    await removeTask(id);
    loadData();
  }

  function askDeleteComment(id) {
    createdComments.value.forEach(function(comment) {
      comment.deleteMode = false;
      if (comment.id == id) {
        comment.deleteMode = true;
      }
    });
  }

  async function confirmDeleteComment(id) {
    await removeComment(id);
    loadData();
  }

  loadData();
</script>
