<template>
  <v-dialog v-model="addMode" width="auto">
    <form-project @closeForm="closeForm"></form-project>
  </v-dialog>

  <v-card class="py-5" contained-text>
    <v-card-title class="text-h3" v-doc-title>
      Projects
    </v-card-title>
  </v-card>

  <p class="text-h6 mt-7 mb-7">
    <app-chip type="add" @click="addMode = true">
      New Project
    </app-chip>
  </p>

  <v-row dense class="my-1">
    <v-col v-for="(project, index) in projects" :key="index" cols="12" md="6">
      <card-project :project="project" :key="project.id">
        <app-chip type="delete" v-if="project.editMode == false" @click="askDeleteProject(project.id)">
          delete
        </app-chip>

        <app-chip type="delete" v-if="project.editMode == true" @click="confirmDeleteProject(project.id)">
          are you sure to delete?
        </app-chip>
      </card-project>
    </v-col>
  </v-row>
</template>
<script setup>
  import { ref } from "vue";
  import { loadProjects, removeProject } from '../api.js';
  import AppChip from '../components/AppChip.vue';
  import CardProject from '../cards/CardProject.vue';
  import formProject from '../forms/FormProject.vue';
  import { VRow, VCol, VCard, VCardTitle, VDialog } from 'vuetify/components';

  const projects = ref(null);

  const addMode = ref(false);

  async function loadData() {
    ({ data: projects.value } = await loadProjects());

    projects.value.forEach(function(project){
      project.editMode = false;
    });
  }

  function askDeleteProject(id) {
    projects.value.forEach(function(project) {
      project.editMode = false;
      if (project.id == id) {
        project.editMode = true;
      }
    });
  }

  async function confirmDeleteProject(id) {
    await removeProject(id);
    loadData();
  }

  function closeForm() {
    addMode.value = false;
    loadData();
  }

  loadData();
</script>
