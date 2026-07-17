<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Left: Branding / Image -->
    <div class="hidden lg:flex lg:w-1/2 bg-slate-900 text-white p-12 flex-col justify-between relative overflow-hidden">
      <!-- Minimalist geometric pattern -->
      <div class="absolute inset-0 opacity-5 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4OCIgaGVpZ2h0PSI4OCI+PGNpcmNsZSBjeD0iNDQiIGN5PSI0NCIgcj0iNDQiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')] bg-repeat" style="background-size: 24px;"></div>
      
      <div class="relative z-10">
        <h2 class="text-2xl font-bold tracking-tight">SaaS<span class="text-slate-400">Manager</span></h2>
      </div>
      
      <div class="relative z-10 max-w-md">
        <h1 class="text-5xl font-bold mb-6 leading-tight">Manage your projects with absolute clarity.</h1>
        <p class="text-slate-400 text-lg">A minimalist approach to team collaboration and task tracking. Focus on what matters.</p>
      </div>
      
      <div class="relative z-10 text-sm text-slate-500">
        &copy; 2026 SaaS Manager. All rights reserved.
      </div>
    </div>

    <!-- Right: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white relative">
      <div class="absolute top-8 left-8 lg:hidden">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">SaaS<span class="text-slate-400">Manager</span></h2>
      </div>

      <div class="w-full max-w-md mt-12 lg:mt-0">
        <div class="mb-10 text-center lg:text-left">
          <h2 class="text-3xl font-bold text-slate-900 mb-2">Welcome back</h2>
          <p class="text-slate-500">Please enter your details to sign in.</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Email Address</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-slate-900 text-slate-900 placeholder-slate-400 outline-none transition-all-smooth"
              placeholder="name@company.com"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Password</label>
            <input 
              v-model="form.password" 
              type="password" 
              required 
              class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-slate-900 focus:border-slate-900 text-slate-900 placeholder-slate-400 outline-none transition-all-smooth"
              placeholder="••••••••"
            />
          </div>

          <div v-if="authStore.error" class="bg-red-50 text-red-600 border border-red-100 p-4 rounded-xl text-sm font-medium">
            {{ authStore.error }}
          </div>

          <button 
            type="submit" 
            :disabled="authStore.loading"
            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3.5 px-4 rounded-xl transition-all-smooth transform active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2 mt-2 shadow-sm"
          >
            <svg v-if="authStore.loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ authStore.loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: ''
});

const handleLogin = async () => {
  try {
    await authStore.login(form);
    router.push({ name: 'Dashboard' });
  } catch (error) {
    // Error is handled in the store
  }
};
</script>
