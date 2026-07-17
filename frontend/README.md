# Frontend Vue.js - Mini Project Management SaaS

## Fitur yang Diimplementasikan
- **Modern UI**: Tailwind CSS v4 + UI minimalis
- **State Management**: Pinia Store terpisah per domain (Auth, Project, Task)
- **Auto Token Refresh**: Axios interceptor yang merotate JWT token pada HTTP 401
- **Role-based Rendering**: Menampilkan UI berdasarkan level auth pengguna
- **Live Updates**: Listener untuk websocket event task update (bisa disambungkan via Laravel Echo)

## Setup
```bash
npm install
npm run dev
```
