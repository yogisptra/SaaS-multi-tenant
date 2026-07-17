<template>
  <div class="min-h-screen bg-slate-900 flex flex-col items-center justify-center text-white relative overflow-hidden">
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
    
    <div class="relative z-10 text-center">
      <h1 class="text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-purple-500 mb-4">404</h1>
      <h2 class="text-3xl font-bold mb-6">Page Not Found</h2>
      <p class="text-slate-400 mb-8 max-w-md mx-auto">We couldn't find the page you're looking for. It might have been removed or the link is incorrect.</p>
      
      <router-link :to="{ name: 'Dashboard' }" class="bg-primary-600 hover:bg-primary-500 text-white font-semibold py-3 px-8 rounded-xl transition-all-smooth inline-flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Dashboard
      </router-link>
    </div>
  </div>
</template>
