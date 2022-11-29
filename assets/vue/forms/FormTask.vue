<template>
  <v-card>
    <v-card-title v-doc-title:[!props.taskId]="`Edit ${taskData.title || ''} task`">
      {{ props.taskId ? 'Edit' : 'New' }} task
    </v-card-title>

    <v-card-text>
      <v-form ref="form">
        <v-text-field
          label="Title"
          v-model="taskData.title"
          variant="filled"
          :rules="[formRules.required]"
          required
          class="mb-2"
        ></v-text-field>

        <v-textarea
          label="Description"
          v-model="taskData.description"
          auto-grow
          outlined
          rows="3"
          row-height="25"
          shaped
          class="mb-2"
        ></v-textarea>

        <v-radio-group
          label="Priority"
          v-model="taskData.priority"
          inline
          :rules="[formRules.required]"
          required
          class="mb-2"
        >
          <v-radio
            v-for="p in priorities"
            :key="p.value"
            :label="p.title"
            :value="p.value"
          ></v-radio>
        </v-radio-group>

        <v-radio-group
          label="Project"
          v-model="taskData.project"
          inline
          class="mb-2"
        >
          <v-radio
            v-for="p in projectsData"
            :key="p.value"
            :label="p.title"
            :value="p.value"
          ></v-radio>
        </v-radio-group>

        <v-radio-group
          label="Assign to"
          v-model="taskData.worker"
          inline
          :rules="[formRules.required]"
          required
        >
          <v-radio
            v-for="u in usersData"
            :key="u.value"
            :label="u.name"
            :value="u.id"
          ></v-radio>
        </v-radio-group>
      </v-form>

      <v-btn
        size="small"
        color="error"
        @click="submitForm"
      >
        Save
      </v-btn>
    </v-card-text>
  </v-card>
</template>
<script setup>
  import { ref } from "vue";
  import { loadTask, addTask, loadUsers, updateTask, loadFormProjects } from '../api.js';
  import { useStore } from '../store.js';
  import { formRules } from '../helpers/formRules.js';
  import { VCard, VCardTitle, VCardText, VForm, VBtn, VTextField, VRadio, VRadioGroup, VTextarea } from 'vuetify/components';

  const { userStore } = useStore();

  const usersData = ref(null);
  const projectsData = ref(null);

  const form = ref(null);

  const taskData = ref({
    title: null,
    description: null,
    project: null,
    worker: props.taskId ? null : userStore.value.id
  });

  const props = defineProps({
    taskId: String
  });

  const emits = defineEmits({
    closeForm: null
  });

  const priorities = [
    { value: 'low', title: 'Low' },
    { value: 'medium', title: 'Medium' },
    { value: 'high', title: 'High' },
  ]

  async function loadData() {
    ({ data: usersData.value } = await loadUsers());
    ({ data: projectsData.value } = await loadFormProjects());

    if (props.taskId) {
      ({ data: taskData.value } = await loadTask(props.taskId));
    }
  }

  async function submitForm() {
    const validateForm = await form.value.validate();

    if (!validateForm.valid) {
      return;
    }

    if (props.taskId) {
      await updateTask(taskData);
    }
    else {
      await addTask(taskData);
    }

    emits('closeForm');
  }

  loadData();
</script>
<style scoped>
  .v-selection-control--inline {
    margin-right: 20px;
  }
</style>
