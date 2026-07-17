<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col fixed inset-y-0 left-0 z-20">
      <div class="h-16 flex items-center px-6 font-bold text-xl tracking-tight border-b border-slate-100">
        <span class="text-primary-600">SaaS</span><span class="text-slate-800">Manager</span>
      </div>
      
      <nav class="flex-1 py-6 px-4 space-y-2">
        <router-link 
          :to="{ name: 'Dashboard' }" 
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all-smooth font-medium"
          :class="[ $route.name === 'Dashboard' ? 'bg-primary-50 text-primary-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' ]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          Dashboard
        </router-link>
        
        <router-link 
          :to="{ name: 'Projects' }" 
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all-smooth font-medium"
          :class="[ $route.name === 'Projects' || $route.name === 'ProjectDetail' ? 'bg-primary-50 text-primary-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' ]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
          Projects
        </router-link>

        <router-link 
          v-if="authStore.isAdmin"
          :to="{ name: 'ActivityLog' }" 
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all-smooth font-medium"
          :class="[ $route.name === 'ActivityLog' ? 'bg-primary-50 text-primary-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' ]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Activity Logs
        </router-link>
      </nav>

    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 flex flex-col min-h-screen relative bg-slate-50/50 overflow-hidden">
      <!-- Background Elements -->
      <div class="absolute inset-0 z-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNlMmU4ZjAiLz48L3N2Zz4=')] bg-repeat" style="background-size: 20px; opacity: 0.7"></div>
      <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary-200/30 rounded-full blur-3xl -z-10 transform translate-x-1/3 -translate-y-1/4 pointer-events-none"></div>
      <div class="absolute bottom-0 left-10 w-[500px] h-[500px] bg-indigo-200/30 rounded-full blur-3xl -z-10 transform -translate-x-1/4 translate-y-1/4 pointer-events-none"></div>
      
      <!-- Top Header -->
      <header class="h-16 bg-white/80 backdrop-blur border-b border-slate-200 flex items-center justify-end px-8 sticky top-0 z-10">
        <div class="flex items-center gap-4">
           <!-- Notifications -->
           <div class="relative">
             <div 
               @click="toggleNotifications"
               class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 cursor-pointer hover:bg-slate-200 transition-all-smooth relative"
             >
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
               <span v-if="notificationStore.unreadCount > 0" class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
             </div>
             
             <!-- Notification Dropdown -->
             <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden z-50">
               <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                 <h3 class="font-semibold text-slate-800">Notifications</h3>
                 <button v-if="notificationStore.unreadCount > 0" @click="notificationStore.markAllAsRead()" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Mark all read</button>
               </div>
               <div class="max-h-80 overflow-y-auto">
                 <div v-if="notificationStore.loading" class="p-4 text-center text-sm text-slate-500">Loading...</div>
                 <div v-else-if="notificationStore.notifications.length === 0" class="p-8 text-center text-sm text-slate-500">No notifications yet</div>
                 <div v-else class="divide-y divide-slate-100">
                   <div 
                     v-for="notif in notificationStore.notifications" 
                     :key="notif.id"
                     @click="handleNotificationClick(notif)"
                     class="p-4 hover:bg-slate-50 transition-colors cursor-pointer"
                     :class="{ 'bg-primary-50/30': !notif.read_at }"
                   >
                     <div class="flex gap-3">
                       <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                       </div>
                       <div>
                         <p class="text-sm font-medium text-slate-800">{{ notif.title }}</p>
                         <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ notif.message }}</p>
                       </div>
                     </div>
                   </div>
                 </div>
               </div>
             </div>
           </div>
           
           <!-- User Profile & Logout -->
           <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
             <div class="text-right hidden md:block">
               <p class="text-sm font-semibold text-slate-800 leading-tight">{{ authStore.user?.name }}</p>
               <p class="text-xs text-slate-500 capitalize font-medium">{{ authStore.user?.role }}</p>
             </div>
             <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm shadow-inner border border-primary-200">
               {{ authStore.user?.name?.charAt(0).toUpperCase() }}
             </div>
             <button 
               @click="handleLogout" 
               class="ml-2 text-slate-400 hover:text-red-600 transition-colors p-1.5 rounded-lg hover:bg-red-50"
               title="Sign Out"
             >
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
             </button>
           </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="flex-1 px-8 pb-8 pt-6 relative z-10" @click="showNotifications = false">
        <router-view :key="$route.fullPath" v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useNotificationStore } from '../stores/notification';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();
const router = useRouter();

const showNotifications = ref(false);

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
};

const handleNotificationClick = async (notif) => {
  if (!notif.read_at) {
    await notificationStore.markAsRead(notif.id);
  }
  showNotifications.value = false;
  // If notification contains project info, could route there:
  // if (notif.data?.project_uuid) router.push({ name: 'ProjectDetail', params: { uuid: notif.data.project_uuid }});
};

onMounted(() => {
  notificationStore.fetchNotifications();
});

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'Login' });
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
