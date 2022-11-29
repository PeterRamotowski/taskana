<template>
  <v-chip
    label
    :color="color"
    :size="size"
    :prepend-icon="prependIcon"
    :append-icon="appendIcon"
    :class="classes"
  >
    <slot></slot>
  </v-chip>
</template>
<script setup>
  import { VChip } from 'vuetify/components';

  const props = defineProps({
    type: {
      type: String,
      required: false,
    },
  });

  let color = 'error';
  let size = 'default';
  let prependIcon = props.type;
  let appendIcon = '';
  let classes = 'mr-3';

  if (!props.type) {
    appendIcon = 'start';
  }

  switch (props.type) {
    case 'add-inline':
      prependIcon = 'add';
      classes = 'ml-4 mb-1';
      break;
    case 'takeover':
      prependIcon = 'sports_martial_arts';
      break;
    case 'complete':
      prependIcon = 'check_circle';
      break;
    case 'priority_high':
      color = 'red';
      classes = 'mb-3 ml-3';
      break;
    case 'priority_low':
      prependIcon = '';
      color = 'grey-darken-2';
      classes = 'mb-3 ml-3';
      break;
    case 'status_pending':
      prependIcon = 'change_circle';
      color = 'grey';
      classes = 'mb-3 ml-3';
      break;
    case 'status_active':
      prependIcon = 'run_circle';
      color = 'green';
      classes = 'mb-3 ml-3';
      break;
    case 'status_complete':
      prependIcon = 'check_circle_outline';
      color = 'grey-darken-1';
      classes = 'mb-3 ml-3';
      break; 
    case 'schedule':
    case 'person':
      color = 'grey-lighten-1';
      break; 
  }
</script>
