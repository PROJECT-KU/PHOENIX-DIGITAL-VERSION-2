# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Phoenix Digital v2 is a Laravel 11 + Livewire 3 application: a public e-commerce storefront (shop, cart, checkout, Midtrans payment) plus a large admin back office for managing products, orders, customers, HR (karyawan/gaji/lowongan/pelamar), and company finance (spending, loans, payroll, cash flow). UI is server-rendered Blade + Bootstrap 5 (not Tailwind for admin, despite Tailwind being installed). The app language is Indonesian — model fields, routes, and UI text are mostly in Bahasa Indonesia.

## Commands

```bash
# Dev: run Vite (assets) and the PHP server in separate terminals
npm run dev                 # Vite dev server (HMR)
php artisan serve           # App at http://localhost:8000
npm run build               # Production asset build

# Tests (PHPUnit configured in phpunit.xml; Pest is also available)
php artisan test                                   # full suite
php artisan test --filter ProfileTest              # single test class
php artisan test tests/Feature/Auth                # a directory
vendor/bin/phpunit --filter test_method_name       # single method

# Code style (Laravel Pint)
vendor/bin/pint                 # format
vendor/bin/pint --test          # check only, no writes

# Common Artisan
php artisan migrate             # uses MySQL (DB_CONNECTION=mysql)
php artisan queue:work          # QUEUE/CACHE/SESSION all use the database driver
php artisan tinker
```

Docker is available (`docker-compose.yml`, `Dockerfile`) but local `artisan serve` + `npm run dev` is the normal dev loop.

## Architecture

### Livewire full-page components, not controllers
Routes map directly to Livewire component classes (`app/Livewire/Pages/...`), not controllers. `routes/web.php` is the source of truth for which component serves a URL and its route name. Each feature folder under `app/Livewire/Pages/Admin/<Feature>/` follows a `FeatureList` / `FeatureCreate` / `FeatureEdit` (sometimes `FeatureDetail`) convention, with matching Blade views under `resources/views/livewire/pages/admin/<feature>/` named `feature-list.blade.php` etc. Controllers (`app/Http/Controllers`) exist only for non-Livewire endpoints like the Midtrans payment callback and PDF preview.

The shared `Create`/`Edit` pages typically embed a single reusable `FeatureForm` Livewire component (e.g. `<livewire:pages.admin.spending.spending-form :spending-id="$id" />`) that handles both create and edit via an `$isEdit` flag set in `mount()`.

### Admin layout and styling system
Admin list/form components render into the `livewire.layout.templateindex` layout (`->layout('livewire.layout.templateindex')`). That layout file contains a large global `<style>` block defining the app's "clean glossy" design system — `.card` (glassmorphism, rounded), `.btn` gradients, `.gradient-text` headings, `.form-control` with left icon padding, `.stat-icon-wrapper` + `.bg-gradient-{purple,blue,green,red}`, and `.empty-state-icon-wrapper`. **Reuse these classes for visual consistency** rather than inventing new styles; feature-specific tweaks go in a scoped `<style>` block inside that feature's Blade file.

List pages share a common shape: a glossy header card (`gradient-text` title + `<x-breadcrumb>`), an `@include(... .partials.filter)` search/filter bar, a table, and SweetAlert2 confirm/toast scripts. Several features keep their filter UI in a `partials/filter.blade.php` next to the list view.

### Finance: polymorphic CashFlow ledger (critical)
`CashFlow` is a single polymorphic ledger (`sourceable_id` / `sourceable_type` morph) that aggregates money movement from many source models: `Order`, `Spending`, `Loan`, `Pengembalian`, `GajiKaryawans`, `PemesananRsc`. Each of those models has a `cashFlow(): MorphOne` relation.

`App\Actions\Finance\SyncCashFlowAction::execute($model, $data)` is the single entry point that keeps the ledger in sync — call it after creating/updating a financial record (see `SpendingForm::save()` for the pattern). Its private `shouldRecord()` encodes the business rule for when money is considered real per model type (e.g. `Spending` only records when `status !== 'pending'`; `Order` only when `paid`/`completed`). If `shouldRecord()` is false it deletes any existing ledger row. The `cash-flow` admin page reads from this ledger and renders income/expense/net summaries and PDF reports. When adding a new money-affecting feature, wire it through `SyncCashFlowAction` and extend `shouldRecord()`.

### Models: behavior lives in the model
Models lean on Eloquent conventions heavily:
- `booted()` hooks auto-generate human IDs (e.g. `Spending` builds `id_transaksi` like `PPA-20260621-001` and forces `penginput_id = auth()->id()` on create).
- Query `scope*` methods (`scopeByStatus`, `scopeByDateRange`, etc.) for filtering.
- `get*Attribute` accessors for display formatting (e.g. `nominal_formatted`, `tanggal_transaksi_formatted`). Date accessors use Carbon `->locale('id')->translatedFormat(...)` to force Indonesian month names because `APP_LOCALE=en` in `.env` — keep that pattern when formatting dates for display.
- Most models use `HasUuids` (string UUID primary keys), so route-model binding keys are UUIDs.
- `Order` is observed by `OrderObserver` (registered in `AppServiceProvider`); other side effects are inline in model `booted()` hooks.

### Auth & roles
Custom RBAC, not a package. `User` has `Role` / `Permission` relations with `hasAnyRole()`. Middleware aliases (registered in `bootstrap/app.php`): `checkrole:admin,admin-mimin` gates admin route groups, `permission:` gates by permission, plus custom `auth`, `IdleTimeout`, `LastUserActivity`, and `EnsureGuestToken` (guest cart token) in the web group. Admin routes are grouped by required role in `routes/web.php`.

### Payments
Midtrans Snap integration via `App\Services\PaymentService`; the gateway calls back to `PaymentCallbackController@midtrans` (`POST /payment/callback/midtrans`, excluded from CSRF). Order status transitions there feed the CashFlow ledger.

### Exports
Excel exports use `maatwebsite/excel` `FromView` classes in `app/Exports/` (e.g. `SpendingExport`), rendered from Blade templates under `resources/views/exports/`. PDF reports/invoices use `barryvdh/laravel-dompdf` from Blade views (e.g. `cash-flow/report-pdf.blade.php`).

## Conventions
- Match the surrounding feature's structure when adding a screen: `List`/`Create`/`Edit` component trio, shared `Form` component, `partials/filter.blade.php`, and the glossy layout classes.
- Indonesian naming is expected for new domain code (fields, routes, UI copy) to stay consistent with the codebase.
- Real-time bits use Laravel Echo on the front end, but `BROADCAST_CONNECTION=log` by default — broadcasting is not wired to a live driver locally.
