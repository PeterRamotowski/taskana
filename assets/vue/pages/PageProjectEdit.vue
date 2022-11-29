<template>
  <form-project @closeForm="closeForm" :projectId="route.params.id"></form-project>
</template>
<script setup>
  import { useRouter, useRoute } from 'vue-router';
  import formProject from '../forms/FormProject.vue';

  const router = useRouter();
  const route = useRoute();

  function closeForm() {
    router.push({ name: 'projects' });
  }
</script>
