import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../pages/Login.vue'),
    meta: { guest: true, title: 'Sign In' },
  },
  {
    path: '/',
    component: () => import('../layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../pages/Dashboard.vue'),
        meta: { title: 'Dashboard' }
      },
      {
        path: 'projects',
        name: 'Projects',
        component: () => import('../pages/projects/ProjectList.vue'),
        meta: { title: 'Projects' }
      },
      {
        path: 'projects/:uuid',
        name: 'ProjectDetail',
        component: () => import('../pages/projects/ProjectDetail.vue'),
        meta: { title: 'Project Details' }
      },
      {
        path: 'activity-logs',
        name: 'ActivityLog',
        component: () => import('../pages/ActivityLog.vue'),
        meta: { title: 'Activity Logs', requiresAdmin: true }
      }
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../pages/NotFound.vue'),
    meta: { title: 'Page Not Found' }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isAuthenticated;
  const isAdmin = authStore.isAdmin;

  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'Login' };
  } else if (to.meta.guest && isAuthenticated) {
    return { name: 'Dashboard' };
  } else if (to.meta.requiresAdmin && !isAdmin) {
    return { name: 'Dashboard' };
  }
  
  return true;
});

router.afterEach((to) => {
  const defaultTitle = 'SaaS Manager';
  document.title = to.meta.title ? `${to.meta.title} | ${defaultTitle}` : defaultTitle;
});

export default router;
