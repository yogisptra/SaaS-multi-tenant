<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800">Activity Logs</h1>
      <button 
        @click="fetchLogs"
        class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl font-medium shadow-sm transition-all-smooth flex items-center gap-2"
        :disabled="loading"
      >
        <svg :class="{'animate-spin': loading}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        Refresh
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div v-if="loading && logs.length === 0" class="p-8 space-y-4">
        <div v-for="i in 5" :key="i" class="h-12 bg-slate-100 rounded-xl animate-pulse"></div>
      </div>
      
      <div v-else-if="logs.length === 0" class="p-12 text-center">
        <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <h3 class="text-lg font-bold text-slate-700 mb-1">No Activity Yet</h3>
        <p class="text-slate-500">Activity logs will appear here when users interact with the system.</p>
      </div>

      <div v-else class="divide-y divide-slate-100">
        <div v-for="log in logs" :key="log.id" class="p-5 flex items-start gap-4 hover:bg-slate-50 transition-colors">
          <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold flex-shrink-0">
            {{ log.user?.name ? log.user.name.charAt(0).toUpperCase() : 'S' }}
          </div>
          <div class="flex-1">
            <p class="text-slate-800 font-medium">
              <span class="font-bold text-slate-900">{{ log.user?.name || 'System' }}</span>
              <span class="text-slate-500 mx-1">performed</span>
              <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider" :class="{
                'bg-blue-100 text-blue-700': log.action === 'created',
                'bg-orange-100 text-orange-700': log.action === 'updated',
                'bg-red-100 text-red-700': log.action === 'deleted',
                'bg-slate-100 text-slate-700': !['created','updated','deleted'].includes(log.action)
              }">
                {{ log.action }}
              </span>
              <span class="text-slate-500 mx-1">on</span>
              <span class="font-bold text-slate-700">{{ formatModelType(log.model_type) }}</span>
              <span class="text-slate-400 ml-1">#{{ log.model_id }}</span>
            </p>
            
            <div class="mt-2 text-xs text-slate-400 flex items-center gap-2">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              {{ formatDate(log.created_at) }}
            </div>
            
            <!-- Details Toggle -->
            <details v-if="(log.old_values && Object.keys(log.old_values).length > 0) || (log.new_values && Object.keys(log.new_values).length > 0)" class="mt-3 group">
              <summary class="text-xs font-semibold text-primary-600 cursor-pointer select-none hover:text-primary-700 flex items-center gap-1">
                View Changes
                <svg class="w-3 h-3 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </summary>
              <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div v-if="log.old_values && Object.keys(log.old_values).length > 0">
                  <h5 class="text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Before</h5>
                  <pre class="text-[11px] text-slate-600 overflow-x-auto whitespace-pre-wrap">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
                </div>
                <div v-if="log.new_values && Object.keys(log.new_values).length > 0">
                  <h5 class="text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">After</h5>
                  <pre class="text-[11px] text-slate-600 overflow-x-auto whitespace-pre-wrap">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
                </div>
              </div>
            </details>
          </div>
        </div>
      </div>
      
      <!-- Pagination Controls (Simple) -->
      <div v-if="pagination.total > pagination.per_page" class="p-4 border-t border-slate-100 flex items-center justify-between">
        <div class="text-sm text-slate-500">
          Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }} entries
        </div>
        <div class="flex gap-2">
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page <= 1"
            class="px-3 py-1 border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button 
            @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3 py-1 border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const logs = ref([]);
const loading = ref(false);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
});

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const response = await api.get(`/activity-logs?page=${page}`);
    const data = response.data.data;
    logs.value = data.data; // paginated data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total
    };
  } catch (error) {
    console.error('Failed to fetch activity logs', error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchLogs(page);
  }
};

onMounted(() => {
  fetchLogs();
});

const formatModelType = (modelType) => {
  if (!modelType) return 'Record';
  // Extracts "Project" from "App\Models\Project"
  const parts = modelType.split('\\');
  return parts[parts.length - 1];
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', { 
    month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' 
  }).format(date);
};
</script>
