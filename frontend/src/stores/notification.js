import { defineStore } from 'pinia';
import api from '../services/api';

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    loading: false,
    error: null,
  }),
  getters: {
    unreadCount: (state) => state.notifications.filter(n => !n.read_at).length,
  },
  actions: {
    async fetchNotifications() {
      this.loading = true;
      try {
        const response = await api.get('/notifications');
        this.notifications = response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch notifications';
      } finally {
        this.loading = false;
      }
    },
    async markAsRead(id) {
      try {
        await api.patch(`/notifications/${id}/read`);
        const notification = this.notifications.find(n => n.id === id);
        if (notification) {
          notification.read_at = new Date().toISOString();
        }
      } catch (err) {
        console.error('Failed to mark notification as read', err);
      }
    },
    async markAllAsRead() {
      try {
        await api.patch('/notifications/read-all');
        this.notifications.forEach(n => {
          n.read_at = new Date().toISOString();
        });
      } catch (err) {
        console.error('Failed to mark all notifications as read', err);
      }
    }
  }
});
