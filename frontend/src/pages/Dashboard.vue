<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Welcome Card -->
      <div class="md:col-span-2 bg-slate-900 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
          <h3 class="text-2xl font-bold mb-2">Welcome back, {{ authStore.user?.name }}!</h3>
          <p class="text-primary-100 mb-6">Here's what's happening with your projects today.</p>
          <router-link :to="{ name: 'Projects' }" class="bg-white text-slate-900 px-6 py-2.5 rounded-lg font-semibold hover:bg-slate-200 transition-all-smooth inline-block shadow-sm">
            View All Projects
          </router-link>
        </div>
        <div class="absolute right-0 top-0 h-full w-1/2 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4OCIgaGVpZ2h0PSI4OCI+PGNpcmNsZSBjeD0iNDQiIGN5PSI0NCIgcj0iNDQiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')] bg-repeat" style="background-size: 24px;"></div>
      </div>

      <!-- Quick Stats -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-center items-center text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-800 flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </div>
        <h4 class="text-3xl font-bold text-slate-800">{{ projectStore.projects.length }}</h4>
        <p class="text-sm text-slate-500 font-medium mt-1">Active Projects</p>
      </div>
    </div>

    <!-- Recent Projects Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-slate-800">Recent Projects</h3>
        <router-link :to="{ name: 'Projects' }" class="text-primary-600 hover:text-primary-700 text-sm font-medium">View all</router-link>
      </div>

      <div v-if="projectStore.loading" class="animate-pulse space-y-4">
        <div v-for="i in 3" :key="i" class="h-16 bg-slate-100 rounded-xl"></div>
      </div>
      
      <div v-else-if="projectStore.projects.length === 0" class="text-center py-12 text-slate-500">
        No projects found. Create one to get started!
      </div>
      
      <div v-else class="space-y-4">
        <div v-for="project in projectStore.projects.slice(0, 5)" :key="project.id" class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all-smooth cursor-pointer" @click="$router.push({ name: 'ProjectDetail', params: { uuid: project.id } })">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center font-bold text-lg">
              {{ project.name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <h4 class="font-semibold text-slate-800">{{ project.name }}</h4>
              <p class="text-sm text-slate-500">{{ project.task_count || 0 }} tasks</p>
            </div>
          </div>
          <div class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide border" :class="{
            'bg-amber-50 border-amber-200 text-amber-700': project.status === 'pending',
            'bg-blue-50 border-blue-200 text-blue-700': project.status === 'in_progress',
            'bg-emerald-50 border-emerald-200 text-emerald-700': project.status === 'completed'
          }">
            {{ project.status.replace('_', ' ') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useProjectStore } from '../stores/project';

const authStore = useAuthStore();
const projectStore = useProjectStore();

onMounted(async () => {
  await projectStore.fetchProjects();
});
</script>
