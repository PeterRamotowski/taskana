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

        <v-row>
          <v-col cols="12" md="6">
            <v-text-field
              label="Estimated Hours"
              v-model.number="taskData.estimatedHours"
              type="number"
              step="0.5"
              min="0"
              variant="filled"
              hint="Estimated time to complete"
              persistent-hint
              class="mb-2"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              label="Due Date"
              v-model="taskData.dueDate"
              type="datetime-local"
              variant="filled"
              class="mb-2"
            ></v-text-field>
          </v-col>
        </v-row>

        <v-divider class="my-4"></v-divider>

        <v-switch
          label="Recurring Task"
          v-model="taskData.isRecurring"
          color="primary"
          class="mb-2"
        ></v-switch>

        <template v-if="taskData.isRecurring">
          <v-row>
            <v-col cols="12" md="6">
              <v-select
                label="Recurrence Pattern"
                v-model="taskData.recurrencePattern"
                :items="recurrencePatterns"
                item-title="label"
                item-value="value"
                variant="filled"
                :rules="taskData.isRecurring ? [formRules.required] : []"
                class="mb-2"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                label="Every (interval)"
                v-model.number="taskData.recurrenceInterval"
                type="number"
                min="1"
                variant="filled"
                hint="e.g., '2' for every 2 weeks"
                persistent-hint
                class="mb-2"
              ></v-text-field>
            </v-col>
          </v-row>

          <v-text-field
            label="Recurrence End Date (optional)"
            v-model="taskData.recurrenceEndDate"
            type="date"
            variant="filled"
            hint="Leave empty for indefinite recurrence"
            persistent-hint
            class="mb-2"
          ></v-text-field>
        </template>
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
  import { VCard, VCardTitle, VCardText, VForm, VBtn, VTextField, VRadio, VRadioGroup, VTextarea, VRow, VCol, VDivider, VSwitch, VSelect } from 'vuetify/components';

  const { userStore } = useStore();

  const usersData = ref(null);
  const projectsData = ref(null);

  const form = ref(null);

  const taskData = ref({
    title: null,
    description: null,
    project: null,
    worker: props.taskId ? null : userStore.value.id,
    estimatedHours: null,
    dueDate: null,
    isRecurring: false,
    recurrencePattern: null,
    recurrenceInterval: 1,
    recurrenceEndDate: null
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

  const recurrencePatterns = [
    { value: 'daily', label: 'Daily' },
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Yearly' },
  ]

  function toDateTimeLocal(value) {
    if (!value) {
      return null;
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return null;
    }

    const tzOffsetMs = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - tzOffsetMs).toISOString().slice(0, 16);
  }

  function toDateLocal(value) {
    if (!value) {
      return null;
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return null;
    }

    const tzOffsetMs = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - tzOffsetMs).toISOString().slice(0, 10);
  }

  async function loadData() {
    ({ data: usersData.value } = await loadUsers());
    ({ data: projectsData.value } = await loadFormProjects());

    if (props.taskId) {
      ({ data: taskData.value } = await loadTask(props.taskId));
      taskData.value.dueDate = toDateTimeLocal(taskData.value.dueDate);
      taskData.value.recurrenceEndDate = toDateLocal(taskData.value.recurrenceEndDate);
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
