import { defineStore } from 'pinia';
import api from '../services/api';

export const useTaskStore = defineStore('task', {
  state: () => ({
    tasks: [],
    loading: false,
    error: null,
  }),
  actions: {
    async fetchTasks(projectUuid) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/projects/${projectUuid}/tasks`);
        this.tasks = response.data.data;
        return this.tasks;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch tasks';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async createTask(projectUuid, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post(`/projects/${projectUuid}/tasks`, data);
        this.tasks.push(response.data.data);
        return response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create task';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async updateTask(projectUuid, taskUuid, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.patch(`/projects/${projectUuid}/tasks/${taskUuid}`, data);
        const index = this.tasks.findIndex(t => t.id === taskUuid);
        if (index !== -1) {
          this.tasks[index] = response.data.data;
        }
        return response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update task';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async deleteTask(projectUuid, taskUuid) {
      this.loading = true;
      this.error = null;
      try {
        await api.delete(`/projects/${projectUuid}/tasks/${taskUuid}`);
        this.tasks = this.tasks.filter(t => t.id !== taskUuid);
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete task';
        throw err;
      } finally {
        this.loading = false;
      }
    }
  }
});
