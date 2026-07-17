<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800">Activity Logs</h1>
      <button 
        @click="fetchLogs(1)"
        class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl font-medium shadow-sm transition-all-smooth flex items-center gap-2"
        :disabled="loading"
      >
        <svg :class="{'animate-spin': loading}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        Refresh
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-bold">
              <th class="px-6 py-4 font-semibold">User</th>
              <th class="px-6 py-4 font-semibold">Action</th>
              <th class="px-6 py-4 font-semibold">Module</th>
              <th class="px-6 py-4 font-semibold">Date & Time</th>
              <th class="px-6 py-4 font-semibold text-right">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loading && logs.length === 0">
              <td colspan="5" class="p-8">
                <div class="space-y-4">
                  <div v-for="i in 5" :key="i" class="h-10 bg-slate-100 rounded-lg animate-pulse w-full"></div>
                </div>
              </td>
            </tr>
            <tr v-else-if="logs.length === 0">
              <td colspan="5" class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-1">No Activity Yet</h3>
                <p class="text-slate-500">Activity logs will appear here when users interact with the system.</p>
              </td>
            </tr>
            
            <!-- Use template to allow details row below the main row -->
            <template v-for="log in logs" :key="log.id">
              <tr class="hover:bg-slate-50/80 transition-colors group">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold flex-shrink-0 text-xs">
                      {{ log.user?.name ? log.user.name.charAt(0).toUpperCase() : 'S' }}
                    </div>
                    <span class="font-bold text-slate-800 text-sm">{{ log.user?.name || 'System' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider border" :class="{
                    'bg-blue-50 text-blue-700 border-blue-200': log.action === 'created',
                    'bg-orange-50 text-orange-700 border-orange-200': log.action === 'updated',
                    'bg-red-50 text-red-700 border-red-200': log.action === 'deleted',
                    'bg-slate-50 text-slate-700 border-slate-200': !['created','updated','deleted'].includes(log.action)
                  }">
                    {{ log.action }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm">
                    <span class="font-bold text-slate-700">{{ formatModelType(log.model_type) }}</span>
                    <span class="text-slate-400 ml-1 text-xs">#{{ log.model_id }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                  {{ formatDate(log.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <button 
                    v-if="(log.old_values && Object.keys(log.old_values).length > 0) || (log.new_values && Object.keys(log.new_values).length > 0)"
                    @click="toggleDetails(log.id)"
                    class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:bg-primary-50 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1 border border-transparent hover:border-primary-100"
                  >
                    {{ expandedRows.includes(log.id) ? 'Hide Changes' : 'View Changes' }}
                    <svg :class="{'rotate-180': expandedRows.includes(log.id)}" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </button>
                  <span v-else class="text-xs text-slate-400 italic">No details</span>
                </td>
              </tr>
              <!-- Details Row -->
              <tr v-if="expandedRows.includes(log.id)" class="bg-slate-50 border-b border-slate-100">
                <td colspan="5" class="px-6 py-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto">
                    <div v-if="log.old_values && Object.keys(log.old_values).length > 0" class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                      <div class="bg-slate-100 px-4 py-2 border-b border-slate-200">
                        <h5 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Previous Data (Before)</h5>
                      </div>
                      <div class="p-4 overflow-x-auto">
                        <pre class="text-[11px] text-slate-600 whitespace-pre-wrap font-mono">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
                      </div>
                    </div>
                    <div v-if="log.new_values && Object.keys(log.new_values).length > 0" class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                      <div class="bg-slate-100 px-4 py-2 border-b border-slate-200">
                        <h5 class="text-xs font-bold text-slate-600 uppercase tracking-wider">New Data (After)</h5>
                      </div>
                      <div class="p-4 overflow-x-auto">
                        <pre class="text-[11px] text-slate-600 whitespace-pre-wrap font-mono">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination Controls -->
      <div class="p-4 border-t border-slate-200 flex items-center justify-between bg-white">
        <div class="text-sm text-slate-500 font-medium">
          Showing <span class="font-bold text-slate-800">{{ logs.length > 0 ? ((pagination.current_page - 1) * pagination.per_page) + 1 : 0 }}</span> to <span class="font-bold text-slate-800">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span> of <span class="font-bold text-slate-800">{{ pagination.total }}</span> entries
        </div>
        
        <div class="flex items-center gap-1">
          <!-- First Page -->
          <button 
            @click="changePage(1)" 
            :disabled="pagination.current_page <= 1"
            class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            title="First Page"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
          </button>
          
          <!-- Previous Page -->
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page <= 1"
            class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors mr-2"
            title="Previous Page"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
          </button>

          <!-- Page Numbers (Max 5 visible) -->
          <template v-for="page in visiblePages" :key="page">
            <button 
              @click="changePage(page)"
              class="w-8 h-8 flex items-center justify-center rounded text-sm font-bold transition-colors"
              :class="page === pagination.current_page ? 'bg-primary-600 text-white border-transparent' : 'border border-slate-200 text-slate-600 hover:bg-slate-50'"
            >
              {{ page }}
            </button>
          </template>

          <!-- Next Page -->
          <button 
            @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page >= pagination.last_page"
            class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors ml-2"
            title="Next Page"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </button>
          
          <!-- Last Page -->
          <button 
            @click="changePage(pagination.last_page)" 
            :disabled="pagination.current_page >= pagination.last_page"
            class="w-8 h-8 flex items-center justify-center rounded border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            title="Last Page"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';

const logs = ref([]);
const loading = ref(false);
const expandedRows = ref([]); // Store IDs of rows with expanded details

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
});

const visiblePages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  
  if (last <= 5) {
    return Array.from({ length: last }, (_, i) => i + 1);
  }
  
  if (current <= 3) {
    return [1, 2, 3, 4, 5];
  }
  
  if (current >= last - 2) {
    return [last - 4, last - 3, last - 2, last - 1, last];
  }
  
  return [current - 2, current - 1, current, current + 1, current + 2];
});

const fetchLogs = async (page = 1) => {
  loading.value = true;
  expandedRows.value = []; // Collapse all rows when changing page
  
  try {
    const response = await api.get(`/activity-logs?page=${page}`);
    const data = response.data.data;
    logs.value = data.data;
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
  if (page >= 1 && page <= pagination.value.last_page && page !== pagination.value.current_page) {
    fetchLogs(page);
  }
};

const toggleDetails = (id) => {
  const index = expandedRows.value.indexOf(id);
  if (index > -1) {
    expandedRows.value.splice(index, 1);
  } else {
    expandedRows.value.push(id);
  }
};

onMounted(() => {
  fetchLogs();
});

const formatModelType = (modelType) => {
  if (!modelType) return 'Record';
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
