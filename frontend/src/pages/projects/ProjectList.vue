<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800">Projects</h1>
      <button 
        v-if="authStore.isAdmin"
        @click="showCreateModal = true"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl font-medium shadow-sm transition-all-smooth flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        New Project
      </button>
    </div>

    <!-- Project Grid -->
    <div v-if="projectStore.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="h-48 bg-white border border-slate-100 rounded-2xl animate-pulse"></div>
    </div>
    
    <div v-else-if="projectStore.projects.length === 0" class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm">
      <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
      </div>
      <h3 class="text-lg font-bold text-slate-800 mb-1">No Projects Found</h3>
      <p class="text-slate-500 mb-6">Get started by creating your first project.</p>
      <button v-if="authStore.isAdmin" @click="showCreateModal = true" class="text-primary-600 font-medium hover:text-primary-700">Create Project</button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <router-link 
        v-for="project in projectStore.projects" 
        :key="project.id"
        :to="{ name: 'ProjectDetail', params: { uuid: project.id } }"
        class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-md hover:border-primary-200 transition-all-smooth group cursor-pointer block"
      >
        <div class="flex justify-between items-start mb-4">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
            {{ project.name.charAt(0).toUpperCase() }}
          </div>
          <span class="text-xs font-semibold px-2.5 py-1 rounded-full capitalize" :class="{
            'bg-yellow-50 text-yellow-600': project.status === 'pending',
            'bg-blue-50 text-blue-600': project.status === 'in_progress',
            'bg-green-50 text-green-600': project.status === 'completed'
          }">
            {{ project.status.replace('_', ' ') }}
          </span>
        </div>
        <h3 class="text-lg font-bold text-slate-800 mb-1 line-clamp-1">{{ project.name }}</h3>
        <p class="text-slate-500 text-sm mb-4 line-clamp-2 min-h-[40px]">{{ project.description || 'No description provided.' }}</p>
        
        <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
          <div class="text-slate-500 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            {{ project.tasks_count || 0 }} Tasks
          </div>
          <div v-if="project.due_date" class="text-slate-500">
            Due {{ formatDate(project.due_date) }}
          </div>
        </div>
      </router-link>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showCreateModal = false"></div>
      <div class="bg-white rounded-2xl w-full max-w-md relative z-10 shadow-2xl overflow-hidden transform transition-all">
        <div class="p-6 border-b border-slate-100">
          <h3 class="text-xl font-bold text-slate-800">Create New Project</h3>
        </div>
        <form @submit.prevent="handleCreateProject" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Project Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all-smooth" placeholder="Enter project name">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all-smooth" placeholder="Enter description"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
              <input v-model="form.start_date" type="date" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all-smooth">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Due Date</label>
              <input v-model="form.due_date" type="date" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all-smooth">
            </div>
          </div>
          
          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-6">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-all-smooth">Cancel</button>
            <button type="submit" :disabled="projectStore.loading" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-all-smooth disabled:opacity-50 flex items-center gap-2">
              <span v-if="projectStore.loading" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
              Create Project
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useProjectStore } from '../../stores/project';

const authStore = useAuthStore();
const projectStore = useProjectStore();

const showCreateModal = ref(false);
const form = reactive({
  name: '',
  description: '',
  start_date: '',
  due_date: ''
});

onMounted(() => {
  projectStore.fetchProjects();
});

const handleCreateProject = async () => {
  try {
    await projectStore.createProject(form);
    showCreateModal.value = false;
    form.name = '';
    form.description = '';
    form.start_date = '';
    form.due_date = '';
  } catch (error) {
    alert(projectStore.error || 'Failed to create project');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date);
};
</script>
