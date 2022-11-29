<template>
  <v-card min-width="600px">
    <v-card-title v-if="props.commentId">
      Edit comment
    </v-card-title>

    <v-card-text>
      <v-form ref="form">
        <v-textarea
          label="Description"
          v-model="commentData.description"
          :rules="[formRules.required]"
          required
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
  import { addComment, loadComment, updateComment } from '../api.js';
  import { useStore } from '../store.js';
  import { formRules } from '../helpers/formRules.js';
  import { VCard, VCardTitle, VCardText, VForm, VBtn, VTextarea } from 'vuetify/components';

  const { userStore } = useStore();

  const form = ref(null);

  const commentData = ref({
    description: null,
    author: userStore.value.id,
    task: props.taskId
  });

  const props = defineProps({
    taskId: String,
    commentId: String
  });

  const emits = defineEmits({
    closeForm: null
  });

  async function loadData() {
    if (props.commentId) {
      ({ data: commentData.value } = await loadComment(props.commentId));
    }
  }

  async function submitForm() {
    const validateForm = await form.value.validate();

    if (!validateForm.valid) {
      return;
    }

    if (props.commentId) {
      await updateComment(commentData);
    }
    else {
      await addComment(commentData);
    }

    emits('closeForm');
  }

  loadData();
</script>
