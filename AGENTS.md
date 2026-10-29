# Project: IT Ticketing System

## Overview
Aplikasi internal buat manage laporan gangguan IT (wifi mati, printer error, CCTV rusak, dll).
Inspirasi: pengalaman Tegar waktu magang IT support — laporan gangguan masih lewat WhatsApp, jadi dibikin aplikasi ini.
Target: portfolio project Laravel, bisa dijelasin saat interview.

## Tech Stack
- Laravel 13 (framework v13.39.0)
- PHP 8.4 (install di `C:\php84`, jangan pake PHP 8.1 XAMPP)
- MySQL (via XAMPP) — fallback: SQLite bawaan Laravel 13
- Blade
- Tailwind CSS
- Laravel Breeze (authentication)
- Laravel Boost (guidelines lengkap ada di `CLAUDE.md`)
- Testing: PHPUnit (bukan Pest)

## Roles & Permissions
- **User (karyawan/pelajar):** bikin ticket, lihat & comment di ticket sendiri
- **Technician:** semua yang user bisa + lihat semua ticket, terima/assign ticket, update status, comment
- **Admin:** manage user & teknisi, manage kategori, lihat semua ticket, dashboard statistik

Gunakan Policy/Gate, jangan cek role manual di view/controller.

## MVP Scope
1. Auth (login/register) — pakai Breeze, tambah field role di users
2. CRUD ticket: create (oleh user), read (sesuai role), update status (oleh teknisi)
3. Kategori ticket (dropdown, dikelola admin)
4. Prioritas: low / medium / high
5. Status flow: `open` → `in_progress` → `resolved` → `closed`
6. Komentar di ticket (bisa dibedakan komentar publik vs internal teknisi)
7. Search & filter (by status, kategori, prioritas)
8. Dashboard admin sederhana (total ticket per status, ticket belum ditugaskan)

## Out of Scope (nanti aja, JANGAN dikerjain sekarang)
- Notifikasi email / realtime
- SLA timer
- Upload file lampiran
- Chart analytics
- API / mobile app
- Multi-tenant / multi-sekolah

## Phase 2 (setelah MVP ticketing jalan)
**Supply/Gudang request flow** — inspirasi pengalaman magang Tegar: persediaan di kantor teknisi habis, harus manual ke gudang (nunggu lama) atau keluar beli sendiri ke toko.
- Role baru: `supply` (petugas gudang/inventaris)
- Tabel `supply_requests`: `ticket_id` (nullable FK), description barang, quantity, status: `pending` → `approved` → `ready` → `picked_up` (+ `rejected`), `requested_by` (FK teknisi), `handled_by` (nullable FK petugas supply)
- Alur: teknisi bikin request dari halaman detail ticket → petugas supply acc → status `ready` → teknisi ambil barang → `picked_up`
- **JANGAN campur** inventory management penuh (stock opname, kartu stok, movement history) — itu project terpisah

## Database Design (initial)
- `users` — tambah kolom `role` (user / technician / admin)
- `categories` — name, slug
- `tickets` — title, description, category_id (FK), priority, status, user_id (requester), assigned_to (nullable FK ke users technician), timestamps
- `comments` — ticket_id (FK), user_id (FK), body, is_internal (boolean)

Semua relasi wajib pakai foreign key constraint + index di kolom yang sering difilter/di-join.

## Aturan Development
- **Validation:** semua form wajib validated server-side (Form Request)
- **Authorization:** wajib Policy, dicek di controller sebelum aksi
- **Mass assignment:** define `$fillable` dengan sadar, jangan `$guarded = []`
- **Secrets:** jangan hardcode API key/password, semua di `.env`
- **Convention over configuration:** ikuti cara Laravel. Jangan tambah Repository/Service layer kecuali memang butuh (project ini cukup Controller + Model + Policy).
- **Commit message:** conventional (`feat: add ticket status flow`, `fix: correct ticket authorization`, `docs: update README`)
- Jangan hapus/melemahkan test yang ada cuma biar pass

## Laravel Rules (dari Boost)
- Generate file baru pake `php artisan make:*` (model, migration, controller, test), jangan bikin manual dari nol. Selalu tambah `--no-interaction`.
- Setelah edit file PHP, jalankan `vendor/bin/pint --dirty --format agent` biar formatting konsisten.
- Test pake PHPUnit: `php artisan make:test {NamaFitur}Test` (tanpa prefix folder), run dengan `php artisan test --compact`.
- Guidelines lengkap Boost ada di `CLAUDE.md` — baca kalau butuh detail soal style PHP, MCP tools, atau search docs.
- Cek versi package sebelum assume API-nya: `composer show --direct`.

## Workflow
1. Sebelum implement fitur besar: breakdown jadi plan dulu, minta approval Tegar
2. Urutan per fitur: migration → model + relationship → policy → controller → view → test → commit
3. Jalankan test/lint sebelum bilang "selesai"
4. Update bagian **Status** di file ini setiap selesai milestone

## Deployment
- **Phase 1 (sekarang):** development lokal (PHP 8.4 CLI + `php artisan serve`, MySQL dari XAMPP)
- **Phase 2:** push rutin ke GitHub, public repo, README lengkap (overview, fitur, tech stack, screenshots, cara install, credentials demo)
- **Phase 3 (opsional, terakhir):** demo live via Railway atau Laravel Cloud. JANGAN pakai VPS manual — hindari ribet.

## Status
- **Phase saat ini:** Fitur 2 selesai (ticket CRUD + authorization) — siap Fitur 3
- **Done:** PHP 8.4.27 (`C:\php84`), Laravel 13.11 + Boost + Breeze, database MySQL `it_ticketing` (port **3307**), GitHub: https://github.com/compavel/it-ticketing
  - **Fitur 1 — database schema:** Enums (`UserRole`, `TicketStatus`, `TicketPriority` di root namespace `App\`), tabel `categories`/`tickets`/`comments` + kolom `role` di users, models + relationships + `#[Fillable]` attribute (Laravel 13 style), factories, `CategorySeeder` (7 kategori). 25 test pass.
  - **Fitur 2 — ticket CRUD + authorization:** `TicketPolicy` (view: requester/assignee/non-User role; update: technician/admin), `StoreTicketRequest` (server-side validation), `TicketController` (index dengan filter status/kategori/prioritas/search + pagination, create, store, show), views (`tickets/index|create|show` + components `x-ticket-status`/`x-ticket-priority`), routes `Route::resource->only(index,create,store,show)` di group auth, UserFactory states `technician()`/`admin()`. 35 test pass.
  - **Lesson learned:** factory HARUS set `role` eksplisit — kalau nggak, instance model nggak punya atribut role (default DB nggak kebaca balik), dan authorization check jadi bypass. Ditangkap test authorization.
- **Next step:** Fitur 3 — update status ticket (flow open→in_progress→resolved→closed oleh teknisi) + komentar (publik vs internal). Atau Fitur 4: dashboard admin. Minta approval plan sebelum implement.

## Catatan untuk AI
- Selalu tanya kalau requirement ambigu, jangan asumsi
- Jangan tambah fitur di luar MVP scope tanpa konfirmasi
- Jangan over-engineer — project ini portfolio, bukan produk enterprise
- Prioritaskan keamanan: auth, authorization, validation, XSS, SQL injection
- Jelaskan keputusan teknis dengan Bahasa Indonesia supaya Tegar paham alasannya
