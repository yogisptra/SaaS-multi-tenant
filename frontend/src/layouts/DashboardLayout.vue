<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col fixed inset-y-0 left-0 z-20">
      <div class="h-16 flex items-center px-6 font-bold text-xl tracking-tight border-b border-slate-800">
        SaaS<span class="text-slate-400">Manager</span>
      </div>
      
      <nav class="flex-1 py-6 px-4 space-y-2">
        <router-link 
          :to="{ name: 'Dashboard' }" 
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all-smooth"
          :class="[ $route.name === 'Dashboard' ? 'bg-white/10 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800' ]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          Dashboard
        </router-link>
        
        <router-link 
          :to="{ name: 'Projects' }" 
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all-smooth"
          :class="[ $route.name === 'Projects' || $route.name === 'ProjectDetail' ? 'bg-white/10 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800' ]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
          Projects
        </router-link>

        <router-link 
          :to="{ name: 'ActivityLog' }" 
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all-smooth"
          :class="[ $route.name === 'ActivityLog' ? 'bg-white/10 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800' ]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Activity Logs
        </router-link>
      </nav>

    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 flex flex-col min-h-screen relative">
      <!-- Top Header -->
      <header class="h-16 bg-white/80 backdrop-blur border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-10">
        <h2 class="text-xl font-semibold text-slate-800 capitalize">{{ $route.meta.title || $route.name }}</h2>
        <div class="flex items-center gap-4">
           <!-- Notifications -->
           <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 cursor-pointer hover:bg-slate-200 transition-all-smooth">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
           </div>
           
           <!-- User Profile & Logout -->
           <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
             <div class="text-right hidden md:block">
               <p class="text-sm font-semibold text-slate-700 leading-tight">{{ authStore.user?.name }}</p>
               <p class="text-xs text-slate-500 capitalize">{{ authStore.user?.role }}</p>
             </div>
             <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm">
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
      <div class="flex-1 p-8">
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
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

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
