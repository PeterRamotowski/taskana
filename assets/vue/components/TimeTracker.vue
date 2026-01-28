<template>
  <v-card class="mb-4">
    <v-card-title class="d-flex align-center">
      <v-icon class="mr-2">schedule</v-icon>
      Time Tracking
    </v-card-title>

    <v-card-text>
      <!-- Time Summary -->
      <v-row class="mb-4">
        <v-col cols="12" md="3">
          <div class="text-caption text-grey">Estimated</div>
          <div class="text-h6">{{ formatHours(estimatedHours) }}</div>
        </v-col>
        <v-col cols="12" md="3">
          <div class="text-caption text-grey">Tracked</div>
          <div class="text-h6">{{ formatHours(summary.total_hours) }}</div>
        </v-col>
        <v-col cols="12" md="3">
          <div class="text-caption text-grey">Remaining</div>
          <div class="text-h6" :class="remainingClass">
            {{ formatHours(summary.remaining_hours) }}
          </div>
        </v-col>
        <v-col cols="12" md="3">
          <div class="text-caption text-grey">Progress</div>
          <v-progress-linear
            :model-value="summary.progress_percentage || 0"
            :color="progressColor"
            height="25"
            class="mt-2"
          >
            <strong>{{ summary.progress_percentage || 0 }}%</strong>
          </v-progress-linear>
        </v-col>
      </v-row>

      <!-- Timer Controls -->
      <div class="mb-4">
        <v-btn
          v-if="!activeTimer"
          color="success"
          prepend-icon="play_arrow"
          @click="startTimer"
          :loading="loading"
        >
          Start Timer
        </v-btn>
        <div v-else>
          <v-chip color="error" size="large" class="mr-2">
            <v-icon start>fiber_manual_record</v-icon>
            {{ formatDuration(elapsedTime) }}
          </v-chip>
          <v-btn
            color="error"
            prepend-icon="stop"
            @click="stopTimer"
            :loading="loading"
          >
            Stop Timer
          </v-btn>
        </div>
      </div>

      <!-- Time Entries List -->
      <v-divider class="my-4"></v-divider>
      
      <div class="d-flex justify-space-between align-center mb-2">
        <h3>Time Entries</h3>
        <v-btn
          size="small"
          variant="outlined"
          prepend-icon="add"
          @click="showManualDialog = true"
        >
          Add Manual Entry
        </v-btn>
      </div>

      <v-list v-if="entries.length > 0">
        <v-list-item
          v-for="entry in entries"
          :key="entry.id"
          class="px-0"
        >
          <template v-slot:prepend>
            <v-icon v-if="entry.is_running" color="error">fiber_manual_record</v-icon>
            <v-icon v-else>schedule</v-icon>
          </template>

          <v-list-item-title>
            {{ entry.user.name }}
            <v-chip size="small" class="ml-2">{{ entry.duration_hours }}h</v-chip>
          </v-list-item-title>

          <v-list-item-subtitle>
            {{ formatDateTime(entry.start_time) }}
            <template v-if="entry.end_time">
              → {{ formatDateTime(entry.end_time) }}
            </template>
            <template v-else>
              (Running)
            </template>
          </v-list-item-subtitle>

          <v-list-item-subtitle v-if="entry.description" class="mt-1">
            {{ entry.description }}
          </v-list-item-subtitle>
        </v-list-item>
      </v-list>

      <div v-else class="text-center text-grey py-4">
        No time entries yet
      </div>
    </v-card-text>
  </v-card>

  <!-- Manual Entry Dialog -->
  <v-dialog v-model="showManualDialog" max-width="500">
    <v-card>
      <v-card-title>Add Manual Time Entry</v-card-title>
      <v-card-text>
        <v-form ref="manualForm">
          <v-text-field
            label="Start Time"
            v-model="manualEntry.startTime"
            type="datetime-local"
            variant="filled"
            :rules="[formRules.required]"
            required
            class="mb-2"
          ></v-text-field>

          <v-text-field
            label="End Time"
            v-model="manualEntry.endTime"
            type="datetime-local"
            variant="filled"
            :rules="[formRules.required]"
            required
            class="mb-2"
          ></v-text-field>

          <v-textarea
            label="Description (optional)"
            v-model="manualEntry.description"
            variant="filled"
            rows="3"
            class="mb-2"
          ></v-textarea>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn @click="showManualDialog = false">Cancel</v-btn>
        <v-btn color="primary" @click="addManualEntry" :loading="loading">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  import { loadTimeEntries as fetchTimeEntries, startTimeTracking, stopTimeTracking, addManualTimeEntry } from '../api.js';
  import { formRules } from '../helpers/formRules.js';
  import {
    VCard, VCardTitle, VCardText, VCardActions,
    VRow, VCol, VBtn, VIcon, VChip, VProgressLinear,
    VDivider, VList, VListItem, VListItemTitle, VListItemSubtitle,
    VDialog, VForm, VTextField, VTextarea, VSpacer
  } from 'vuetify/components';

  const props = defineProps({
    taskId: {
      type: String,
      required: true
    },
    estimatedHours: {
      type: Number,
      default: null
    }
  });

  const entries = ref([]);
  const summary = ref({
    total_hours: 0,
    estimated_hours: null,
    remaining_hours: null,
    progress_percentage: 0
  });
  const activeTimer = ref(null);
  const loading = ref(false);
  const showManualDialog = ref(false);
  const elapsedTime = ref(0);
  const timerInterval = ref(null);

  const manualForm = ref(null);
  const manualEntry = ref({
    startTime: '',
    endTime: '',
    description: ''
  });

  const remainingClass = computed(() => {
    if (summary.value.remaining_hours === null) return '';
    return summary.value.remaining_hours < 0 ? 'text-error' : 'text-success';
  });

  const progressColor = computed(() => {
    const progress = summary.value.progress_percentage || 0;
    if (progress > 100) return 'error';
    if (progress > 75) return 'warning';
    return 'success';
  });

  function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  }

  function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '';
    const date = new Date(dateTimeString);
    return date.toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function formatHours(hours) {
    if (hours === null || hours === undefined) {
      return '-';
    }

    const isNegative = hours < 0;
    const totalMinutes = Math.round(Math.abs(hours) * 60);
    const hrs = Math.floor(totalMinutes / 60);
    const mins = totalMinutes % 60;
    const prefix = isNegative ? '-' : '';

    if (mins === 0) {
      return `${prefix}${hrs}h`;
    }

    return `${prefix}${hrs}h ${mins}m`;
  }

  function updateElapsedTime() {
    if (activeTimer.value) {
      const start = new Date(activeTimer.value.start_time);
      const now = new Date();
      elapsedTime.value = Math.floor((now - start) / 1000);
    }
  }

  function startTimerInterval() {
    if (timerInterval.value) {
      clearInterval(timerInterval.value);
    }
    updateElapsedTime();
    timerInterval.value = setInterval(updateElapsedTime, 1000);
  }

  async function loadTimeEntries() {
    try {
      const { data, error } = await fetchTimeEntries(props.taskId);
      if (error) {
        throw error;
      }
      entries.value = data.data.entries;
      summary.value = data.data.summary;

      const runningEntry = entries.value.find(e => e.is_running);
      if (runningEntry) {
        activeTimer.value = runningEntry;
        startTimerInterval();
      }
    } catch (error) {
      console.error('Failed to load time entries:', error);
    }
  }

  async function startTimer() {
    loading.value = true;
    try {
      const { data, error } = await startTimeTracking(props.taskId);
      if (error) {
        throw error;
      }
      activeTimer.value = data.data;
      startTimerInterval();
      await loadTimeEntries();
    } catch (error) {
      console.error('Failed to start timer:', error);
      alert(error.message || 'Failed to start timer');
    } finally {
      loading.value = false;
    }
  }

  async function stopTimer() {
    if (!activeTimer.value) return;
    
    loading.value = true;
    try {
      const { error } = await stopTimeTracking(activeTimer.value.id);
      if (error) {
        throw error;
      }
      activeTimer.value = null;
      elapsedTime.value = 0;
      if (timerInterval.value) {
        clearInterval(timerInterval.value);
        timerInterval.value = null;
      }
      await loadTimeEntries();
    } catch (error) {
      console.error('Failed to stop timer:', error);
      alert(error.message || 'Failed to stop timer');
    } finally {
      loading.value = false;
    }
  }

  async function addManualEntry() {
    const validateForm = await manualForm.value.validate();
    if (!validateForm.valid) return;

    loading.value = true;
    try {
      const { error } = await addManualTimeEntry(props.taskId, {
        start_time: manualEntry.value.startTime,
        end_time: manualEntry.value.endTime,
        description: manualEntry.value.description
      });
      if (error) {
        throw error;
      }
      
      showManualDialog.value = false;
      manualEntry.value = { startTime: '', endTime: '', description: '' };
      await loadTimeEntries();
    } catch (error) {
      console.error('Failed to add manual entry:', error);
      alert(error.message || 'Failed to add manual entry');
    } finally {
      loading.value = false;
    }
  }

  onMounted(() => {
    loadTimeEntries();
  });

  onUnmounted(() => {
    if (timerInterval.value) {
      clearInterval(timerInterval.value);
    }
  });
</script>

<style scoped>
  .text-error {
    color: rgb(var(--v-theme-error));
  }
  .text-success {
    color: rgb(var(--v-theme-success));
  }
</style>
