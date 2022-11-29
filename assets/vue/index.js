import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';

import { createVuetify } from 'vuetify';
import { aliases, md } from 'vuetify/lib/iconsets/md';
import 'vuetify/styles';
import 'material-design-icons-iconfont/dist/material-design-icons.css';

import App from './pages/App.vue';
import PageTasks from './pages/PageTasks.vue';
import PageTaskDetails from './pages/PageTaskDetails.vue';
import PageTaskEdit from './pages/PageTaskEdit.vue';
import PageProjects from './pages/PageProjects.vue';
import PageProjectEdit from './pages/PageProjectEdit.vue';
import PageUsers from './pages/PageUsers.vue';
import PageUserDetails from './pages/PageUserDetails.vue';
import '../styles/app.scss';

const routes = [
  { path: '/', name: 'front', redirect: '/app' },
  { path: '/app', name: 'tasks', component: PageTasks },
  { path: '/app/task/:id', name: 'taskDetails', component: PageTaskDetails },
  { path: '/app/task/:id/edit', name: 'taskEdit', component: PageTaskEdit },
  { path: '/app/projects', name: 'projects', component: PageProjects },
  { path: '/app/project/:id/edit', name: 'projectEdit', component: PageProjectEdit },
  { path: '/app/users', name: 'users', component: PageUsers },
  { path: '/app/user/:id', name: 'userDetails', component: PageUserDetails },
  { path: '/app/account', name: 'userAccount', component: PageUserDetails },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

const system = createApp(App);
const vuetify = createVuetify({
  theme: {
    defaultTheme: 'dark'
  },
  icons: {
    defaultSet: 'md',
    aliases,
    sets: { md },
  },
});

system.directive('docTitle', function (el, binding) {
  const newTitle = binding.value || el.innerText;

  if (!newTitle || binding.arg === true) {
    return;
  }

  document.title = newTitle;
});

system.use(vuetify);
system.use(router)

system.mount('#app');
