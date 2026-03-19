# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer setup          # First-time setup (install, env, key, migrate, npm install, build)
composer dev            # Dev server + queue + logs + Vite (concurrently)
composer test           # Clear config + run PHPUnit
php artisan test --filter=TestClassName   # Run a single test class
php artisan test --filter=test_method_name  # Run a single test method
npm run dev             # Vite dev server (hot reload)
npm run build           # Production asset build
php artisan migrate && php artisan db:seed  # Bootstrap database
```

Note: npm binary is at `/opt/homebrew/bin/npm`.

Testing uses SQLite `:memory:` (configured in `phpunit.xml`).

## Architecture

Church management system (Primera Iglesia del Nazareno "Ven y Ve", Columbus OH). Laravel 12, Livewire 3, Tailwind CSS 4, Sanctum, Spatie Permission.

### Domain Layer (`app/Domain/{Module}/Actions/`)

Business logic lives in Action classes, not controllers. Each Action has a `public function handle(...)` method returning a Model.

Modules: People, Membership, Discipleship, Library, Attendance, Events.

### Two UI surfaces

- **Admin panel** (web, session auth): Livewire 3 full-page components at `app/Livewire/{Module}/`. Uses `#[Layout('components.layouts.app')]` attribute. Layouts live in `resources/views/components/layouts/` (anonymous component pattern). Routes protected by `auth` + `active` middleware.
- **REST API** (`/api/v1/`, Sanctum token auth): Thin controllers in `app/Http/Controllers/Api/` inject Actions and return API Resources from `app/Http/Resources/`.

### Request → Response flow

`FormRequest` (validation + authorization) → `Controller` (thin orchestrator) → `Action::handle()` → `Resource` (JSON transform) or Livewire view.

### Authorization

Spatie Laravel Permission. Controllers call `$this->authorize('permission.name')`. FormRequests implement `authorize()`. Blade uses `@can()`. Roles/permissions seeded via `RolePermissionSeeder`.

### Enums (`app/Support/Enums/`)

Backed string/int enums with a `label()` method for UI display. Cast on models: `'status' => PersonStatus::class`.

### Audit

`AuditObserver` (in `app/Support/Audit/`) auto-logs create/update/delete on 7 key models. Registered in `AppServiceProvider::boot()`.

### Key conventions

- Base `Controller` must `use AuthorizesRequests` (Laravel 12 removed it from default).
- `EnsureUserIsActive` middleware registered as alias `active` in `bootstrap/app.php`.
- Livewire views: `resources/views/livewire/{module}/{component}.blade.php`.
- Seeders require env vars: `ADMIN_EMAIL`, `ADMIN_NAME`, `ADMIN_PASSWORD`.
