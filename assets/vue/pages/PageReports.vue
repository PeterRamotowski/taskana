<template>
  <v-container fluid>
    <h1 class="text-h3 mb-6">Time Tracking Reports & Analytics</h1>

    <!-- Period Selector -->
    <v-card class="mb-6">
      <v-card-title>Report Settings</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="3">
            <v-select
              label="Report Type"
              v-model="reportType"
              :items="reportTypes"
              item-title="label"
              item-value="value"
              variant="filled"
              @update:modelValue="onReportTypeChange"
            ></v-select>
          </v-col>
          
          <v-col cols="12" md="3">
            <v-select
              label="Quick Period"
              v-model="quickPeriod"
              :items="quickPeriods"
              item-title="label"
              item-value="value"
              variant="filled"
              @update:modelValue="onQuickPeriodChange"
            ></v-select>
          </v-col>

          <v-col cols="12" md="2">
            <v-text-field
              label="Start Date"
              v-model="startDate"
              type="date"
              variant="filled"
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="2">
            <v-text-field
              label="End Date"
              v-model="endDate"
              type="date"
              variant="filled"
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="2" class="d-flex align-center">
            <v-btn
              color="primary"
              block
              @click="generateReport"
              :loading="loading"
              prepend-icon="assessment"
            >
              Generate
            </v-btn>
          </v-col>
        </v-row>

        <v-row v-if="reportType === 'team' || reportType === 'project'">
          <v-col cols="12" md="6">
            <v-select
              v-if="reportType === 'team' || reportType === 'project'"
              label="Project (optional for team)"
              v-model="selectedProject"
              :items="projects"
              item-title="title"
              item-value="id"
              variant="filled"
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="6">
            <v-select
              v-if="reportType === 'user'"
              label="User"
              v-model="selectedUser"
              :items="users"
              item-title="name"
              item-value="id"
              variant="filled"
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Report Results -->
    <template v-if="reportData">
      <!-- Summary Cards -->
      <v-row class="mb-6">
        <v-col cols="12" md="3" v-for="(card, index) in summaryCards" :key="index">
          <v-card color="primary" variant="tonal">
            <v-card-text>
              <div class="text-caption text-grey">{{ card.label }}</div>
              <div class="text-h4 mt-2">{{ card.value }}</div>
              <div class="text-caption mt-1" v-if="card.subtitle">{{ card.subtitle }}</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row -->
      <v-row class="mb-6">
        <!-- Time by Date Chart -->
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Time Tracking Trend</v-card-title>
            <v-card-text>
              <div class="chart-container">
                <canvas ref="dateChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Time by Day of Week -->
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Time by Day of Week</v-card-title>
            <v-card-text>
              <div class="chart-container">
                <canvas ref="dayOfWeekChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Second Charts Row -->
      <v-row class="mb-6" v-if="reportData.by_priority">
        <!-- Time by Priority -->
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Time by Priority</v-card-title>
            <v-card-text>
              <div class="chart-container">
                <canvas ref="priorityChart"></canvas>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Top Tasks -->
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Top Tasks by Hours</v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="(task, index) in topTasks"
                  :key="task.task_id"
                  :prepend-icon="index === 0 ? 'emoji_events' : 'task'"
                >
                  <v-list-item-title>{{ task.task_title }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ task.total_hours }} hours • {{ task.entries_count }} entries
                  </v-list-item-subtitle>
                  <template v-slot:append>
                    <v-chip size="small" :color="getPriorityColor(task.task_priority)">
                      {{ task.task_priority }}
                    </v-chip>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Data Tables -->
      <v-row>
        <!-- By Task Table -->
        <v-col cols="12" v-if="reportData.by_task && reportData.by_task.length > 0">
          <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
              <span>Time by Task</span>
              <v-btn
                size="small"
                variant="outlined"
                prepend-icon="download"
                @click="exportReport('csv')"
              >
                Export CSV
              </v-btn>
            </v-card-title>
            <v-card-text>
              <v-table class="elevation-1">
                <thead>
                  <tr>
                    <th v-for="header in taskHeaders" :key="header.key">{{ header.title }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in reportData.by_task" :key="item.task_id">
                    <td>{{ item.task_title }}</td>
                    <td>
                      <v-chip size="small" :color="getStatusColor(item.task_status)">
                        {{ item.task_status }}
                      </v-chip>
                    </td>
                    <td>
                      <v-chip size="small" :color="getPriorityColor(item.task_priority)">
                        {{ item.task_priority }}
                      </v-chip>
                    </td>
                    <td>{{ item.total_hours }} h</td>
                    <td>{{ item.estimated_hours || 'N/A' }}</td>
                    <td>{{ item.entries_count }}</td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- By User Table (for team reports) -->
        <v-col cols="12" v-if="reportData.by_user && reportData.by_user.length > 0">
          <v-card>
            <v-card-title>Time by Team Member</v-card-title>
            <v-card-text>
              <v-table class="elevation-1">
                <thead>
                  <tr>
                    <th v-for="header in userHeaders" :key="header.key">{{ header.title }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in reportData.by_user" :key="item.user_id">
                    <td>{{ item.user_name }}</td>
                    <td>{{ item.total_hours }} h</td>
                    <td>{{ item.entries_count }}</td>
                    <td>{{ item.tasks_count }}</td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- By Project Table -->
        <v-col cols="12" v-if="reportData.by_project && reportData.by_project.length > 0">
          <v-card>
            <v-card-title>Time by Project</v-card-title>
            <v-card-text>
              <v-table class="elevation-1">
                <thead>
                  <tr>
                    <th v-for="header in projectHeaders" :key="header.key">{{ header.title }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in reportData.by_project" :key="item.project_id">
                    <td>{{ item.project_title }}</td>
                    <td>{{ item.total_hours }} h</td>
                    <td>{{ item.tasks_count }}</td>
                    <td>{{ item.entries_count }}</td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </template>

    <!-- Empty State -->
    <v-card v-else-if="!loading" class="text-center py-12">
      <v-card-text>
        <v-icon size="64" color="grey">assessment</v-icon>
        <p class="text-h6 mt-4">Select report settings and click Generate</p>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
  import { ref, computed, onMounted, watch, nextTick } from 'vue';
  import {
    loadProjects, loadUsers,
    loadUserReport, loadTeamReport, loadProjectReport,
    exportUserReport, exportTeamReport, exportProjectReport
  } from '../api.js';
  import {
    VContainer, VCard, VCardTitle, VCardText, VRow, VCol,
    VSelect, VTextField, VBtn, VList, VListItem, VListItemTitle,
    VListItemSubtitle, VChip, VTable, VIcon
  } from 'vuetify/components';
  import { Chart, registerables } from 'chart.js';
  import { useStore } from '../store.js';

  Chart.register(...registerables);

  const { userStore } = useStore();

  const reportType = ref('user');
  const quickPeriod = ref('week');
  const startDate = ref('');
  const endDate = ref('');
  const selectedProject = ref(null);
  const selectedUser = ref(null);
  const reportData = ref(null);
  const loading = ref(false);
  const projects = ref([]);
  const users = ref([]);

  const dateChart = ref(null);
  const dayOfWeekChart = ref(null);
  const priorityChart = ref(null);

  let dateChartInstance = null;
  let dayOfWeekChartInstance = null;
  let priorityChartInstance = null;

  const reportTypes = [
    { value: 'user', label: 'My Report' },
    { value: 'team', label: 'Team Report' },
    { value: 'project', label: 'Project Report' }
  ];

  const quickPeriods = [
    { value: 'today', label: 'Today' },
    { value: 'yesterday', label: 'Yesterday' },
    { value: 'week', label: 'This Week' },
    { value: 'last_week', label: 'Last Week' },
    { value: 'month', label: 'This Month' },
    { value: 'last_month', label: 'Last Month' },
    { value: 'quarter', label: 'This Quarter' },
    { value: 'year', label: 'This Year' },
    { value: 'custom', label: 'Custom Range' }
  ];

  const taskHeaders = [
    { title: 'Task', key: 'task_title' },
    { title: 'Status', key: 'task_status' },
    { title: 'Priority', key: 'task_priority' },
    { title: 'Hours', key: 'total_hours' },
    { title: 'Estimated', key: 'estimated_hours' },
    { title: 'Entries', key: 'entries_count' }
  ];

  const userHeaders = [
    { title: 'User', key: 'user_name' },
    { title: 'Total Hours', key: 'total_hours' },
    { title: 'Entries', key: 'entries_count' },
    { title: 'Tasks', key: 'tasks_count' }
  ];

  const projectHeaders = [
    { title: 'Project', key: 'project_title' },
    { title: 'Total Hours', key: 'total_hours' },
    { title: 'Tasks', key: 'tasks_count' },
    { title: 'Entries', key: 'entries_count' }
  ];

  const summaryCards = computed(() => {
    if (!reportData.value || !reportData.value.summary) return [];

    const summary = reportData.value.summary;
    const cards = [
      {
        label: 'Total Hours',
        value: summary.total_hours + ' h',
        subtitle: summary.total_entries !== undefined ? `${summary.total_entries} entries` : undefined
      }
    ];

    if (summary.average_hours_per_day !== undefined) {
      cards.push({
        label: 'Avg Hours/Day',
        value: summary.average_hours_per_day + ' h'
      });
    }

    if (summary.total_users !== undefined) {
      cards.push({
        label: 'Team Members',
        value: summary.total_users
      });
    }

    if (summary.total_tasks !== undefined) {
      cards.push({
        label: 'Tasks',
        value: summary.total_tasks
      });
    }

    if (summary.completion_percentage !== undefined) {
      cards.push({
        label: 'Completion',
        value: summary.completion_percentage + '%',
        subtitle: `${summary.completed_tasks}/${summary.total_tasks} tasks`
      });
    }

    return cards.slice(0, 4);
  });

  const topTasks = computed(() => {
    if (!reportData.value) return [];
    return reportData.value.top_tasks || reportData.value.by_task?.slice(0, 5) || [];
  });

  function onQuickPeriodChange() {
    const today = new Date();
    const formatDate = (date) => date.toISOString().split('T')[0];

    switch (quickPeriod.value) {
      case 'today':
        startDate.value = formatDate(today);
        endDate.value = formatDate(today);
        break;
      case 'yesterday':
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        startDate.value = formatDate(yesterday);
        endDate.value = formatDate(yesterday);
        break;
      case 'week':
        const weekStart = new Date(today);
        weekStart.setDate(today.getDate() - today.getDay() + 1);
        startDate.value = formatDate(weekStart);
        endDate.value = formatDate(today);
        break;
      case 'last_week':
        const lastWeekEnd = new Date(today);
        lastWeekEnd.setDate(today.getDate() - today.getDay());
        const lastWeekStart = new Date(lastWeekEnd);
        lastWeekStart.setDate(lastWeekEnd.getDate() - 6);
        startDate.value = formatDate(lastWeekStart);
        endDate.value = formatDate(lastWeekEnd);
        break;
      case 'month':
        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
        startDate.value = formatDate(monthStart);
        endDate.value = formatDate(today);
        break;
      case 'last_month':
        const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
        startDate.value = formatDate(lastMonthStart);
        endDate.value = formatDate(lastMonthEnd);
        break;
      case 'quarter':
        const quarterMonth = Math.floor(today.getMonth() / 3) * 3;
        const quarterStart = new Date(today.getFullYear(), quarterMonth, 1);
        startDate.value = formatDate(quarterStart);
        endDate.value = formatDate(today);
        break;
      case 'year':
        const yearStart = new Date(today.getFullYear(), 0, 1);
        startDate.value = formatDate(yearStart);
        endDate.value = formatDate(today);
        break;
    }
  }

  function onReportTypeChange() {
    reportData.value = null;
  }

  async function generateReport() {
    loading.value = true;
    reportData.value = null;

    try {
      if (!startDate.value || !endDate.value) {
        alert('Please select start and end dates');
        return;
      }

      const params = {
        start_date: startDate.value,
        end_date: endDate.value
      };

      let result;
      if (reportType.value === 'user') {
        const userId = userStore.value?.id;
        if (!userId) {
          alert('User not authenticated');
          return;
        }
        result = await loadUserReport(userId, params);
        
      } else if (reportType.value === 'team') {
        if (selectedProject.value) {
          params.project_id = selectedProject.value;
        }
        result = await loadTeamReport(params);
        
      } else if (reportType.value === 'project' && selectedProject.value) {
        result = await loadProjectReport(selectedProject.value, params);
      }
      
      if (result?.error) {
        throw result.error;
      }

      reportData.value = result.data?.data || result.data;

      await nextTick();
      renderCharts();

    } catch (error) {
      console.error('Failed to generate report:', error);
      alert('Failed to generate report. Please try again.');
    } finally {
      loading.value = false;
    }
  }

  async function exportReport(format) {
    try {
      const params = {
        start_date: startDate.value,
        end_date: endDate.value,
        format: format
      };

      let result;
      if (reportType.value === 'user') {
        const userId = userStore.value.id;
        result = await exportUserReport(userId, params);
      } else if (reportType.value === 'team') {
        if (selectedProject.value) {
          params.project_id = selectedProject.value;
        }
        result = await exportTeamReport(params);
      } else if (reportType.value === 'project' && selectedProject.value) {
        result = await exportProjectReport(selectedProject.value, params);
      }

      if (result?.error) {
        throw result.error;
      }

      const blob = result.data;
      const link = document.createElement('a');
      link.href = window.URL.createObjectURL(blob);
      link.download = `report_${reportType.value}_${startDate.value}.${format}`;
      link.click();
      window.URL.revokeObjectURL(link.href);

    } catch (error) {
      console.error('Failed to export report:', error);
      alert('Failed to export report. Please try again.');
    }
  }

  function renderCharts() {
    if (!reportData.value) return;

    if (dateChartInstance) dateChartInstance.destroy();
    if (dayOfWeekChartInstance) dayOfWeekChartInstance.destroy();
    if (priorityChartInstance) priorityChartInstance.destroy();

    if (dateChart.value && reportData.value.by_date) {
      const ctx = dateChart.value.getContext('2d');
      dateChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: reportData.value.by_date.map(d => d.date),
          datasets: [{
            label: 'Hours',
            data: reportData.value.by_date.map(d => d.hours),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          }
        }
      });
    }

    if (dayOfWeekChart.value && reportData.value.by_day_of_week) {
      const ctx = dayOfWeekChart.value.getContext('2d');
      const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
      dayOfWeekChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: days,
          datasets: [{
            label: 'Hours',
            data: days.map(day => reportData.value.by_day_of_week[day] || 0),
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          }
        }
      });
    }

    if (priorityChart.value && reportData.value.by_priority) {
      const ctx = priorityChart.value.getContext('2d');
      priorityChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Low', 'Medium', 'High'],
          datasets: [{
            data: [
              reportData.value.by_priority.low || 0,
              reportData.value.by_priority.medium || 0,
              reportData.value.by_priority.high || 0
            ],
            backgroundColor: [
              'rgba(75, 192, 192, 0.5)',
              'rgba(255, 206, 86, 0.5)',
              'rgba(255, 99, 132, 0.5)'
            ],
            borderColor: [
              'rgb(75, 192, 192)',
              'rgb(255, 206, 86)',
              'rgb(255, 99, 132)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
    }
  }

  function getPriorityColor(priority) {
    const colors = {
      low: 'success',
      medium: 'warning',
      high: 'error'
    };
    return colors[priority] || 'default';
  }

  function getStatusColor(status) {
    const colors = {
      pending: 'warning',
      active: 'info',
      complete: 'success'
    };
    return colors[status] || 'default';
  }

  async function loadProjectsData() {
    try {
      const { data, error } = await loadProjects();
      if (error) {
        throw error;
      }
      projects.value = data || [];
    } catch (error) {
      console.error('Failed to load projects:', error);
    }
  }

  async function loadUsersData() {
    try {
      const { data, error } = await loadUsers();
      if (error) {
        throw error;
      }
      users.value = data || [];
    } catch (error) {
      console.error('Failed to load users:', error);
    }
  }

  onMounted(() => {
    onQuickPeriodChange();
    loadProjectsData();
    loadUsersData();
  });
</script>

<style scoped>
  .chart-container {
    position: relative;
    height: 300px;
  }
</style>
