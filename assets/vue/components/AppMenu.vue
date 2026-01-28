<template>
  <div class="d-none d-sm-flex align-center flex-column flex-sm-row fill-height justify-end">
    <v-btn size="large" class="ml-sm-4" :to="{ name: 'tasks' }" :variant="activePage == 'tasks' ? 'outlined' : 'text'">
      Tasks
    </v-btn>
    <v-btn size="large" class="ml-sm-4" :to="{ name: 'projects' }"
      :variant="activePage == 'projects' ? 'outlined' : 'text'">
      Projects
    </v-btn>
    <v-btn size="large" class="ml-sm-4" :to="{ name: 'users' }" :variant="activePage == 'users' ? 'outlined' : 'text'">
      Users
    </v-btn>
    <v-btn size="large" class="ml-sm-4" :to="{ name: 'reports' }" :variant="activePage == 'reports' ? 'outlined' : 'text'">
      Reports
    </v-btn>
    <v-btn size="large" class="ml-sm-4" :to="{ name: 'userAccount' }" :variant="activePage == 'account' ? 'outlined' : 'text'">
      Me
    </v-btn>
    <v-btn size="small" class="ml-sm-4" href="/user/logout">
      Logout
    </v-btn>
  </div>
</template>
<script setup>
  import { ref, watch } from "vue";
  import { useRoute } from 'vue-router';
  import { VBtn } from 'vuetify/components';

  let activePage = ref(null);

  const route = useRoute();

  watch(
    () => route.name,
    async routeName => {
      switch (routeName) {
        case 'tasks':
        case 'taskEdit':
        case 'taskDetails':
          activePage.value = 'tasks';
          break;
        case 'projects':
        case 'projectEdit':
          activePage.value = 'projects';
          break;
        case 'users':
        case 'userDetails':
          activePage.value = 'users';
          break;
        case 'reports':
          activePage.value = 'reports';
          break;
        case 'userAccount':
          activePage.value = 'account';
          break;
      }
    }
  )
</script>