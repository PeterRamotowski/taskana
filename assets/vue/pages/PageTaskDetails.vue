<template>
  <v-dialog v-model="editMode" width="auto">
    <form-task :taskId="taskData.id" @closeForm="closeTaskForm"></form-task>
  </v-dialog>

  <v-card contained-text v-if="taskData">
    <v-card-text class="text-body-1">
      <p class="text-body-2" v-if="taskData.projectTitle">
        {{ taskData.projectTitle }} <span class="text-lighten text-smaller">project</span>
      </p>

      <p class="text-h3 mb-4" v-doc-title="`${taskData.title || ''} task details`">
        {{ taskData.title }}

        <app-chip type="priority_high" v-if="taskData.priority == 'high'">
          high priority
        </app-chip>

        <app-chip type="priority_low" v-if="taskData.priority == 'low'">
          low priority
        </app-chip>

        <app-chip :type="`status_${taskData.status}`" :key="taskData.status" v-if="taskData.status">
          {{ taskData.status }}
        </app-chip>
      </p>

      <p class="mb-8 multiline">
        {{ taskData.description }}
      </p>

      <div class="mb-3">
        <template v-if="isWorker()">
          <app-chip type="takeover" v-if="taskData.status == 'pending'" @click="changeTaskStatus(taskData.id, 'active')">
            take over
          </app-chip>

          <app-chip type="complete" v-if="taskData.status == 'active'" @click="changeTaskStatus(taskData.id, 'complete')">
            complete
          </app-chip>
        </template>

        <template v-if="isAuthor()">
          <app-chip type="delete" v-if="deleteMode == false" @click="deleteMode = true">
            delete
          </app-chip>

          <app-chip type="delete" v-if="deleteMode == true" @click="confirmDeleteTask()">
            are you sure to delete?
          </app-chip>

          <app-chip type="edit" @click="editMode = true">
            edit
          </app-chip>
        </template>
      </div>

      <div class="text-body-2">
        <app-chip type="schedule">
          Updated: {{ formatDate(taskData.updatedDate) }}
        </app-chip>
        <app-chip type="schedule">
          Created: {{ formatDate(taskData.createdDate) }}
        </app-chip>
        <app-chip type="schedule" v-if="taskData.dueDate">
          Due: {{ formatDate(taskData.dueDate) }}
        </app-chip>
        <app-chip type="person" v-if="taskData.worker" :to="{ name: 'userDetails', params: { id: taskData.worker }}">
          Assigned to: {{ taskData.workerUsername }}
        </app-chip>
        <app-chip type="schedule" v-if="taskData.isRecurring">
          🔄 Recurring: {{ taskData.recurrencePattern }} (every {{ taskData.recurrenceInterval }})
        </app-chip>
      </div>
    </v-card-text>
  </v-card>

  <time-tracker 
    v-if="taskData.id" 
    :task-id="taskData.id" 
    :estimated-hours="taskData.estimatedHours"
    class="mt-4"
  />

  <p class="text-h6 mt-7 mb-7">
    Comments
    <app-chip type="add-inline" @click="addCommentMode = !addCommentMode">
      New Comment
    </app-chip>
  </p>

  <form-comment v-if="addCommentMode" :taskId="route.params.id" @closeForm="closeCommentForm"></form-comment>

  <v-row dense class="my-1">
    <v-col v-for="(item, index) in comments" :key="index" cols="12">
      <v-card class="h-100 d-flex flex-column">
        <v-card-text class="text-body-1 h-100">
          {{ item.description }}
        </v-card-text>
        <v-card-actions class="mt-auto px-4">
          <app-chip type="schedule">
            {{ formatDate(item.createdDate) }}
          </app-chip>

          <app-chip type="person" v-if="item.author"
            :to="{ name: 'userDetails', params: { id: item.author }}"
          >
            Author: {{ item.authorUsername }}
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
  import { useRouter, useRoute } from 'vue-router';
  import { loadTask, removeTask, updateTaskStatus, loadTaskComments, removeComment } from '../api.js';
  import { useStore } from '../store.js';
  import { formatDate } from '../helpers/date.js';
  import formTask from '../forms/FormTask.vue';
  import formComment from '../forms/FormComment.vue';
  import AppChip from '../components/AppChip.vue';
  import TimeTracker from '../components/TimeTracker.vue';
  import { VCard, VCardText, VCardActions, VRow, VCol, VDialog } from 'vuetify/components';

  const { userStore } = useStore();

  const router = useRouter();
  const route = useRoute();

  const comments = ref(null);

  const addCommentMode = ref(false);
  const editMode = ref(false);
  const deleteMode = ref(false);

  const taskData = ref({
    title: null,
    project: null,
    worker: route.params.id ? null : userStore.value.id,
    id: route.params.id
  });

  async function loadData() {
    ({ data: taskData.value } = await loadTask(route.params.id));

    if (!taskData.value.id) {
      return;
    }

    loadComments();
  }

  async function confirmDeleteTask() {
    await removeTask(taskData.value.id);
    router.push({ name: 'tasks' });
  }

  async function changeTaskStatus(id, status) {
    await updateTaskStatus(id, status);
    loadData();
  }

  async function loadComments() {
    ({ data: comments.value } = await loadTaskComments(route.params.id));

    comments.value.forEach(function(comment){
      comment.deleteMode = false;
    });
  }

  function askDeleteComment(id) {
    comments.value.forEach(function(comment) {
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

  function closeTaskForm() {
    editMode.value = false;
    loadData();
  }

  function closeCommentForm() {
    addCommentMode.value = false;
    loadComments();
  }

  function isAuthor() {
    return userStore.value.id === taskData.value.creator;
  }

  function isWorker() {
    return userStore.value.id === taskData.value.worker;
  }

  loadData();
</script>
