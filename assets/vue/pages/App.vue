<template>
  <v-app>
    <v-app-bar color="grey-darken-2">
      <template v-slot:prepend>
        <router-link :to="{ name: 'front'}" class="text-decoration-none">
          <v-icon icon="home"></v-icon>
        </router-link>
      </template>

      <v-app-bar-title class="text-xs-h6 text-md-h5 justify-start">
        Taskana
      </v-app-bar-title>

      <app-menu></app-menu>

      <template v-slot:append>
        <v-app-bar-nav-icon icon="menu" class="d-sm-none"></v-app-bar-nav-icon>
      </template>
    </v-app-bar>
    
    <v-main>
      <v-container fluid>
        <router-view :key="$route.path"></router-view>
      </v-container>
    </v-main>
  </v-app>
</template>
<script setup>
  import AppMenu from '../components/AppMenu.vue';
  import { VApp, VAppBar, VAppBarTitle, VMain, VContainer, VBtn, VAppBarNavIcon, VIcon } from 'vuetify/components';
</script>
