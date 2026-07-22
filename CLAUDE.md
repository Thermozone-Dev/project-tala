# Project Context

## Stack
- **TALL Stack**: TailwindCSS, Alpine.js, Laravel, Livewire
- **Admin Panel**: Laravel Filament (v3)
- **Language**: PHP (Laravel), JavaScript (Alpine.js)
- **CSS**: TailwindCSS v3
- **Frontend Build Tool**: Vite

## Database
- Supports both **Microsoft SQL Server (MSSQL)** and **MySQL**
- Always write **database-agnostic** Eloquent queries — avoid raw SQL unless absolutely necessary
- When raw SQL is needed, provide variants for both drivers or use `DB::getDriverName()` to branch logic
- Never use MySQL-only functions (e.g. `GROUP_CONCAT`) or MSSQL-only functions (e.g. `STRING_AGG`) without a cross-driver fallback
- Use Laravel migrations with compatible column types across both drivers:
  - Prefer `string()`, `text()`, `integer()`, `boolean()`, `timestamps()` — these are safe on both
  - Avoid `json()` columns without checking driver support
  - Use `unsignedBigInteger()` for foreign keys consistently

## Commands
- `npm run dev` — Start Vite development server (hot reload)
- `npm run build` — Build frontend assets for production
- `php artisan serve` — Start Laravel development server
- `php artisan migrate` — Run database migrations
- `php artisan filament:make-resource` — Scaffold a Filament resource
- `php artisan make:livewire` — Scaffold a Livewire component

## Conventions

### General
- Follow **PSR-12** coding standards for PHP
- Use **Laravel naming conventions**: PascalCase for models, snake_case for DB columns/tables
- Always use **typed properties** and **return types** in PHP classes
- Prefer **service classes** for business logic — keep controllers and Livewire components thin

### Filament
- All admin resources live in `app/Filament/Resources/`
- Use Filament's built-in form components and table columns — avoid raw Blade inside panels
- Define `form()`, `table()`, `infolist()` methods cleanly and keep them readable
- Use **Filament Actions** (not custom routes) for resource-level operations
- Register widgets in the appropriate Filament panel provider

### Livewire
- Use Livewire v3 features: `#[Computed]`, `wire:model.live`, `#[Validate]`
- Keep component logic in the PHP class, not in Blade templates
- Use `$this->dispatch()` for cross-component events

### TailwindCSS
- Use utility classes directly in Blade/Livewire templates
- Avoid writing custom CSS unless Tailwind cannot handle the case
- Use Filament's design tokens and color variables for consistency inside the admin panel

### Alpine.js
- Use Alpine.js for lightweight, isolated UI interactions (dropdowns, modals, toggles)
- Do not use Alpine.js for anything that requires server state — use Livewire instead
- Prefix Alpine data with `x-data`, directives with `x-`

## File Structure
```
app/
├── Filament/
│   ├── Resources/        # Filament resources
│   ├── Pages/            # Custom Filament pages
│   └── Widgets/          # Dashboard widgets
├── Http/
│   ├── Controllers/      # Thin controllers
│   └── Livewire/         # Livewire components (if not using Volt)
├── Models/               # Eloquent models
├── Services/             # Business logic
└── Providers/            # Service & panel providers

resources/
├── views/
│   ├── livewire/         # Livewire Blade templates
│   └── components/       # Blade components
└── css/ & js/            # Vite entry points
```

## Environment & Config
- `.env` must define `DB_CONNECTION` as either `mysql` or `sqlsrv`
- Always test migrations and queries against **both** DB drivers before committing
- Use `config('database.default')` or `DB::getDriverName()` when conditional DB logic is needed
- Keep `.env.example` updated with all required keys

## Code Quality
- Write **Pest** tests for services and critical business logic
- Use **Laravel Pint** for code formatting (`./vendor/bin/pint`)
- Avoid `dd()` or `dump()` in committed code
- Use Laravel's built-in validation — never trust raw user input


## Form Assignment 
- Chairman - C1
- Vice Chairman - C1
- Trustee - C2
- Corporate Secretary - C4
- Treasurer - C5
- Comptroller - C6
- EVP-GM - C3
- LRPs -C7

