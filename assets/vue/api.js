async function useFetch(url, method = 'GET', body = null) {
  let data = {};
  let error = {};
  let headers = {};

  if (body) {
    body = JSON.stringify(body);
    headers = {
      'Content-Type': 'application/json'
    };
  }

  await fetch(url, { method, body, headers })
    .then((res) => res.json())
    .then((json) => (data = json))
    .catch((err) => (error = err));

  return { data, error }
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
