import { defineStore } from 'pinia';
import api from '../services/api';

export const useProjectStore = defineStore('project', {
  state: () => ({
    projects: [],
    currentProject: null,
    loading: false,
    error: null,
  }),
  actions: {
    async fetchProjects() {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/projects');
        // API returns paginated response: response.data.data.data is the array
        const payload = response.data.data;
        this.projects = Array.isArray(payload) ? payload : (payload.data ?? []);
        return this.projects;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch projects';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async fetchProject(uuid) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.get(`/projects/${uuid}`);
        this.currentProject = response.data.data;
        return this.currentProject;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch project';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async createProject(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/projects', data);
        this.projects.push(response.data.data);
        return response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create project';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async updateProject(uuid, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.patch(`/projects/${uuid}`, data);
        const index = this.projects.findIndex(p => p.id === uuid);
        if (index !== -1) {
          this.projects[index] = response.data.data;
        }
        if (this.currentProject && this.currentProject.id === uuid) {
          this.currentProject = response.data.data;
        }
        return response.data.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update project';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    
    async deleteProject(uuid) {
      this.loading = true;
      this.error = null;
      try {
        await api.delete(`/projects/${uuid}`);
        this.projects = this.projects.filter(p => p.id !== uuid);
        if (this.currentProject && this.currentProject.id === uuid) {
          this.currentProject = null;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete project';
        throw err;
      } finally {
        this.loading = false;
      }
    }
  }
});
