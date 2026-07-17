<template>
  <div v-if="projectStore.loading && !project" class="flex justify-center items-center h-64">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
  </div>
  
  <div v-else-if="project" class="space-y-6">
    <!-- Header / Project Info -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 lg:p-8">
      <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-3xl font-bold text-slate-800">{{ project.name }}</h1>
            <span class="text-xs font-semibold px-3 py-1 rounded-full capitalize border" :class="{
              'bg-white border-slate-300 text-slate-600': project.status === 'pending',
              'bg-slate-100 border-slate-300 text-slate-800': project.status === 'in_progress',
              'bg-slate-900 border-transparent text-white': project.status === 'completed'
            }">
              {{ project.status.replace('_', ' ') }}
            </span>
          </div>
          <p class="text-slate-500 text-lg max-w-3xl">{{ project.description || 'No description provided.' }}</p>
          
          <div class="flex flex-wrap items-center gap-6 mt-6 text-sm text-slate-600">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              <span>Started: <span class="font-medium text-slate-800">{{ formatDate(project.start_date) || 'Not set' }}</span></span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <span>Due: <span class="font-medium text-slate-800">{{ formatDate(project.due_date) || 'Not set' }}</span></span>
            </div>
            <div v-if="project.creator" class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                {{ project.creator.name.charAt(0) }}
              </div>
              <span>Created by {{ project.creator.name }}</span>
            </div>
          </div>
        </div>
        
        <div v-if="authStore.isAdmin" class="flex items-center gap-3">
          <button @click="showEditModal = true" class="px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 font-medium rounded-xl border border-slate-200 transition-all-smooth flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            Edit Project
          </button>
          <button @click="confirmDeleteProject" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-medium rounded-xl border border-red-100 transition-all-smooth flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Tasks Board / List -->
    <div>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-800">Tasks</h2>
        <button 
          v-if="authStore.isAdmin"
          @click="openTaskModal()"
          class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl font-medium shadow-sm transition-all-smooth flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Add Task
        </button>
      </div>
      
      <div v-if="taskStore.loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="h-20 bg-white rounded-xl border border-slate-100 animate-pulse"></div>
      </div>
      
      <div v-else-if="taskStore.tasks.length === 0" class="bg-white border border-slate-200 border-dashed rounded-2xl p-12 text-center">
        <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
        </div>
        <h3 class="text-lg font-bold text-slate-700 mb-1">No Tasks Yet</h3>
        <p class="text-slate-500 mb-4">Create tasks to break down your project into actionable steps.</p>
        <button v-if="authStore.isAdmin" @click="openTaskModal()" class="text-primary-600 font-medium hover:text-primary-700">Add First Task</button>
      </div>

      <div v-else class="flex flex-col lg:flex-row gap-6 overflow-x-auto pb-4 items-start">
        <!-- Column: To Do -->
        <div class="flex-1 min-w-[320px] bg-slate-50 rounded-2xl p-4 border border-slate-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800">To Do</h3>
            <span class="bg-slate-200 text-slate-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ todoTasks.length }}</span>
          </div>
          <div class="space-y-3 min-h-[150px]" @dragover.prevent @drop="onDrop($event, 'todo')">
            <div 
              v-for="task in todoTasks" 
              :key="task.id"
              :draggable="canEditTask(task)"
              @dragstart="onDragStart($event, task)"
              class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-all-smooth relative group"
              :class="{ 'cursor-grab active:cursor-grabbing': canEditTask(task) }"
            >
              <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full" :class="{
                  'bg-slate-900': task.priority === 'high',
                  'bg-slate-400': task.priority === 'medium',
                  'bg-slate-200': task.priority === 'low'
                }"></span>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ task.priority }}</span>
              </div>
              <h4 class="font-bold text-slate-800 text-[15px] mb-1">{{ task.title }}</h4>
              <p class="text-slate-500 text-xs line-clamp-2 mb-3">{{ task.description }}</p>
              
              <div class="flex items-center justify-between mt-4">
                <div v-if="task.assignee" class="flex items-center gap-2" title="Assignee">
                  <div class="w-6 h-6 rounded-full bg-slate-800 text-white flex items-center justify-center text-[10px] font-bold">
                    {{ task.assignee.name.charAt(0) }}
                  </div>
                  <span class="text-xs font-medium text-slate-600">{{ task.assignee.name }}</span>
                </div>
                <div v-else class="text-xs text-slate-400 italic">Unassigned</div>
                
                <div v-if="canEditTask(task)" class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="openTaskModal(task)" class="p-1.5 text-slate-400 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 rounded">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                  </button>
                  <button v-if="authStore.isAdmin" @click="confirmDeleteTask(task)" class="p-1.5 text-slate-400 hover:text-red-600 bg-slate-50 hover:bg-red-50 rounded">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </div>
            </div>
            
            <div v-if="todoTasks.length === 0" class="h-24 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center text-slate-400 text-sm">
              Drop tasks here
            </div>
          </div>
        </div>

        <!-- Column: In Progress -->
        <div class="flex-1 min-w-[320px] bg-slate-50 rounded-2xl p-4 border border-slate-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800">In Progress</h3>
            <span class="bg-slate-200 text-slate-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ inProgressTasks.length }}</span>
          </div>
          <div class="space-y-3 min-h-[150px]" @dragover.prevent @drop="onDrop($event, 'in_progress')">
            <div 
              v-for="task in inProgressTasks" 
              :key="task.id"
              :draggable="canEditTask(task)"
              @dragstart="onDragStart($event, task)"
              class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-all-smooth relative group"
              :class="{ 'cursor-grab active:cursor-grabbing': canEditTask(task) }"
            >
              <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full" :class="{
                  'bg-slate-900': task.priority === 'high',
                  'bg-slate-400': task.priority === 'medium',
                  'bg-slate-200': task.priority === 'low'
                }"></span>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ task.priority }}</span>
              </div>
              <h4 class="font-bold text-slate-800 text-[15px] mb-1">{{ task.title }}</h4>
              <p class="text-slate-500 text-xs line-clamp-2 mb-3">{{ task.description }}</p>
              
              <div class="flex items-center justify-between mt-4">
                <div v-if="task.assignee" class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-slate-800 text-white flex items-center justify-center text-[10px] font-bold">
                    {{ task.assignee.name.charAt(0) }}
                  </div>
                  <span class="text-xs font-medium text-slate-600">{{ task.assignee.name }}</span>
                </div>
                <div v-else class="text-xs text-slate-400 italic">Unassigned</div>
                
                <div v-if="canEditTask(task)" class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="openTaskModal(task)" class="p-1.5 text-slate-400 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 rounded">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                  </button>
                  <button v-if="authStore.isAdmin" @click="confirmDeleteTask(task)" class="p-1.5 text-slate-400 hover:text-red-600 bg-slate-50 hover:bg-red-50 rounded">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </div>
            </div>
            <div v-if="inProgressTasks.length === 0" class="h-24 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center text-slate-400 text-sm">
              Drop tasks here
            </div>
          </div>
        </div>

        <!-- Column: Completed -->
        <div class="flex-1 min-w-[320px] bg-slate-50 rounded-2xl p-4 border border-slate-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800">Completed</h3>
            <span class="bg-slate-200 text-slate-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ doneTasks.length }}</span>
          </div>
          <div class="space-y-3 min-h-[150px]" @dragover.prevent @drop="onDrop($event, 'done')">
            <div 
              v-for="task in doneTasks" 
              :key="task.id"
              :draggable="canEditTask(task)"
              @dragstart="onDragStart($event, task)"
              class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-all-smooth relative group opacity-75"
              :class="{ 'cursor-grab active:cursor-grabbing': canEditTask(task) }"
            >
              <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider line-through">{{ task.priority }}</span>
              </div>
              <h4 class="font-bold text-slate-500 text-[15px] mb-1 line-through">{{ task.title }}</h4>
              <p class="text-slate-400 text-xs line-clamp-2 mb-3">{{ task.description }}</p>
              
              <div class="flex items-center justify-between mt-4">
                <div v-if="task.assignee" class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-slate-300 text-slate-600 flex items-center justify-center text-[10px] font-bold">
                    {{ task.assignee.name.charAt(0) }}
                  </div>
                  <span class="text-xs font-medium text-slate-500">{{ task.assignee.name }}</span>
                </div>
                <div v-else class="text-xs text-slate-400 italic">Unassigned</div>
                
                <div v-if="canEditTask(task)" class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="openTaskModal(task)" class="p-1.5 text-slate-400 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 rounded">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                  </button>
                  <button v-if="authStore.isAdmin" @click="confirmDeleteTask(task)" class="p-1.5 text-slate-400 hover:text-red-600 bg-slate-50 hover:bg-red-50 rounded">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </div>
            </div>
            <div v-if="doneTasks.length === 0" class="h-24 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center text-slate-400 text-sm">
              Drop tasks here
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Project Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showEditModal = false"></div>
      <div class="bg-white rounded-2xl w-full max-w-md relative z-10 shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-100">
          <h3 class="text-xl font-bold text-slate-800">Edit Project</h3>
        </div>
        <form @submit.prevent="handleEditProject" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Project Name</label>
            <input v-model="projectForm.name" type="text" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
            <textarea v-model="projectForm.description" rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select v-model="projectForm.status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          
          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-6">
            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            <button type="submit" :disabled="projectStore.loading" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Task Modal (Create/Edit) -->
    <div v-if="showTaskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showTaskModal = false"></div>
      <div class="bg-white rounded-2xl w-full max-w-md relative z-10 shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-100">
          <h3 class="text-xl font-bold text-slate-800">{{ taskForm.uuid ? 'Edit Task' : 'New Task' }}</h3>
        </div>
        <form @submit.prevent="handleSaveTask" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Task Title <span class="text-red-500">*</span></label>
            <input v-model="taskForm.title" type="text" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
            <textarea v-model="taskForm.description" rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Priority</label>
              <select v-model="taskForm.priority" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
              <select v-model="taskForm.status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="todo">To Do</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
              </select>
            </div>
          </div>
          
          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-6">
            <button type="button" @click="showTaskModal = false" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            <button type="submit" :disabled="taskStore.loading" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-lg shadow-sm transition-colors">
              {{ taskForm.uuid ? 'Update Task' : 'Create Task' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useProjectStore } from '../../stores/project';
import { useTaskStore } from '../../stores/task';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const projectStore = useProjectStore();
const taskStore = useTaskStore();

const projectUuid = route.params.uuid;
const project = computed(() => projectStore.currentProject);

const todoTasks = computed(() => taskStore.tasks.filter(t => t.status === 'todo'));
const inProgressTasks = computed(() => taskStore.tasks.filter(t => t.status === 'in_progress'));
const doneTasks = computed(() => taskStore.tasks.filter(t => t.status === 'done'));

const showEditModal = ref(false);
const showTaskModal = ref(false);

const projectForm = reactive({
  name: '',
  description: '',
  status: 'pending'
});

const taskForm = reactive({
  uuid: null,
  title: '',
  description: '',
  priority: 'medium',
  status: 'todo',
  assigned_to: null,
});

onMounted(async () => {
  try {
    const proj = await projectStore.fetchProject(projectUuid);
    projectForm.name = proj.name;
    projectForm.description = proj.description || '';
    projectForm.status = proj.status;
    
    await taskStore.fetchTasks(projectUuid);
  } catch (error) {
    if (error.response?.status === 404) {
      router.push({ name: 'Projects' });
    }
  }
});

const handleEditProject = async () => {
  try {
    await projectStore.updateProject(projectUuid, projectForm);
    showEditModal.value = false;
  } catch (error) {
    alert(projectStore.error || 'Failed to update project');
  }
};

const confirmDeleteProject = async () => {
  if (confirm('Are you sure you want to delete this project? All tasks will also be deleted.')) {
    try {
      await projectStore.deleteProject(projectUuid);
      router.push({ name: 'Projects' });
    } catch (error) {
      alert(projectStore.error || 'Failed to delete project');
    }
  }
};

const canEditTask = (task) => {
  if (authStore.isAdmin) return true;
  // Assignee.id from API is UUID; user.uuid is the same UUID field
  if (authStore.isMember && task.assignee?.id === authStore.user?.uuid) return true;
  return false;
};

const updateTaskStatus = async (task) => {
  const previousStatus = task.status;
  try {
    await taskStore.updateTask(projectUuid, task.id, { status: task.status });
  } catch (error) {
    // Revert status on failure by re-fetching
    task.status = previousStatus;
    alert(taskStore.error || 'Failed to update task status');
  }
};

const onDragStart = (event, task) => {
  if (!canEditTask(task)) {
    event.preventDefault();
    return;
  }
  event.dataTransfer.setData('taskId', task.id);
  event.dataTransfer.effectAllowed = 'move';
};

const onDrop = (event, newStatus) => {
  const taskId = event.dataTransfer.getData('taskId');
  const task = taskStore.tasks.find(t => t.id === taskId);
  
  if (task && task.status !== newStatus) {
    task.status = newStatus;
    updateTaskStatus(task);
  }
};

const openTaskModal = (task = null) => {
  if (task) {
    taskForm.uuid = task.id;          // task.id is the UUID from API
    taskForm.title = task.title;
    taskForm.description = task.description || '';
    taskForm.priority = task.priority;
    taskForm.status = task.status;
    taskForm.assigned_to = task.assignee?.id || null;
  } else {
    taskForm.uuid = null;
    taskForm.title = '';
    taskForm.description = '';
    taskForm.priority = 'medium';
    taskForm.status = 'todo';
    taskForm.assigned_to = null;
  }
  showTaskModal.value = true;
};

const handleSaveTask = async () => {
  try {
    if (taskForm.uuid) {
      await taskStore.updateTask(projectUuid, taskForm.uuid, {
        title: taskForm.title,
        description: taskForm.description,
        priority: taskForm.priority,
        status: taskForm.status,
      });
    } else {
      await taskStore.createTask(projectUuid, {
        title: taskForm.title,
        description: taskForm.description,
        priority: taskForm.priority,
        status: taskForm.status,
        assigned_to: taskForm.assigned_to,
      });
    }
    showTaskModal.value = false;
  } catch (error) {
    alert(taskStore.error || 'Failed to save task');
  }
};

const confirmDeleteTask = async (task) => {
  if (confirm('Are you sure you want to delete this task?')) {
    try {
      await taskStore.deleteTask(projectUuid, task.id); // task.id is the UUID
    } catch (error) {
      alert(taskStore.error || 'Failed to delete task');
    }
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date);
};
</script>
