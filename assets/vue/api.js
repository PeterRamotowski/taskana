async function useFetch(url, method = 'GET', body = null, options = {}) {
  let data = null;
  let error = null;
  let response = null;
  const headers = {
    'Content-Type': 'application/json',
    ...options.headers
  };

  try {
    response = await fetch(url, { 
      method, 
      body: body ? JSON.stringify(body) : null, 
      headers,
      ...options
    });

    if (options.responseType === 'blob') {
      data = await response.blob();
    } else {
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        data = await response.json();
      } else {
        data = await response.text();
      }
    }

    if (!response.ok) {
      const errorMessage = data?.message || data?.error || `HTTP ${response.status}: ${response.statusText}`;
      throw new Error(errorMessage);
    }
  } catch (err) {
    error = err;
    console.error(`API Error [${method} ${url}]:`, err);
  }

  return { data, error, response };
}

function buildQueryString(params) {
  if (!params) return '';
  const filteredParams = Object.fromEntries(
    Object.entries(params).filter(([, value]) => value != null)
  );
  const queryString = new URLSearchParams(filteredParams).toString();
  return queryString ? '?' + queryString : '';
}

/* Tasks related */

export function loadTasks() {
  return useFetch('/api/tasks');
}
export function loadAssignedTasks(id) {
  return useFetch('/api/tasks/worker/' + id);
}
export function loadCreatedTasks(id) {
  return useFetch('/api/tasks/creator/' + id);
}
export function loadTask(id) {
  return useFetch('/api/task/' + id);
}
export function updateTask(body) {
  return useFetch('/api/task/' + body.value.id, 'PUT', body.value);
}
export function updateTaskStatus(taskId, status) {
  return useFetch('/api/task/'+taskId+'/status/'+status, 'PATCH');
}
export function addTask(body) {
  return useFetch('/api/task', 'POST', body.value);
}
export function removeTask(id) {
  return useFetch('/api/task/' + id, 'DELETE');
}

/* Comments related */

export function loadTaskComments(id) {
  return useFetch('/api/comments/task/' + id);
}
export function loadUserComments(id) {
  return useFetch('/api/comments/user/' + id);
}
export function loadComment(id) {
  return useFetch('/api/comment/' + id);
}
export function addComment(body) {
  return useFetch('/api/comment', 'POST', body.value);
}
export function updateComment(body) {
  return useFetch('/api/comment/' + body.value.id, 'PUT', body.value);
}
export function removeComment(id) {
  return useFetch('/api/comment/' + id, 'DELETE');
}

/* Projects related */

export function loadProjects() {
  return useFetch('/api/projects');
}
export function loadFormProjects() {
  return useFetch('/api/projects/form');
}
export function loadProject(id) {
  return useFetch('/api/project/' + id);
}
export function updateProject(body) {
  return useFetch('/api/project/' + body.value.id, 'PUT', body.value);
}
export function addProject(body) {
  return useFetch('/api/project', 'POST', body.value);
}
export function removeProject(id) {
  return useFetch('/api/project/' + id, 'DELETE');
}

/* Users related */

export function loadUsers() {
  return useFetch('/api/users');
}
export function loadUser(id) {
  return useFetch('/api/user/' + id);
}

/* Time Tracking related */

export function loadTimeEntries(taskId) {
  return useFetch('/api/time-tracking/task/' + taskId + '/entries');
}
export function startTimeTracking(taskId) {
  return useFetch('/api/time-tracking/task/' + taskId + '/start', 'POST');
}
export function stopTimeTracking(entryId) {
  return useFetch('/api/time-tracking/entry/' + entryId + '/stop', 'POST');
}
export function addManualTimeEntry(taskId, body) {
  return useFetch('/api/time-tracking/task/' + taskId + '/manual', 'POST', body);
}

/* Reports related */

export function loadUserReport(userId, params) {
  return useFetch('/api/reports/user/' + userId + buildQueryString(params));
}
export function loadTeamReport(params) {
  return useFetch('/api/reports/team' + buildQueryString(params));
}
export function loadProjectReport(projectId, params) {
  return useFetch('/api/reports/project/' + projectId + buildQueryString(params));
}
export function exportUserReport(userId, params) {
  return useFetch('/api/reports/user/' + userId + '/export' + buildQueryString(params), 'GET', null, { responseType: 'blob' });
}
export function exportTeamReport(params) {
  return useFetch('/api/reports/team/export' + buildQueryString(params), 'GET', null, { responseType: 'blob' });
}
export function exportProjectReport(projectId, params) {
  return useFetch('/api/reports/project/' + projectId + '/export' + buildQueryString(params), 'GET', null, { responseType: 'blob' });
}
