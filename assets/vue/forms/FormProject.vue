<template>
  <v-card min-width="600px">
    <v-card-title v-doc-title:[!props.projectId]="`Edit ${projectData.title || ''} project`">
      {{ props.projectId ? 'Edit' : 'New' }} project
    </v-card-title>

    <v-card-text>
      <v-form ref="form">
        <v-text-field
          label="Title"
          v-model="projectData.title"
          variant="filled"
          :rules="[formRules.required]"
          required
        ></v-text-field>

        <v-textarea
          label="Description"
          v-model="projectData.description"
          auto-grow
          outlined
          rows="3"
          row-height="25"
          shaped
        ></v-textarea>
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
  import { addProject, loadProject, updateProject } from '../api.js';
  import { formRules } from '../helpers/formRules.js';
  import { VCard, VCardTitle, VCardText, VForm, VBtn, VTextField, VTextarea } from 'vuetify/components';

  const form = ref(null);

  const projectData = ref({
    title: null,
    description: null,
  });

  const props = defineProps({
    projectId: String
  });

  const emits = defineEmits({
    closeForm: null
  });

  async function loadData() {
    if (props.projectId) {
      ({ data: projectData.value } = await loadProject(props.projectId));
    }
  }

  async function submitForm() {
    const validateForm = await form.value.validate();

    if (!validateForm.valid) {
      return;
    }

    if (props.projectId) {
      await updateProject(projectData);
    }
    else {
      await addProject(projectData);
    }

    emits('closeForm');
  }

  loadData();
</script>
