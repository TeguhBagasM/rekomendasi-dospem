# AGENTS.md

## What this is

Laravel 12 SPK (Sistem Pendukung Keputusan) for recommending thesis advisors (dosen pembimbing). Uses TF-IDF + Cosine Similarity + Profile Matching to rank and assign dosen to student thesis proposals.

## Quick commands

```bash
composer dev          # Start all: artisan serve, queue:listen, pail, npm run dev (uses concurrently)
php artisan test     # Run Pest tests (uses in-memory SQLite)
vendor/bin/pint      # Code style fixer (Laravel Pint defaults)
npm run dev          # Vite dev server only
npm run build        # Vite production build
php artisan migrate:fresh --seed  # Reset DB with seeders
```

## Dev environment

- **PHP 8.2+**, **MySQL 8** in production, **SQLite** file-based in dev (`database/database.sqlite`)
- `.env` has MySQL (`DB_CONNECTION=mysql`, db `rekomendasi_dospem`); `.env.example` defaults to SQLite
- Tests always use in-memory SQLite (`phpunit.xml` overrides `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)
- Default admin login: `admin@gmail.com` / `111` (seeded by `DatabaseSeeder`)

## Roles & authorization

Three Spatie roles: `admin`, `dosen`, `mahasiswa`. Routes use `role:` middleware. The `User` model exposes `isAdmin()`, `isDosen()`, `isMahasiswa()`. Always check authorization explicitly — controllers abort 403 with `abort_unless`.

## Architecture notes

- **Business logic lives in Services**, not controllers. There are 17 service classes in `app/Services/`. Controllers are thin wrappers that delegate to services.
- **Recommendation pipeline** (`app/Services/RecommendationGeneratorService.php`):
  1. TF-IDF (`TfidfService`) → 2. Cosine Similarity (`CosineSimilarityService`) → 3. Profile Matching (`ProfileMatchingService`) → 4. Ranking DESC → 5. Assignment (`RecommendationAssignmentService`)
- Profile Matching: Core Factor = topic similarity (60%), Secondary Factor = education + jabatan + quota (40%)
- `TextPreprocessingService` handles Indonesian/English stopword removal, case folding, normalization
- `DocumentSimilarityService` wraps TF-IDF + Cosine for one-to-many comparison (title vs dosen documents)
- `TextSimilarityService` checks title duplication across student submissions (75% threshold), **not** used in recommendation pipeline

## Domain rules that matter

- **Dospem1** must be from program studi "Teknik Informatika" (hard constraint in `ProfileMatchingService::isEligibleForDospem1`)
- **Dospem1 jabatan rank >= Dospem2** rank: Lektor(3) > Asisten Ahli(2) > Tenaga Pengajar(1) — enforced in `RecommendationAssignmentService::pickDospem2`
- Only one `Gelombang` can be active at a time (`GelombangService` enforces this)
- Mahasiswa cannot submit if they have a pending submission (`Mahasiswa::bolehMengajukanBaru`)
- Recommendation assignment quota is tracked in-memory during batch generation, **not** written to `kuota_terpakai_*` columns until admin imports finalized results via `PenetapanDospembingImport`

## Testing

- Pest 3 with `pestphp/pest-plugin-laravel`
- Feature tests auto-apply `RefreshDatabase` (configured in `tests/Pest.php`)
- Unit tests exist in `tests/Unit/` (currently just `ExampleTest`)
- Feature tests in `tests/Feature/`: `TextSimilarityTest`, `RekomendasiFinalizeTest`, `MahasiswaPengajuanBaruTest`, plus Breeze `Auth/` and `ProfileTest`
- Test fixtures create their own data in global helper functions (e.g. `finalisasiFixtures()`, `similarityFixtures()`, `mahasiswaPengajuanFixtures()`) — they do **not** use factory seeders
- Run a single test: `vendor/bin/pest --filter="test name"`
- Run a single file: `vendor/bin/pest tests/Feature/TextSimilarityTest.php`

## Code style

- 4-space indent, LF line endings (`.editorconfig`)
- No custom Pint config — uses Laravel defaults
- No CI/CD pipeline, no pre-commit hooks configured
- No lint/typecheck commands beyond Pint

## Gotchas

- `.env` has MySQL; `.env.example` defaults to SQLite. Always verify `.env` exists before running artisan
- The `composer dev` script runs 4 processes via `npx concurrently` — requires both PHP and Node in PATH
- `DosenProfile.dokumen_tfidf` stores a pre-computed raw text document per dosen (keahlian + topik_metode + judul_penelitian) — the corpus + IDF are rebuilt fresh on **every** generate call, so no manual re-index step exists despite comments in `ProfileDosenService`
- `PenetapanDospembingImportService` is the **only** path that updates `kuota_terpakai_*` columns in database (via `consumeQuota`/`releaseQuota`)
- Views are Blade with Tailwind CSS; layout is `resources/views/layouts/app.blade.php`

### Dead / legacy code (do not build on)

- `KeahlianDosen` model + `KeahlianDosenController` are **dead code**: there is no `keahlian_dosens` migration/table, no route, and `Dosen` has no `keahlian()` relation (the controller calls it and would error)
- `kriterias` / `sub_kriterias` tables exist and are seeded by `KriteriaSeeder`, but **no service reads them** — `ProfileMatchingService` hardcodes its own weights (CF 60% / SF 40%) and scale tables. The seeder weights (TOPIK 0.35, etc.) are stale/display-only
- `KeahlianDosen::isCoreFactor()` / `tipe_faktor` is unused by the pipeline

### Behavioral quirks

- `RiwayatRekomendasi.status` enum is `['draft','exported','imported','finalized']` (migration), but code only ever writes `draft` (on generate) and `finalized` (on finalize); `exported`/`imported` labels exist only in `DashboardService::adminActivities()`
- `POST rekomendasi/generate` creates a **new** `RiwayatRekomendasi` on every click — there is no idempotency; quota is NOT consumed at generate time (only in-memory state)
- Rekomendasi routes sit behind `role:admin` middleware; `RekomendasiController::authorizeAccess()` also allows dosen but is unreachable for them via routing
- `RekomendasiController::review()`/`updateDraft()`/`finalize()`/`exportDraft()` accept `int $riwayatId`/`int $rekomendasiId` — the route param `{riwayat}` is NOT route-model-bound
- `RecommendationGeneratorService` preloads `kuota_dosen_gelombangs` via `loadKuotaGelombangMap()` (one query), but `RecommendationAssignmentService::initializeQuotaState()` re-queries the table per dosen (N+1-ish) — acceptable but inconsistent
- Dospem1 must be from program studi **exactly** "Teknik Informatika" (`ProfileMatchingService::isEligibleForDospem1` does a case-insensitive compare of the prodi name string)

## Key files for orientation

- `routes/web.php` — all route definitions
- `app/Services/RecommendationGeneratorService.php` — core orchestration
- `app/Services/ProfileMatchingService.php` — scoring algorithm with business constants
- `app/Services/RecommendationAssignmentService.php` — dosen assignment rules
- `app/Models/Dosen.php` — dosen model with quota fields
- `app/Models/PengajuanJudul.php` — thesis proposal model
- `database/migrations/2026_06_23_000001_create_rekomendasi_spk_tables.php` — SPK schema
- `database/seeders/DatabaseSeeder.php` — seeder order and default data
