<template>
  <form-task @closeForm="closeForm" :taskId="route.params.id"></form-task>
</template>
<script setup>
  import { useRouter, useRoute } from 'vue-router';
  import formTask from '../forms/FormTask.vue';

  const router = useRouter();
  const route = useRoute();

  function closeForm() {
    router.push({ name: 'taskDetails', params: { id: route.params.id } });
  }
</script>
