<template>
  <v-card class="py-5" contained-text>
    <v-card-title class="text-h3" v-doc-title>
      Users
    </v-card-title>
  </v-card>

  <v-row dense class="my-1">
    <v-col v-for="(user, index) in users" :key="index" cols="12" md="6">
      <card-user :user="user"></card-user>
    </v-col>
  </v-row>
</template>
<script setup>
  import { ref } from "vue";
  import { loadUsers } from '../api.js';
  import CardUser from '../cards/CardUser.vue';
  import { VCard, VCardTitle, VRow, VCol } from 'vuetify/components';

  const users = ref(null);

  async function loadData() {
    ({ data: users.value } = await loadUsers());
  }

  loadData();
</script>
