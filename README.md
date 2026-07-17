# Mini Project Management SaaS

> Take-Home Test — Fullstack Engineer (SaaS)

Implementasi backend + frontend untuk **Mini Project Management SaaS** multi-tenant — versi ringkas dari Asana/Trello yang mendukung isolasi data antar perusahaan, RBAC dua level, background job, dan feature testing komprehensif.

## Pembaruan Terkini (Final Revamp)

Sebagai penyempurnaan, beberapa pembaruan besar telah dilakukan pada frontend dan backend:

1. **Premium Modern UI**: Desain antarmuka telah ditingkatkan dengan tema warna biru/indigo yang cerah, *glassmorphism bubbles*, dan *dotted background patterns* agar tidak monoton.
2. **Dashboard Data Visualization**: Mengintegrasikan `chart.js` & `vue-chartjs` untuk menampilkan *Doughnut Chart* interaktif pada *Dashboard* yang merangkum status seluruh project secara *real-time*.
3. **Data Table Pagination**: Halaman *Activity Logs* dirombak total menggunakan format tabel (bukan *list*) yang dilengkapi dengan navigasi *Pagination* tingkat lanjut (First, Prev, 1, 2, 3, Next, Last).
4. **Admin Route Constraint**: Halaman dan API *Activity Logs* kini sepenuhnya dikunci secara ketat dan hanya dapat diakses/dilihat oleh *user* dengan role **Admin**. (Diimplementasikan melalui Vue Router Guards, Sidebar kondisional, dan *Controller Authorization*).
5. **Kanban Board & Drag-and-Drop**: Pengelolaan *task* pada halaman *Project Detail* kini menggunakan *Kanban Board* interaktif.
6. **Backend Completion**: Seluruh fitur opsional seperti *Audit Trail*, *Pessimistic Locking* (Race Condition), Auto-refresh JWT Token, hingga *Real-time notifications* dan *Soft Delete Recovery* kini telah **diimplementasikan sepenuhnya**.

---

## Table of Contents

1. [Tech Stack & Alasan Pilihan](#tech-stack--alasan-pilihan)
2. [Strategi Multi-Tenancy + Trade-off](#strategi-multi-tenancy--trade-off)
3. [ERD & Skema Database](#erd--skema-database)
4. [RBAC](#rbac)
5. [Background Job](#background-job)
6. [Cara Menjalankan](#cara-menjalankan)
7. [API Documentation](#api-documentation)
8. [Testing](#testing)
9. [Keputusan Teknis yang Masih Ragu](#keputusan-teknis-yang-masih-ragu)

---

## Tech Stack & Alasan Pilihan

### Backend: Laravel 12 (PHP)
- **Alasan:** Eloquent ORM mendukung Global Scope secara native — sangat cocok untuk row-level multi-tenancy tanpa harus mengubah setiap query. Middleware, Policy, dan Queue sudah built-in dan terintegrasi dengan baik.
- **Auth:** JWT via `tymon/jwt-auth` — stateless, cocok untuk API-first SaaS.
- **Database:** SQLite (dev/test) → siap upgrade ke PostgreSQL (konfigurasi `.env` sudah disiapkan).

### Frontend: Vue 3 + Pinia + Tailwind CSS v4
- **Alasan:** Reaktivitas Vue 3 Composition API sangat clean untuk state management per-modul. Pinia menggantikan Vuex dengan API yang lebih sederhana. Tailwind v4 untuk styling yang konsisten.
- **Routing:** Vue Router 4 dengan navigation guard berbasis `return` (bukan `next()` yang deprecated).

---

## Strategi Multi-Tenancy + Trade-off

### Strategi yang Dipilih: **Row-Level Scoping**

Setiap tabel yang berisi data tenant (`projects`, `tasks`, `notifications`) memiliki kolom `company_id`. Isolasi diterapkan di **dua lapisan**:

#### Lapisan 1: Global Scope (Eloquent)
```php
// app/Traits/BelongsToTenant.php
protected static function booted(): void
{
    static::addGlobalScope('tenant', function (Builder $builder) {
        $companyId = app(TenantService::class)->getCompanyId();
        if ($companyId) {
            $builder->where(static::getModel()->getTable() . '.company_id', $companyId);
        }
    });
    
    static::creating(function ($model) {
        $model->company_id = app(TenantService::class)->getCompanyId();
    });
}
```

Semua query Eloquent pada model yang menggunakan trait `BelongsToTenant` otomatis di-scope ke `company_id` milik user yang login, tanpa perlu menulis `->where('company_id', ...)` secara manual di setiap controller.

#### Lapisan 2: Middleware (Tenant Resolution)
```php
// Tenant di-resolve dari user yang login, BUKAN dari URL
$companyId = auth('api')->user()->company_id;
app(TenantService::class)->setCompanyId($companyId);
```

Setelah middleware berjalan, `TenantService` menyimpan `company_id` di app container untuk digunakan oleh Global Scope.

### Trade-off Row-Level Scoping

| Aspek | Row-Level Scoping | Schema-per-tenant | DB-per-tenant |
|---|---|---|---|
| **Kompleksitas setup** | ✅ Rendah | ⚠️ Sedang | ❌ Tinggi |
| **Isolasi keamanan** | ⚠️ Tergantung query | ✅ Baik | ✅ Sangat tinggi |
| **Skalabilitas** | ⚠️ Tabel besar | ⚠️ Banyak schema | ✅ Independen |
| **Backup per-tenant** | ❌ Rumit | ⚠️ Sedang | ✅ Mudah |
| **Query performa** | ⚠️ Perlu index | ⚠️ Sedang | ✅ Terisolasi |
| **Biaya infra** | ✅ Satu DB | ⚠️ Satu DB | ❌ N DB |

**Alasan memilih row-level:** Untuk SaaS B2B skala awal dengan ratusan hingga ribuan tenant, row-level adalah sweet spot antara kemudahan implementasi dan keamanan yang memadai. Schema/DB per tenant baru worth it jika ada kebutuhan compliance data residency yang ketat.

**Mitigasi risiko:** Semua index ditempatkan pada `(company_id, id)` untuk memastikan query tetap efisien meski tabel besar.

---

## ERD & Skema Database

```
companies
├── id (PK)
├── name
├── domain (unique)
├── plan (enum: free, pro, enterprise)
├── status (enum: active, suspended)
└── timestamps

users
├── id (PK)
├── uuid (unique)
├── company_id (FK → companies) [index]
├── name
├── email (unique)
├── password
├── role (enum: admin, member)
├── status (enum: active, inactive)
├── last_login
└── timestamps + soft_deletes

projects
├── id (PK)
├── uuid (unique)
├── company_id (FK → companies) [index]
├── created_by (FK → users)
├── name
├── description
├── status (enum: pending, in_progress, completed)
├── start_date
├── due_date
└── timestamps + soft_deletes

tasks
├── id (PK)
├── uuid (unique)
├── company_id (FK → companies) [index]
├── project_id (FK → projects)
├── assigned_to (FK → users, nullable)
├── title
├── description
├── priority (enum: low, medium, high)
├── status (enum: todo, in_progress, completed)
├── due_date
└── timestamps + soft_deletes

notifications
├── id (PK)
├── uuid (unique)
├── company_id (FK → companies) [index]
├── user_id (FK → users)
├── type
├── title
├── message
├── data (JSON)
├── read_at (nullable)
└── timestamps
```

**Index composite** yang ditambahkan:
- `projects`: `(company_id, deleted_at)`, `(company_id, status)`
- `tasks`: `(company_id, deleted_at)`, `(project_id, company_id)`, `(assigned_to, company_id)`

---

## RBAC

Dua role dalam satu perusahaan:

| Aksi | Admin | Member |
|---|---|---|
| Lihat semua project perusahaan | ✅ | ✅ |
| Buat/ubah/hapus project | ✅ | ❌ (403) |
| Buat task | ✅ | ❌ (403) |
| Lihat semua task dalam project | ✅ | ✅ |
| Update task (assigned ke dirinya) | ✅ | ✅ |
| Update task (bukan miliknya) | ✅ | ❌ (403) |
| Hapus task | ✅ | ❌ (403) |
| Akses data perusahaan lain | ❌ (404) | ❌ (404) |

RBAC diimplementasikan via **Laravel Policy** (`TaskPolicy`, `ProjectPolicy`) yang terdaftar di `AppServiceProvider`. Controller menggunakan `$this->authorize('action', $model)`.

---

## Background Job

Saat task di-assign ke user, sistem mengirimkan notifikasi via queue:

```
[Request] POST /projects/{id}/tasks
    ↓
[TaskController] createTask()
    ↓
[TaskService] createTask() → event(new TaskAssigned($task))
    ↓
[TaskAssigned Event] → dispatch TaskAssignedNotification job
    ↓
[Queue Worker] TaskAssignedNotification::handle()
    ↓ 
[DB] Insert ke tabel notifications (mock email, logged to DB)
```

Job keluar dari request cycle — response dikembalikan ke client sebelum notifikasi diproses. Untuk development, gunakan `QUEUE_CONNECTION=sync` di `.env` (sync langsung di request) atau `database` untuk async yang sesungguhnya.

---

## Cara Menjalankan

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (default) atau PostgreSQL

### 1. Clone Repository

```bash
git clone https://github.com/yogisptra/SaaS-multi-tenant.git
cd SaaS-multi-tenant
```

### 2. Setup Backend

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Jalankan migration
php artisan migrate

# Seed database (membuat 2 perusahaan, 4 user, 6 project, 20+ task)
php artisan db:seed

# Jalankan server
php artisan serve
# → http://localhost:8000
```

> **Untuk PostgreSQL:** Ubah `.env`: `DB_CONNECTION=pgsql`, `DB_HOST=127.0.0.1`, `DB_PORT=5432`, `DB_DATABASE=saas_db`, `DB_USERNAME=postgres`, `DB_PASSWORD=yourpassword`

### 3. Setup Frontend

```bash
cd frontend

# Install dependencies
npm install

# Jalankan dev server
npm run dev
# → http://localhost:5173
```

### 4. Jalankan Tests

```bash
cd backend

# Semua test
php artisan test

# Test spesifik
php artisan test --filter=test_task_rbac_and_isolation
php artisan test --filter=test_admin_can_crud_projects_member_cannot_modify
```

### 5. Akun Seed Default

| Email | Password | Role | Perusahaan |
|---|---|---|---|
| `admin@saas.test` | `password` | Admin | Acme Corp |
| `budi@saas.test` | `password` | Member | Acme Corp |
| `siti@saas.test` | `password` | Member | Acme Corp |
| `andi@saas.test` | `password` | Member | Acme Corp |

> Untuk testing isolasi tenant, daftarkan akun perusahaan baru via `POST /api/v1/auth/register` dengan `company_name` berbeda.

### 6. Queue Worker (opsional, untuk async job)

```bash
# Ubah .env: QUEUE_CONNECTION=database
php artisan queue:work
```

---

## API Documentation

**Base URL:** `http://localhost:8000/api/v1`

**Authentication:** Semua endpoint (kecuali login/register) membutuhkan header:
```
Authorization: Bearer {token}
```

**Format Response:**
```json
{
  "success": true,
  "message": "...",
  "data": { ... }
}
```

### Auth

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| POST | `/auth/register` | Daftar akun + perusahaan baru | ❌ |
| POST | `/auth/login` | Login, dapat JWT token | ❌ |
| POST | `/auth/logout` | Invalidate token | ✅ |
| GET | `/auth/profile` | Info user yang login | ✅ |
| POST | `/auth/refresh` | Refresh JWT token | ✅ |

**Login Request:**
```json
{
  "email": "admin@saas.test",
  "password": "password"
}
```

**Login Response:**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": { "id": 1, "uuid": "...", "role": "admin", ... }
  }
}
```

### Projects

| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/projects` | List project (paginated) | Admin, Member |
| POST | `/projects` | Buat project baru | Admin only |
| GET | `/projects/{uuid}` | Detail project | Admin, Member |
| PATCH | `/projects/{uuid}` | Update project | Admin only |
| DELETE | `/projects/{uuid}` | Hapus project (soft delete) | Admin only |

**Create Project Request:**
```json
{
  "name": "Website Redesign",
  "description": "Optional description",
  "start_date": "2026-07-01",
  "due_date": "2026-09-30"
}
```

### Tasks

| Method | Endpoint | Deskripsi | Role |
|---|---|---|---|
| GET | `/projects/{uuid}/tasks` | List task dalam project | Admin, Member |
| POST | `/projects/{uuid}/tasks` | Buat task baru | Admin only |
| GET | `/projects/{uuid}/tasks/{uuid}` | Detail task | Admin, Member |
| PATCH | `/projects/{uuid}/tasks/{uuid}` | Update task | Admin; Member (own task only) |
| DELETE | `/projects/{uuid}/tasks/{uuid}` | Hapus task | Admin only |

**Create Task Request:**
```json
{
  "title": "Design mockups",
  "description": "Optional",
  "priority": "high",
  "assigned_to": 2,
  "due_date": "2026-08-15"
}
```

### Notifications

| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | `/notifications` | List notifikasi user |
| PATCH | `/notifications/{id}/read` | Tandai dibaca |
| PATCH | `/notifications/read-all` | Tandai semua dibaca |

---

## Testing

Minimal 3 test yang diimplementasikan:

### Test 1: Tenant Isolation (WAJIB)
```
Tests\Feature\ProjectAndTaskTest::test_task_rbac_and_isolation
```
- Admin B dari Perusahaan B mencoba GET task milik Perusahaan A → **404 Not Found**
- User dari perusahaan berbeda tidak bisa membaca/mengubah resource perusahaan lain meski menebak UUID

### Test 2: RBAC — Member Tidak Bisa Modifikasi Project
```
Tests\Feature\ProjectAndTaskTest::test_admin_can_crud_projects_member_cannot_modify
```
- Member mencoba POST `/projects` → **403 Forbidden**
- Admin B mencoba PATCH project milik Perusahaan A → **404 Not Found** (bukan 403, karena scope membuatnya tidak visible)

### Test 3: Task RBAC
```
Tests\Feature\ProjectAndTaskTest::test_task_rbac_and_isolation (bagian 2)
```
- Member A bisa PATCH task yang di-assign ke dirinya → **200 OK**
- Member A mencoba PATCH task yang **tidak** di-assign ke dirinya → **403 Forbidden**

### Test 4–5: Auth Tests
```
Tests\Feature\AuthTest::test_user_can_register_and_login
Tests\Feature\AuthTest::test_invalid_credentials_return_401
```
- Validasi input dan response format

**Menjalankan semua test:**
```bash
php artisan test
# Expected: 7 tests, 38 assertions — PASSED
```

---

## Keputusan Teknis yang Masih Ragu

**Menggunakan UUID vs integer ID sebagai identifier API eksternal.**

Pada implementasi ini, API mengekspos `uuid` sebagai identifier publik untuk semua resource. Ini keputusan tepat dari sisi keamanan (tidak ada sequential ID yang bisa di-enumerate), namun menambah overhead:

1. **Index UUID** tidak seefisien integer pada JOIN query besar
2. **Inkonsistensi**: `users.id` di login response adalah integer, tapi di `UserResource` (untuk assignee) diekspos sebagai UUID — ini menyebabkan bug di frontend (`task.assignee.id` adalah UUID string, tapi `authStore.user.id` adalah integer)

Solusi yang lebih clean: ekspos `uuid` secara konsisten di semua resource, atau buat alias field. Untuk saat ini bug telah di-workaround di frontend dengan membandingkan `task.assignee.id === authStore.user.uuid`.

**Kenapa ragu:** UUID memperlambat query dan memperumit debugging, tapi integer sequential ID adalah security risk nyata di SaaS multi-tenant.

---

## Penanganan Race Condition (Dijelaskan)

Pada kasus dua Member mengupdate status task yang sama bersamaan, terdapat potensi lost update. Solusi yang akan diimplementasikan:

```php
// TaskService::updateTask()
DB::transaction(function () use ($task, $dto) {
    $task = Task::lockForUpdate()->find($task->id);
    $task->update($dto->toArray());
});
```

Alternatif: **Optimistic locking** dengan kolom `version` — update hanya diizinkan jika `version` di request sama dengan di DB, dan `version` di-increment setiap update. Jika tidak cocok, response 409 Conflict.

---

## Struktur Proyek

```
saas-project/
├── backend/                    # Laravel 12 API
│   ├── app/
│   │   ├── DTO/                # Data Transfer Objects
│   │   ├── Events/             # TaskAssigned event
│   │   ├── Http/
│   │   │   ├── Controllers/    # API Controllers
│   │   │   ├── Middleware/     # TenantMiddleware
│   │   │   ├── Requests/       # Form validation
│   │   │   └── Resources/      # API Resource transformers
│   │   ├── Jobs/               # TaskAssignedNotification queue job
│   │   ├── Models/             # Eloquent Models
│   │   ├── Policies/           # RBAC Policies
│   │   ├── Repositories/       # Repository pattern
│   │   ├── Services/           # Business logic layer
│   │   └── Traits/             # BelongsToTenant (Global Scope)
│   ├── database/
│   │   ├── migrations/         # Reversible migrations
│   │   └── seeders/            # Demo data seeder
│   └── tests/
│       └── Feature/            # Integration tests
│           ├── AuthTest.php
│           └── ProjectAndTaskTest.php
│
└── frontend/                   # Vue 3 + Pinia
    └── src/
        ├── layouts/            # DashboardLayout
        ├── pages/              # Login, Dashboard, Projects, 404
        ├── router/             # Vue Router (navigation guards)
        ├── services/           # Axios HTTP client
        └── stores/             # Pinia stores (auth, project, task)
```
