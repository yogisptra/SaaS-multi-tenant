import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isMember: (state) => state.user?.role === 'member',
  },
  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/auth/login', credentials);
        this.token = response.data.data.access_token;
        this.user = response.data.data.user;
        
        localStorage.setItem('token', this.token);
        localStorage.setItem('user', JSON.stringify(this.user));
        
        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Login failed';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async fetchUser() {
      if (!this.token) return;
      
      try {
        const response = await api.get('/auth/profile');
        this.user = response.data.data;
        localStorage.setItem('user', JSON.stringify(this.user));
      } catch (err) {
        if (err.response?.status === 401) {
          this.logout();
        }
      }
    },
    
    async logout() {
      try {
        if (this.token) {
          await api.post('/auth/logout');
        }
      } catch (err) {
        console.error('Logout error', err);
      } finally {
        this.user = null;
        this.token = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      }
    }
  }
});
