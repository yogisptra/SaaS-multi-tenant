# Backend API - Mini Project Management SaaS

## Fitur yang Diimplementasikan
- **Tenant Isolation**: Row-level scoping dengan trait `BelongsToTenant`
- **RBAC**: Multi-role support (Admin, Member)
- **Activity Log / Audit Trail**: Trait `LogsActivity` pada Project dan Task
- **Pessimistic Locking**: Penanganan Race Condition pada update task
- **Soft Delete Recovery**: Endpoint khusus untuk restore resource
- **Realtime Notifications**: Event broadcast via Laravel Reverb
- **Index Optimization**: Composite index `(project_id, status, assigned_to)` di tabel Tasks

## Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

## Testing
Jalankan feature tests:
```bash
php artisan test
```
