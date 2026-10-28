# SK Generator - Project Documentation

**Framework:** Laravel 13.32.0 | **PHP:** 8.5.10 (WinGet) | **DB:** SQLite | **Last Updated:** 2026-10-28 (Tahap 16 + 17A + 17B + 17C + 17D selesai & terverifikasi)

**Current Phase:** Tahap 4B (SK Generation) + Tahap 5 (Riwayat/History) + Tahap 6/7 (SK Custom Builder) + **Tahap 9A** (Document refinement: kop surat on first page only, Memperhatikan section, Tembusan, typo fix, "Nonaktifkan Semua"/"Hapus" buttons) + **Tahap 10A** (UI Foundation: Bootstrap 5 local install, new homepage, Bootstrap pagination, riwayat table styling) + **Tahap 11** (Layout Dokumen Produksi: layout follows "surat produksi" sekolah — font Calibri Light, margin 1,55/1,5/1,5/1,0 cm, Menimbang/Mengingat/Pertama with colon at 2,50 cm, penanda at 3,0 cm, text at 3,50 cm, tanda tangan block at 10,16 cm, new `SkLayoutBuilder` class, `jabatan_judul` field on Pejabat) + **Tahap 12** (Penomoran/Numbering: auto-incrementing nomor_urut per year, configurable format template with `{nomor}` placeholder, `penomoran_tahun` ledger table, Pengaturan Sekolah format_nomor + digit_nomor fields, `PenomoranService` service class, `PenomoranController` + `/pengaturan/penomoran` UI) + **Tahap 13** (Riwayat: hapus surat per-baris dan massal + filter pencarian lanjutan: kata kunci, jenis SK, tahun, bulan, rentang tanggal, urutkan) + **Tahap 15** (opsi 'Lainnya (isi manual)…' pada setiap dropdown select form SK + seeder patch hapus opsi 'Lainnya' lama dari Alasan Pemberhentian) + **Tahap 16** (Launcher: start.bat/stop.bat/buat-shortcut.bat + launcher/cek-awal.php — pemeriksaan kesiapan otomatis, port selection 8000-8010, auto-migrate dengan backup, storage:link, cek LibreOffice/font Calibri Light) + **Tahap 17A** (PHP portabel untuk peluncur: launcher/siapkan-php.ps1 baru, start.bat memakai runtime\php\php.exe lewat path absolut + PHPRC, .gitignore /runtime/, dokumentasi) + **Tahap 17B** (Penyiapan otomatis aplikasi: launcher/siapkan-aplikasi.php, .env, vendor, database, storage:link) + **Tahap 17C** (LibreOffice + Visual C++ otomatis: siapkan-libreoffice.ps1 baru, siapkan-php.ps1 diperbarui dengan Install-VcRedist + perbaikan bug null) + **Tahap 17D** (Pemasangan dan Pembaruan dari GitHub: launcher/perbarui.ps1 baru, update.bat baru, pasang.bat baru)

**Git State:** 4 commits on master: 59fe357 (Tahap 1) + dfb412f (Tahap 2-7) + 7f34841 (Tahap 8 docs update) + c51718a (Tahap 15 seeder restore-point). All development from Tahap 9A through Tahap 14 (plus the blade & generator code of Tahap 15) remains in the working tree as untracked/uncommitted files; hanya seeder Tahap 15 saja yang ter-commit (c51718a, restore-point). Tahap 16 (launcher files: start.bat, stop.bat, buat-shortcut.bat, launcher/cek-awal.php) juga baru ditambahkan di working tree. Lihat Section 16 & 17A. Tahap 17A (launcher/siapkan-php.ps1 baru, start.bat dimodifikasi untuk pakai runtime\php\php.exe, .gitignore /runtime/, PROJECT_DOCUMENTATION.md) ditambahkan di working tree, belum ter-commit. Tahap 17B (launcher/siapkan-aplikasi.php, PROJECT_DOCUMENTATION.md) dan Tahap 17C (launcher/siapkan-libreoffice.ps1 baru, siapkan-php.ps1 + start.bat diperbarui, PROJECT_DOCUMENTATION.md) juga ditambahkan di working tree. Tahap 17D (launcher/perbarui.ps1 baru, update.bat baru, pasang.bat baru, PROJECT_DOCUMENTATION.md) juga ditambahkan di working tree.

---

## 1. Environment Setup

### PHP 8.5.10 (required)
- Executable: C:\Users\ricii\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.5_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe
- php.ini: Inside the WinGet PHP 8.5 package directory. Extensions configured without .dll suffix.
- Extensions loaded: pdo_sqlite, mbstring, openssl, fileinfo, curl, gd, bcmath, ctype, dom, hash, json, libxml, pcre, PDO, Phar, session, SPL, tokenizer, xml, Zend OPcache.
- Old PHP 8.1.10 (Laragon, incompatible): C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe

### LibreOffice (DOCX to PDF)
- Binary: C:\Program Files\LibreOffice\program\soffice.exe
- Env var: LIBREOFFICE_BINARY in .env and .env.example (FIXED in Tahap 8 — both files now include `LIBREOFFICE_BINARY="C:\Program Files\LibreOffice\program\soffice.exe"`)

### Database
- Type: SQLite | File: database/database.sqlite | DB_CONNECTION=sqlite
- Storage symlink: public/storage -> storage/app/public (already linked)

### Composer & Node
- Composer: PHP dependency manager
- Node.js: package.json present with Vite 8 + TailwindCSS 4
- npm scripts: npm run build (prod), npm run dev (Vite dev server)

---

## 2. Key Paths

| Resource | Path |
|----------|------|
| Project root | C:\Users\ricii\smk-sk-generator |
| PHP 8.5 | ...\WinGet\Packages\PHP.PHP.8.5_...\php.exe |
| Database | database/database.sqlite |
| Uploaded images | storage/app/public/letterhead/ |
| Generated DOCX/PDF | storage/app/public/sk-output/ |
| Seeders | database/seeders/ |
| Tests | tests/Feature/, tests/Unit/ |
| Vite assets | resources/css/app.css, resources/js/app.js |

---
## 3. Project Structure (Key Files)

```
C:\Users\ricii\smk-sk-generator\
  .env / .env.example                  # Environment (env missing LIBREOFFICE_BINARY)
  artisan, bootstrap/app.php, composer.json/.lock
  package.json, vite.config.js (Vite 8 + TailwindCSS 4)
    phpunit.xml, AGENTS.md, CLAUDE.md, README.md
  routes/web.php (31 routes+ incl Boost/Storage/Up infrastructure + 3 penomoran) / console.php
  public/vendor/bootstrap/          # Bootstrap 5.3.3 CSS + JS bundle (Tahap 10A, LOCAL)
  app/
    Console/Commands/TestPipeline.php
    Http/Controllers/
      Controller.php (base, abstract)
      KopSuratController.php  # Kop surat upload/activation
      PejabatController.php   # Pejabat CRUD + activation (UPDATED - Tahap 11: handles jabatan_judul field)
      JenisSkController.php   # Jenis SK list/edit/update
      SkController.php        # SK form + generation (Tahap 4B)
      RiwayatController.php   # SK history list (NEW - Tahap 5)
      SkCustomController.php  # Custom SK builder (NEW - Tahap 6/7)
      PenomoranController.php  # Penomoran/Numbering config UI (NEW - Tahap 12)
      PengaturanSekolahController.php  # School settings page (NEW - Tahap 9B)
      HomeController.php               # Homepage with system readiness panel, SK cards (NEW - Tahap 10A)
    Services/SkDocumentGenerator.php  # Core DOCX/PDF generation (updated Tahap 9B: uses PengaturanSekolah; Tahap 11: delegates layout to SkLayoutBuilder)
    Services/SkLayoutBuilder.php     # NEW - Tahap 11: Pure layout class (no DB knowledge), builds PhpWord per skLayoutBuilder rules
    Services/PenomoranService.php          # NEW - Tahap 12: Year ledger, next-number lookup, format template application
    Models/{JenisSk, LetterheadAsset, PejabatPenandatangan, PengaturanSekolah, SkTerbit, PenomoranTahun, User}
  database/
    database.sqlite
    factories/UserFactory.php
    migrations/ (3 default Laravel + 10 app-specific = 13 total)
      0001_01_01_000000_create_users_table.php
      0001_01_01_000001_create_cache_table.php
      0001_01_01_000002_create_jobs_table.php
      2026_09_15_213437_create_letterhead_assets_table.php
      2026_09_15_213440_create_pejabat_penandatangan_table.php
      2026_09_17_001904_create_jenis_sk_table.php
      2026_09_18_003447_add_template_columns_to_jenis_sk_table.php
      2026_09_18_003449_create_sk_terbit_table.php
      2026_09_29_025945_add_memperhatikan_column_to_jenis_sk_table.php
      2026_09_30_113725_create_pengaturan_sekolah_table.php  # NEW - Tahap 9B
      2026_10_15_003138_add_jabatan_judul_to_pejabat_penandatangan_table.php  # NEW - Tahap 11: jabatan_judul field
      2026_10_15_051022_add_nomor_urut_to_sk_terbit_table.php              # NEW - Tahap 12: nomor_urut on sk_terbit
      2026_10_15_051023_create_penomoran_tahun_table.php                   # NEW - Tahap 12: per-year numbering ledger
      2026_10_15_051024_add_format_nomor_to_pengaturan_sekolah_table.php   # NEW - Tahap 12: format_nomor + digit_nomor
    seeders/
      DatabaseSeeder.php             # Does NOT call JenisSkSeeder
      JenisSkSeeder.php              # Seeds 12 standard jenis SK entries
      Tahap9aPatchSeeder.php         # Tahap 9A: Memperhatikan draft + typo fix
      Tahap9bPatchSeeder.php         # NEW - Tahap 9B: Memperhatikan draft for 10 remaining jenis SK
  resources/views/
    layouts/app.blade.php            # Bootstrap 5 navbar layout (Beranda, Riwayat, SK Custom, Pengaturan dropdown) (UPDATED - Tahap 10A)
    welcome.blade.php                # Default Laravel welcome (no longer used)
    home.blade.php                   # Homepage: system readiness panel, SK cards, recent SK table (NEW - Tahap 10A)
    pengaturan/
      index.blade.php                # Settings hub
      kop-surat/index.blade.php      # Upload form + history table
      pejabat/{_form,index,create,edit}.blade.php
      jenis-sk/{index,edit}.blade.php  # edit.blade.php updated Tahap 9B: 4 template textareas
      sekolah/edit.blade.php           # NEW - Tahap 9B: Pengaturan Sekolah form
      penomoran/edit.blade.php            # NEW - Tahap 12: Penomoran/Numbering config form
    sk/
      create.blade.php               # Dynamic form from field_schema
      hasil.blade.php                # Success page (download DOCX/PDF)
      hasil-uji.blade.php            # Debug view (DELETED in Tahap 8)
    sk-custom/                       # NEW - Tahap 6/7
      create.blade.php               # Block builder (paragraf + tabel, JS)
      hasil.blade.php                # Success page
      hasil-uji.blade.php            # Debug view (DELETED in Tahap 8)
    riwayat/
      index.blade.php                # NEW - Tahap 5 (history list with filters + Bootstrap table styling - UPDATED Tahap 10A)
  storage/app/public/{letterhead/, sk-output/}
  tests/
    TestCase.php
    Feature/ExampleTest.php
    Unit/ExampleTest.php
  config/                            # Laravel config files
  resources/css/app.css              # TailwindCSS entrypoint
  resources/js/app.js                # JS entrypoint (minimal)
```

---

## 4. Routes (31 total: 1 root + 1 + 5 kop-surat + 7 pejabat + 3 jenis-sk + 2 SK + 1 riwayat + 2 SK-custom + 2 sekolah + 3 penomoran + 4 infrastructure)

| # | Method | URI | Name | Controller |
|---|--------|-----|------|------------|
| 1 | GET | `/` | `home` | HomeController@index **(UPDATED - Tahap 10A)** |
| 2 | GET | `/pengaturan` | `pengaturan.index` | Closure (view) |
| 3 | GET | `/pengaturan/kop-surat` | `pengaturan.kop-surat.index` | KopSurat::index |
| 4 | POST | `/pengaturan/kop-surat` | `pengaturan.kop-surat.store` | KopSurat::store |
| 5 | POST | `/pengaturan/kop-surat/{letterhead}/aktifkan` | `pengaturan.kop-surat.aktifkan` | KopSurat::aktifkan |
| 6 | POST | `/pengaturan/kop-surat/nonaktifkan-semua` | `pengaturan.kop-surat.nonaktifkan-semua` | KopSurat::nonaktifkanSemua |
| 7 | DELETE | `/pengaturan/kop-surat/{letterhead}` | `pengaturan.kop-surat.destroy` | KopSurat::destroy |
| 8 | GET | `/pengaturan/pejabat` | `pengaturan.pejabat.index` | Pejabat::index |
| 9 | GET | `/pengaturan/pejabat/create` | `pengaturan.pejabat.create` | Pejabat::create |
| 10 | POST | `/pengaturan/pejabat` | `pengaturan.pejabat.store` | Pejabat::store |
| 11 | GET | `/pengaturan/pejabat/{pejabat}/edit` | `pengaturan.pejabat.edit` | Pejabat::edit |
| 12 | PUT | `/pengaturan/pejabat/{pejabat}` | `pengaturan.pejabat.update` | Pejabat::update |
| 13 | DELETE | `/pengaturan/pejabat/{pejabat}` | `pengaturan.pejabat.destroy` | Pejabat::destroy |
| 14 | POST | `/pengaturan/pejabat/{pejabat}/aktifkan` | `pengaturan.pejabat.aktifkan` | Pejabat::aktifkan |
| 15 | GET | `/pengaturan/jenis-sk` | `pengaturan.jenis-sk.index` | JenisSk::index |
| 16 | GET | `/pengaturan/jenis-sk/{jenisSk}/edit` | `pengaturan.jenis-sk.edit` | JenisSk::edit |
| 17 | PUT | `/pengaturan/jenis-sk/{jenisSk}` | `pengaturan.jenis-sk.update` | JenisSk::update |
| 18 | GET | `/sk/{jenisSk:kode}/create` | `sk.create` | SkController::create |
| 19 | POST | `/sk/{jenisSk:kode}` | `sk.store` | SkController::store |
| 20 | GET | `/riwayat` | `riwayat.index` | RiwayatController::index **(NEW - Tahap 5)** |
| 21 | GET | `/sk-custom/create` | `sk-custom.create` | SkCustomController::create **(NEW - Tahap 6/7)** |
| 22 | POST | `/sk-custom` | `sk-custom.store` | SkCustomController::store **(NEW - Tahap 6/7)** |
| 23 | GET | `/pengaturan/sekolah` | `pengaturan.sekolah.edit` | PengaturanSekolahController::edit **(NEW - Tahap 9B)** |
| 24 | PUT | `/pengaturan/sekolah` | `pengaturan.sekolah.update` | PengaturanSekolahController::update **(NEW - Tahap 9B)** |
| 25 | GET | `/pengaturan/penomoran` | `pengaturan.penomoran.edit` | PenomoranController::edit **(NEW - Tahap 12)** |
| 26 | PUT | `/pengaturan/penomoran/format` | `pengaturan.penomoran.format` | PenomoranController::simpanFormat **(NEW - Tahap 12)** |
| 27 | POST | `/pengaturan/penomoran/atur` | `pengaturan.penomoran.atur` | PenomoranController::atur **(NEW - Tahap 12)** |

**Notes:**
- Route 1: `/` now uses HomeController (named `home`) with system readiness panel, SK cards, and recent SK table. No longer returns welcome.blade.php. (Tahap 10A)
- Routes 3-7: Kop Surat routes — index (upload form + history), store (validate image), aktifkan, nonaktifkan-semua, destroy.
- Routes 18-19: SK generation from predefined jenis_sk templates (Tahap 4B). Route model binding by `kode`.
- Route 20: Riwayat (history) page with search + filter by jenis_sk_id.
- Routes 21-22: SK Custom builder - compose SK from free-form paragraf + tabel blocks.
- Nav in app.blade.php has links: Beranda, Riwayat SK, SK Custom, and Pengaturan dropdown (Pengaturan Sekolah, Kop Surat, Pejabat, Jenis SK). (UPDATED - Tahap 10A)

---

## 5. Database Schema

### letterhead_assets
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| file_path | varchar(255) | e.g. letterhead/abc.jpg |
| is_active | boolean | only one true at a time |
| catatan | varchar(255) | nullable |
| created_at/updated_at | timestamp | timestamps |

### pejabat_penandatangan
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| nama_lengkap | varchar(255) | with title |
| jabatan | varchar(255) | e.g. Kepala Sekolah |
| label_id | varchar(50) | e.g. NUKS, NIP |
| nomor_id | varchar(100) | ID number |
| is_active | boolean | only one true at a time |
| created_at/updated_at | timestamp | timestamps |

### jenis_sk
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| kode | varchar(255) | unique - e.g. PENGANGKATAN, PEMBENTUKAN_TIM |
| nama | varchar(255) | display name |
| kode_klasifikasi | varchar(50) | nullable - e.g. 800, 421, 400 |
| field_schema | json | array of field definitions |
| is_custom | boolean | default false - true for CUSTOM kode |
| template_menimbang | text | nullable (added by migration 003447) |
| template_mengingat | text | nullable (added by migration 003447) |
| template_diktum | text | nullable (added by migration 003447) |
| template_memperhatikan | text | nullable (added by migration 025945, filled by Tahap9bPatchSeeder) |
| created_at/updated_at | timestamp | timestamps |

### sk_terbit
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| jenis_sk_id | bigint FK | references jenis_sk.id |
| nomor_surat | varchar(255) | UNIQUE constraint |
| nomor_urut | unsignedInteger | nullable (Tahap 12 - per-year sequence)|
| tanggal_sk | date | date of SK issuance |
| data_isian | json | all form data submitted by user |
| file_docx | varchar(255) | nullable - path relative to storage/app/public/ |
| file_pdf | varchar(255) | nullable - path relative to storage/app/public/ |
| created_at/updated_at | timestamp | timestamps |

### pengaturan_sekolah
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| nama_sekolah | varchar(255) | default 'Nama Sekolah' |
| kota | varchar(100) | default 'Banjarmasin' |
| singkatan_nomor | varchar(50) | default 'SMK' |
| tembusan_default | text | nullable |
| format_nomor | varchar(255) | default '{klasifikasi}/KEP.{nomor}–SMKM.3/DISDIKBUD/{bulan}/{tahun}' (NEW - Tahap 12) |
| digit_nomor | unsignedTinyInteger | default 3, validated 1-6 (NEW - Tahap 12) |
| created_at/updated_at | timestamp | timestamps |

### penomoran_tahun
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| tahun | integer | unique - e.g. 2026 |
| nomor_mulai | integer | default 1 - starting number for the year |
| reset_at | datetime | nullable - if set, sequence counts from this date |
| created_at/updated_at | timestamp | timestamps |

### Default Laravel tables
users, cache, jobs tables created by default migrations 0001_01_01_*.

---

## 6. Models

- **LetterheadAsset** - table `letterhead_assets`. Fillable: file_path, is_active, catatan. Static `aktif()` returns first active.

- **PejabatPenandatangan** - table `pejabat_penandatangan` (explicit). Fillable: nama_lengkap, jabatan, label_id, nomor_id, is_active. Static `aktif()` returns first active.

- **JenisSk** - table `jenis_sk` (explicit). Fillable: kode, nama, kode_klasifikasi, field_schema, is_custom, template_menimbang, template_mengingat, template_memperhatikan, template_diktum. (Added template_memperhatikan in Tahap 9A) Casts: field_schema => array, is_custom => boolean. Seeded with 12 standard entries via JenisSkSeeder. The 12 entries:

| # | Kode | Nama | Kode Klasifikasi |
|---|------|------|-----------------|
| 1 | PENGANGKATAN | SK Pengangkatan | 800 |
| 2 | PEMBENTUKAN_TIM | SK Pembentukan Tim / Panitia | 421 |
| 3 | PEMBERHENTIAN | SK Pemberhentian | 800 |
| 4 | PENUGASAN_MUTASI | SK Penugasan / Mutasi | 800 |
| 5 | KEBIJAKAN_PERATURAN | SK Kebijakan / Peraturan | 400 |
| 6 | PERUBAHAN | SK Perubahan | 000 |
| 7 | DELEGASI_WEWENANG | SK Delegasi Wewenang | 000 |
| 8 | PENGHAPUSAN_ASET | SK Penghapusan Aset | 028 |
| 9 | ZONA_INTEGRITAS | SK Penetapan Zona Integritas | 061 |
| 10 | PENGHARGAAN_SANKSI | SK Penghargaan / Sanksi | 800 |
| 11 | PENGELOLA_LAYANAN | SK Pengelola Layanan | 065 |
| 12 | SOP_LAYANAN | SK Standar Operasional Layanan | 065 |

- **SkTerbit** (NEW - Tahap 4B) - table `sk_terbit`. Fillable: jenis_sk_id, nomor_surat, nomor_urut, tanggal_sk, data_isian, file_docx, file_pdf. (Added `nomor_urut` in Tahap 12) Casts: data_isian => array, tanggal_sk => date, nomor_urut => integer. Relationship: `jenisSk()` belongsTo(JenisSk).

- **PengaturanSekolah** (NEW - Tahap 9B) - table `pengaturan_sekolah`. Fillable: nama_sekolah, kota, singkatan_nomor, tembusan_default, format_nomor (Tahap 12), digit_nomor (Tahap 12). Static `ambil()` returns the singleton row (creates with defaults if none exists).

- **PenomoranTahun** (NEW - Tahap 12) - table `penomoran_tahun`. Fillable: tahun, nomor_mulai, reset_at. Casts: reset_at => datetime. No static methods on the model - year lookup via PenomoranService::ambilTahun() which uses firstOrCreate.

- **User** (default Laravel) - table `users`. Uses HasFactory + Notifiable. Fillable: name, email, password. Casts: email_verified_at => datetime, password => hashed.

---

## 7. Controllers

- **Controller** (base) - abstract class, empty body. All other controllers extend this.

- **KopSuratController** - `index()` (upload form + history table), `store()` (validate image: required|image|mimes:jpg,jpeg,png|max:2048, stores via store('letterhead', 'public')), `aktifkan()` (deactivates all active, activates one).

- **PejabatController** - Full CRUD: `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`, plus `aktifkan()`. Validation: nama_lengkap (required|string|max:255), jabatan (required|string|max:255), label_id (nullable|string|max:50), nomor_id (nullable|string|max:100).

- **JenisSkController** (Tahap 9B) - `index()` (lists 12 jenis SK with field_schema details, jumlah field count, edit link, and test link), `edit()` (form to edit kode_klasifikasi, template_menimbang, template_mengingat, template_memperhatikan, template_diktum), `update()` (validates all template fields as nullable|string).

- **PengaturanSekolahController** (NEW - Tahap 9B) - `edit()` renders `pengaturan.sekolah.edit` view with PengaturanSekolah::ambil() data. `update(Request $request)` validates nama_sekolah (required|string|max:255), kota (required|string|max:100), singkatan_nomor (required|string|max:50), tembusan_default (nullable|string). Updates and redirects with status message.

- **SkController** (Tahap 4B) - `create(JenisSk $jenisSk)` renders `sk.create` view with dynamic form fields from field_schema (supports text, number, date, textarea, select, table types). `store(Request $request, JenisSk $jenisSk, SkDocumentGenerator $generator)` extracts form data via `$request->except('_token')`, calls `$generator->generate()`, catches exceptions with `back()->withErrors()`, returns `sk.hasil` view.

- **RiwayatController** (NEW - Tahap 5) - `index(Request $request)` queries `SkTerbit::with('jenisSk')->orderByDesc('created_at')`, supports filtering by `jenis_sk_id` and searching `nomor_surat` with LIKE, paginates 15 per page with `->withQueryString()`. Renders `riwayat.index` with daftar (paginated) and jenisSkList.

- **PenomoranController** (NEW - Tahap 12) - Numbering configuration UI. `edit()` shows current year ledger, next-number preview, and a live format-template sample; `simpanFormat()` validates and saves `format_nomor` + `digit_nomor` to `pengaturan_sekolah`; `atur()` sets the starting `nomor_mulai` for a given year. Uses `PenomoranService` via DI. Routes: `pengaturan.penomoran.edit`, `pengaturan.penomoran.format`, `pengaturan.penomoran.atur`.
- **SkCustomController** (NEW - Tahap 6/7) - `create()` renders `sk-custom.create` (JavaScript block builder UI: add/remove/move paragraf + tabel blocks, each with label, content/kolom/baris). `store(Request $request, SkDocumentGenerator $generator)` extracts `judul_sk` and `blocks_json` (JSON string), auto-creates JenisSk with kode='CUSTOM', kode_klasifikasi='000', is_custom=true, calls `$generator->generateCustom($jenisSk, $judul, $blocks)`, catches exceptions, renders `sk-custom.hasil` view.

---

## 8. Service: SkDocumentGenerator (app/Services/SkDocumentGenerator.php)

Core service: prepares data spec, delegates layout to SkLayoutBuilder, converts DOCX to PDF via LibreOffice, persists SkTerbit record.

### Public methods

**`generate(JenisSk $jenisSk, array $data, array $opsi = []): SkTerbit`** (Tahap 4B, refactored Tahap 11; **Tahap 14: per-surat opsi kop/penandatangan/tanggal**)
1. Resolves per-surat opsi via `siapkanOpsi($opsi)` (Tahap 14): validates `tanggal` (default today, throws on invalid), and resolves `kop` (aktif/tanpa/kosong/id) + `penandatangan` (aktif/manual/id).
2. Loads `PengaturanSekolah::ambil()` for kota, singkatan_nomor, tembusan_default.
3. Calls `app(PenomoranService::class)->buat($jenisSk, $tanggal)` (Tahap 12) to get `[$nomorUrut, $nomorSurat]`. The `$nomorUrut` is passed to `simpanDanKonversi()` for persistence on SkTerbit.
4. Builds a `$spec` array containing: `kop_path`, `jabatan_judul` (falls back to `jabatan`), `nomor`, `kota`, `tanggal`, `penandatangan` (nama, jabatan, label_id, nomor_id), `tembusan`, plus mode-specific data (`judul`, `menimbang`, `mengingat`, `memperhatikan`, `diktum`, `tabel`).
5. Delegates layout: `$phpWord = (new SkLayoutBuilder)->build($spec)` — SkLayoutBuilder handles all PhpWord document structure (Calibri Light font, margins, blok positions, kop on first page only, table header repeat, no empty trailing page).
6. Calls `simpanDanKonversi()`.

**`generateCustom(JenisSk $jenisSk, string $judul, array $blocks, array $opsi = []): SkTerbit`** (NEW - Tahap 6/7, refactored Tahap 11; **Tahap 14: opsi kop/penandatangan/tanggal**)
Same spec/letterhead/header structure, but instead of `menimbang`/`mengingat`/`diktum`/`tabel`, passes `mode='custom'` and `blok=$blocks`. SkLayoutBuilder renders each block: paragraf (label + isi) or tabel (kolom + baris).

### Private helper methods
The class contains exactly 11 methods (Tahap 14 adds `siapkanOpsi` and `catatanOpsi`):
- **`specDasar(array $siap, PengaturanSekolah $pengaturan, string $nomorSurat): array`** - Builds the shared `$spec` array (kop_path, kop_ruang, jabatan_judul, nomor, kota, tanggal, penandatangan, tembusan) from the resolved opsi `$siap`.
- **`siapkanOpsi(array $opsi): array`** (NEW - Tahap 14, private) - Translates form "opsi" into usable data: validates `tanggal` (Carbon, throws on invalid), resolves `kop` (id/aktif/tanpa/kosong) with path, and resolves `penandatangan` (id/aktif/manual with `jabatan_judul` fallback). Returns `tanggal`, `kop_mode`, `kop_path`, `kop_id`, and `pejabat` map.
- **`catatanOpsi(array $siap): array`** (NEW - Tahap 14, private) - Returns the SkTerbit persistence map (`kop_mode`, `letterhead_asset_id`, `penandatangan_id`/`nama`/`jabatan`) so per-surat choices are stored.
- **`barisTemplate(?string $template, array $data): array`** - Splits template by newlines, calls `isiTemplate()` per line, returns array of strings.
- **`diktumDariTemplate(?string $template, array $data): array`** - Splits template by newlines, parses each line: if contains ":", split into [label, isi]; else label=''. Returns array of `['label' => ..., 'teks' => ...]`.
- **`tabelDariField(array $fieldSchema, array $data): array`** - Iterates field_schema for type='table' entries with data present; returns array of `['judul' => null, 'kolom' => ..., 'baris' => ...]`.
- **`simpanDanKonversi(PhpWord, string $kodeUntukNamaFile, array $dataSkTerbit): SkTerbit`** - Saves DOCX as `SK-{kode}-{time()}.docx`, converts to PDF via LibreOffice (`Process::fromShellCommandline()` + `setEnv(getenv())` + `set_time_limit(0)`). Throws detailed Exception on failure. Creates SkTerbit record. **UNCHANGED from Tahap 9A.**
- **`formatTanggalIsian(array $fieldSchema, array $data): array`** - NEW (Tahap 12). Iterates field_schema for `type='date'` entries, parses via `Carbon::parse()`, reformats to Indonesian locale (`d F Y`). Invalid dates left unchanged.
- **`isiTemplate(?string $template, array $data): string`** - Replaces `{key}` placeholders with `str_replace`. Flattens newlines to spaces (Tahap 11 fix). Returns '-' if null/empty.

### SkLayoutBuilder (app/Services/SkLayoutBuilder.php) (NEW - Tahap 11)

Pure layout class with NO database/Laravel knowledge. Receives a `$spec` array and returns a `PhpWord` object.

**Key constants:**
- Font: `Calibri Light` — judul 12pt, isi 10pt, jeda 11pt
- Page: 11907 × 16840 twips (A4), margins: atas 879 (1,55cm), bawah 567 (1,0cm), kiri&kanan 851 (1,5cm)
- Text area width (LEBAR): 10205 twips
- Column positions: KOL_LABEL 1418 (2,50cm), KOL_TITIK2 283 (0,50cm), KOL_NOMOR 284 (0,50cm), INDENT_TTD 5760 (10,16cm)

**Methods:**
- `build(array $spec): PhpWord` - Main entry; creates PhpWord, sets defaults, adds section, calls kop (if path), blokJudul, content (isiStandar or isiCustom), blokTandaTangan, blokTembusan
- `kop(string $imagePath): void` - Adds letterhead image (only first page via regular section content, NOT Word Header)
- `blokJudul(array $spec): void` - Writes "SURAT KEPUTUSAN", jabatan_judul (bold, 12pt), "Nomor : {nomor}", "Tentang" + judul (bold)
- `isiStandar(array $spec): void` - Renders menimbang/mengingat/memperhatikan blocks (via `blokMultiPoin`), MEMUTUSKAN, diktum (via `blokDiktum`), tabel (via `tabelBergaris`)
- `isiCustom(array $bloks): void` - Renders custom blocks: paragraf (via `blokBerlabel` if label set, else plain text) or tabel (via `tabelBergaris`)
- `blokTandaTangan(array $spec): void` - Right-aligned at 10,16cm: "Ditetapkan di : {kota}", "Pada tanggal : {tanggal}", jabatan, 2 blank lines, nama, "NUKS/ID: {label_id} : {nomor_id}"
- `blokTembusan(array $tembusan): void` - Numbered list: "1. ...", "2. ..."
- `blokMultiPoin(string $label, array $baris): void` - 4-cell table for Menimbang/Mengingat: label, ":", penanda (a./1.), isi (rata kiri-kanan)
- `blokDiktum(array $diktum): void` - Same 4-cell layout for each diktum entry
- `blokBerlabel(string $label, string $teks): void` - 3-cell table: label, ":", isi (for custom paragraf)
- `tabelBergaris(array $kolom, array $baris): void` - Table with visible borders, bold header row (repeats on new page), "No" column sempit (567 twips/1cm)

---

## 9. TestPipeline Command (php artisan test:pipeline)

Generates a test DOCX from active letterhead + signer, converts to PDF via LibreOffice.
- Uses `Symfony\Component\Process\Process` with array constructor (CLI-only, works because CLI SAPI sets max_execution_time=0). SkDocumentGenerator now uses Process::fromShellCommandline() with setEnv(getenv()) + set_time_limit(0) for web server compatibility (Tahap 9A).
- Output: storage/app/public/sk-output/test.docx and test.pdf
- Checks $process->isSuccessful() and file_exists(pdfPath) on failure
- Error handling: reports $process->getErrorOutput() on failure
- **Note:** Line 57 has a minor indentation issue (cosmetic only)
- Run after setting up active letterhead + pejabat

---

## 10. Tahap Implementation Notes

### Tahap 1: Foundation
Laravel project init, SQLite DB, migrations for letterhead_assets + pejabat_penandatangan, TestPipeline command for DOCX+PDF pipeline.

### Tahap 2: Pipeline Verification
TestPipeline command confirmed DOCX generation with letterhead header and PDF conversion via LibreOffice.

### Tahap 3: Jenis SK Management
JenisSk model + migration (2026_09_17) with field_schema JSON. JenisSkSeeder seeds 12 standard entries with kode, nama, kode_klasifikasi, and field_schema. JenisSkController for listing/editing kode_klasifikasi.

### Tahap 4A: SK Form Generation
`sk.create` view dynamically renders form fields from field_schema (text, number, date, textarea, select, table types). `sk.hasil` view shows download links.

### Tahap 4B: SK Document Generation
SkDocumentGenerator service: `generate()` method creates full DOCX document (kop surat, header, menimbang, mengingat, memutuskan, diktum, table fields, TTD) and converts to PDF. `sk_terbit` table created with unique nomor_surat constraint. SkTerbit model with jenisSk relationship. SkController routes: `/sk/{jenisSk:kode}/create` and `/sk/{jenisSk:kode}`.

### Tahap 5: Riwayat (History)
RiwayatController + `riwayat.index` view. Lists all generated SKs with nomor_surat, jenis, tanggal, data_isian (expandable), download links. Supports filter by jenis_sk_id and search by nomor_surat. Pagination 15/page.

### Tahap 6/7: SK Custom Builder
SkCustomController + `sk-custom.create` view with JavaScript block builder UI. Users compose SK from paragraf (label + isi) and tabel (kolom + baris) blocks. `generateCustom()` method handles the custom blocks. Custom SK uses kode='CUSTOM', kode_klasifikasi='000', is_custom=true in jenis_sk table (auto-created on first use).

---

### Tahap 9A: Document Refinement
Updated SkDocumentGenerator.php: kop surat as regular content (not Word Header) so it only appears on first page, each Menimbang/Mengingat sub-poin on its own line via `tambahBlokMultiPoin()`, added conditional Memperhatikan section, added conditional Tembusan section (now reads from PengaturanSekolah), fixed typo "apabili"->"apabila" via Tahap9aPatchSeeder, added Nonaktifkan Semua/Hapus buttons in KopSurat UI.

**Tahap 9A: LibreOffice Web Context Fix** - Fixed LibreOffice PDF conversion in web context.
Fixed LibreOffice PDF conversion failure in `php artisan serve` context by switching to `Process::fromShellCommandline()` with `setEnv(getenv())` and `set_time_limit(0)`.

### Tahap 9B: Pengaturan Sekolah + Editable Redaksi
Added `pengaturan_sekolah` table (migration `2026_09_30_113725`) with columns: `nama_sekolah` (default 'Nama Sekolah'), `kota` (default 'Banjarmasin'), `singkatan_nomor` (default 'SMK'), `tembusan_default` (nullable). Added model `PengaturanSekolah` with static `ambil()` method. Added controller `PengaturanSekolahController` with `edit()`/`update()` routes. Added view `pengaturan/sekolah/edit.blade.php`. Updated `SkDocumentGenerator.php`: removed `TEMBUSAN_DEFAULT` constant; `kota`, `singkatan_nomor`, and `tembusan_default` now read from `PengaturanSekolah::ambil()` via `generate()` and `generateCustom()`. Method signatures updated: `buatNomorSurat()` now accepts `$singkatan`, `tambahBlokTtd()` now accepts `$kota`, `tambahTembusan()` now accepts `$tembusanTeks`. Updated `JenisSkController::update()` to accept template fields. Updated `jenis-sk/edit.blade.php` with 4 template textareas + placeholder cheat-sheet. Added `Tahap9bPatchSeeder` to fill `template_memperhatikan` for 10 remaining jenis SK. Nav link "Pengaturan Sekolah" added to layout.

### Tahap 10A: UI Foundation (Bootstrap 5 + Homepage + Riwayat)
- **Bootstrap 5.3.3 installed locally**: Downloaded `bootstrap.min.css` (~227 KB) and `bootstrap.bundle.min.js` (~79 KB) to `public/vendor/bootstrap/`. Application loads Bootstrap from local files, not CDN — works offline.
- **Bootstrap pagination activated**: `AppServiceProvider::boot()` now calls `Paginator::useBootstrapFive()`. Previously paginasi panah di `/riwayat` tampil raksasa karena Laravel defaultnya pakai Tailwind (yang tidak terpasang). Sekarang pakai kelas Bootstrap 5 (`page-item`, `page-link`) dengan panah normal (`&lsaquo;`/`&rsaquo;`).
- **Navbar Bootstrap baru**: `layouts/app.blade.php` diganti penuh — navbar gelap responsive dengan brand "SK Generator", link Beranda/Riwayat SK/SK Custom, dan dropdown "Pengaturan" (4 link: Pengaturan Sekolah, Kop Surat, Pejabat, Jenis SK). Layout ini juga menampilkan otomatis `alert alert-success` untuk session status dan `alert alert-danger` untuk error pertama di semua halaman.
- **HomeController baru**: `app/Http/Controllers/HomeController.php` — query `JenisSk` (12 jenis non-CUSTOM), 5 SK terbaru (`SkTerbit`), dan paket kesiapan sistem (kop surat aktif, pejabat aktif, LibreOffice terdeteksi).
- **`home.blade.php` baru**: Panel kesiapan (3 kolom dengan link "atur di sini" jika belum aktif), kartu 12 jenis SK + 1 kartu SK Custom, tabel SK terbaru, link ke riwayat.
- **Route `/` diperbarui**: Dari closure `view('welcome')` → `Route::get('/', [HomeController::class, 'index'])->name('home')`. File `welcome.blade.php` tidak dihapus (tidak dipakai lagi).
- **Error blocks duplikat dihapus**: Blok `@if ($errors->any())` dihapus dari `sk/create.blade.php` dan `sk-custom/create.blade.php` karena error sudah ditampilkan otomatis oleh layout.
- **Riwayat page diperbarui**: `riwayat/index.blade.php` diganti penuh dengan styling Bootstrap 5 (`table-responsive`, `table-striped`, `table-hover`, form filter Bootstrap, tombol Bootstrap).
- **Test diperbarui**: `tests/Feature/ExampleTest.php` diupdate untuk menggunakan `RefreshDatabase` trait + `JenisSkSeeder` karena HomeController sekarang query database (sebelumnya `/` hanya kembalikan view welcome yang tidak butuh DB).
- **Cache opsional**: Jalankan `php artisan optimize:clear` setelah mengganti route `/` untuk menghindari `MethodNotAllowedHttpException` dari route cache yang sudah kadaluarsa.

### Tahap 11: Layout Dokumen Produksi (SkLayoutBuilder)

Mengganti cara dokumen Word disusun supaya mengikuti aturan "surat produksi" sekolah (hasil analisis `Surat_SK_umum.docx`).

- **Migration `jabatan_judul`**: Ditambahkan kolom `jabatan_judul` (nullable, after `jabatan`) ke tabel `pejabat_penandatangan`. Di surat contoh, judul berbunyi "KEPALA SMK MUHAMMADIYAH 3 BANJARMASIN" sedangkan baris tanda tangan hanya "Kepala Sekolah," — jadi butuh 2 isian terpisah. `specDasar()` menggunakan `$pejabat->jabatan_judul ?: $pejabat->jabatan` sebagai fallback.
- **Model `PejabatPenandatangan`**: `jabatan_judul` ditambahkan ke `$fillable`.
- **Controller `PejabatController`**: Field `jabatan_judul` ditambahkan ke form dan diproses di method `store`/`update`.
- **Form `_form.blade.php`**: Input baru dengan label "Jabatan untuk Judul SK" dan name `jabatan_judul`.
- **Class `SkLayoutBuilder`** (baru): Class layout murni — tidak tahu apa-apa soal database/Laravel. Menerima `$spec` array dan mengembalikan objek `PhpWord`. Aturan layout: font Calibri Light (judul 12pt, isi 10pt), margin atas 1,55 cm / kiri & kanan 1,5 cm / bawah 1,0 cm, blok Menimbang/Mengingat/Pertama dengan titik dua di 2,50 cm dan teks di 3,00/3,50 cm, blok tanda tangan di 10,16 cm dari margin kiri, kop surat hanya di halaman pertama, tanpa halaman kosong di akhir, baris judul tabel berulang di halaman berikutnya.
- **`SkDocumentGenerator` direfaktor**: Method `generate()` dan `generateCustom()` hanya menyiapkan `$spec` lalu menyerahkannya ke `(new SkLayoutBuilder)->build($spec)`. Semua method layout lama (`tambahKopSurat`, `tambahBlokMultiPoin`, `tambahBlokLabel`, `tambahTembusan`, `tambahBlokTtd`, `ratakanBaris`) dihapus. Class kini berisi tepat 9 method: `generate`, `generateCustom`, `specDasar`, `barisTemplate`, `diktumDariTemplate`, `tabelDariField`, `formatTanggalIsian`, `simpanDanKonversi`, `isiTemplate`.
- **Method tak terubah**: `simpanDanKonversi()` dan `isiTemplate()` TIDAK diubah — masih memakai `Process::fromShellCommandline()` + `setEnv(getenv())` + `set_time_limit(0)`. **Perubahan Tahap 12:** `buatNomorSurat()` DIHAPUS — diganti dengan `app(PenomoranService::class)->buat()` pada `generate()` dan `generateCustom()`.
- **`SkLayoutBuilder` diuji di luar Laravel**: Render LibreOffice cocok dengan surat contoh (posisi horizontal persis, jarak vertikal selisih ±0,03 cm per tabel), tabel yang pindah halaman tidak mengulang kop, dan tidak ada halaman kosong.

### Tahap 12: Penomoran/Numbering Configuration

Penomoran (numbering) module for auto-incrementing SK nomor sequence per year.

- **Migrations:**
  - `2026_10_15_051022_add_nomor_urut_to_sk_terbit_table.php` - Drops UNIQUE on `nomor_surat`; adds `nomor_urut` (unsignedInteger, nullable, after `nomor_surat`) + index; backfills from existing `nomor_surat` via regex /KEP.(\d+)/.
  - `2026_10_15_051023_create_penomoran_tahun_table.php` - Creates `penomoran_tahun`: `tahun` (unsignedSmallInteger, unique), `nomor_mulai` (unsignedInteger, default 1), `reset_at` (timestamp, nullable), + timestamps. `reset_at` set via `PenomoranService::atur()`.
  - `2026_10_15_051024_add_format_nomor_to_pengaturan_sekolah_table.php` - Adds `format_nomor` (string, default `{klasifikasi}/KEP.{nomor}–SMKM.3/DISDIKBUD/{bulan}/{tahun}`) and `digit_nomor` (unsignedTinyInteger, default 3) to `pengaturan_sekolah`.

- **Models:** `PenomoranTahun` (fillable: `tahun`, `nomor_mulai`, `reset_at`; cast: `reset_at` => datetime), `SkTerbit` (`nomor_urut` in $fillable + cast to integer), `PengaturanSekolah` (`format_nomor` + `digit_nomor` in $fillable).

- **Service:** `PenomoranService` (app/Services/PenomoranService.php) provides:
  - `ambilTahun(int $tahun): PenomoranTahun` - Returns row via `firstOrCreate(['tahun' => $tahun], ['nomor_mulai' => 1])`. Always returns model, never null.
  - `nomorTertinggi(int $tahun): int` - Highest `nomor_urut` for year; if `reset_at` set, counts only SKs created after that timestamp.
  - `berikutnya(int $tahun): int` - Returns `max(nomorTertinggi, nomor_mulai - 1) + 1`. Always >= nomor_mulai.
  - `buat(JenisSk $jenisSk, Carbon $tanggal): array` - Entry point: calls `berikutnya()` + `format()`, returns `[$nomorUrut, $nomorSurat]`. Used by `SkDocumentGenerator::generate()` + `generateCustom()`.
  - `atur(int $tahun, int $mulaiDari): void` - Sets `nomor_mulai` + `reset_at` to `now()`.
  - `format(JenisSk $jenisSk, int $nomor, Carbon $tanggal): string` - Applies `format_nomor` via `strtr()`. Replaces: `{klasifikasi}` (kode_klasifikasi), `{nomor}` (zero-padded per `digit_nomor`), `{bulan}` (roman I-XII), `{tahun}` (4-digit). Falls back to `FORMAT_DEFAULT` if empty.

- **Controller:** `PenomoranController` - `edit()` shows ledger table, next-number preview, and live format sample; `simpanFormat()` validates `format_nomor` (must contain `{nomor}`) and `digit_nomor` (1-6); `atur()` validates `tahun` (2000-2100) and `nomor_mulai` (1-999999).

- **Routes:** `pengaturan.penomoran.edit` (GET), `pengaturan.penomoran.format` (PUT), `pengaturan.penomoran.atur` (POST).

- **Views:** `pengaturan/penomoran/edit.blade.php` - Form with format_nomor input, digit_nomor input, live format preview, year selection dropdown, ledger table showing existing tahun entries, and an "Atur Nomor" form for setting next year's starting number.

- **`SkDocumentGenerator` integration (WIRED - Tahap 12 revisi):** `generate()` dan `generateCustom()` kini memanggil `app(PenomoranService::class)->buat($jenisSk, $tanggal)` untuk mendapatkan `[$nomorUrut, $nomorSurat]`. Nilai `nomor_urut` disimpan ke rekor SkTerbit lewat `simpanDanKonversi()` (lines 46 & 74). Metode lama `buatNomorSurat()` **dihapus** — logika penomoran digantikan oleh `PenomoranService::buat()` → `berikutnya()` + `format()`. Class SkDocumentGenerator tetap berisi **9 method**: `generate`, `generateCustom`, `specDasar`, `barisTemplate`, `diktumDariTemplate`, `tabelDariField`, `formatTanggalIsian`, `simpanDanKonversi`, `isiTemplate`.

## 11. Bug Fixes & Implementation Details

#### 1. Nomor surat collision fix (buatNomorSurat in SkDocumentGenerator.php)
- **Problem:** Duplicate nomor_surat values caused `UNIQUE constraint failed: sk_terbit.nomor_surat` SQL errors. The `nomor_surat` column has a UNIQUE constraint (see migration 003449).
- **Fix:** `buatNomorSurat()` generates nomor using `kode_klasifikasi` as the grouping prefix, then increments sequence within that group via `count() + 1` with LIKE pattern matching on `nomor_surat` and `whereYear('created_at', $tahun)`.
- **Format:** `{kode_klasifikasi}/KEP.{seq:03d}/{singkatan}/{roman_month}/{4-digit year}`
- **Result:** Each SK type gets independent sequence: `400/KEP.001/SMK/IX/2026`, `400/KEP.002/SMK/IX/2026`, `421/KEP.001/SMK/IX/2026`, etc.

#### 2. Template placeholder typo fix (JenisSkSeeder.php - KEBIJAKAN_PERATURAN)
- **Problem:** `template_diktum` for KEBIJAKAN_PERATURAN used `{judul_kebijikin}` (with 'i') but field_schema key was `judul_kebijawan` (with 'a'). Terminal renders 'a' and 'i' identically, so mismatch was invisible. `isiTemplate()` uses `str_replace('{key}', value)` - since keys didn't match, placeholder rendered literally as `{judul_kebijikin}` in the DOCX.
- **Fix:** Changed `{judul_kebijikin}` to `{judul_kebijawan}` in `template_diktum` to match field_schema key.
- **Note:** This was the only typo of this kind across all 12 jenis SK templates. All other `{placeholder}` tokens in `template_diktum`, `template_menimbang`, and `template_mengingat` match their corresponding field_schema keys.

#### 3. Multi-line textarea diktum parsing fix (SkDocumentGenerator.php)
- **Problem:** Diktum parsing previously substituted template placeholders THEN split by newlines. If a textarea field (e.g., `isi_kebijawan`) contained multiple lines, embedded newlines caused extra diktum blocks to be parsed, pushing content out of the intended block into orphaned paragraphs.
- **Fix:** Reordered diktum loop to split the RAW template by newlines first, then substitute placeholders per-line via `isiTemplate()`. Additionally, `isiTemplate()` now flattens newlines within individual field values (\n -> space) before substitution.
- **Result:** Multi-line textarea values (e.g., "1. Dilarang merokok\n2. Wajib memakai seragun") render as single continuous text within one diktum block.

#### 4. LibreOffice PDF conversion fails in web server context (SkDocumentGenerator.php)
- **Problem:** `php artisan serve` (PHP built-in web server) has `max_execution_time=30` from php.ini. LibreOffice PDF conversion requires >30 seconds in web context (CLI overrides to 0/unlimited). Additionally, the `new Process([...])` array form didn't pass all required environment variables (USERPROFILE, APPDATA) to LibreOffice, causing exit code 1 with empty output/error.
- **Fix:** Switched to `Process::fromShellCommandline()` (runs through `cmd.exe`, proper environment setup), added `$process->setEnv(getenv())` to explicitly pass all env vars, added `set_time_limit(0)` to override PHP time limit, and `$process->setWorkingDirectory(base_path())` for consistent working directory.
- **Result:** Web POST flow (SkController store) now successfully creates both DOCX and PDF. See Section 17, item 18.
## 12. Riwayat SK Feature (Tahap 5)

The Riwayat (history) feature allows users to browse, search, and download all previously generated SK documents.

- **Route:** GET /riwayat -> 
iwayat.index -> RiwayatController::index()
- **Controller:** Queries SkTerbit::with('jenisSk')->orderByDesc('created_at'), supports filtering by jenis_sk_id and searching 
omor_surat with LIKE, paginates 15 per page with ->withQueryString().
- **View:** 
iwayat/index.blade.php - table with columns: Nomor Surat, Jenis, Tanggal, Detail Isian (expandable), Aksi (Word/PDF download links).

---

## 13. SK Custom Builder Feature (Tahap 6/7)

The SK Custom builder allows composing SK documents without predefined templates.

- **Routes:** GET /sk-custom/create (SkCustomController::create), POST /sk-custom (SkCustomController::store)
- **View:** sk-custom/create.blade.php - JavaScript block builder with paragraf + tabel blocks (add/remove/reorder)
- **Controller:** store() extracts judul_sk + locks_json, auto-creates JenisSk (kode='CUSTOM', kode_klasifikasi='000', is_custom=true), calls generateCustom()
- **Service:** generateCustom() uses same header/footer/TTD as generate() but iterates $blocks instead of 	emplate_diktum. Paragraf blocks render via 	ambahBlokLabel() if label set; tabel blocks as Word tables.
- **Result view:** sk-custom/hasil.blade.php - shows judul, nomor_surat, download links

---

## 14. Utility & Test Scripts

Semua script debug sudah dihapus di Tahap 8.

---

## 15. Useful Commands

`
php artisan serve --host=127.0.0.1 --port=8999
php artisan test
php artisan test:pipeline
php artisan migrate
php artisan migrate:fresh
php artisan db:seed --class=JenisSkSeeder
php artisan storage:link
php artisan config:clear
php artisan optimize:clear    # Clear ALL caches (route, config, view, compiled) - run after route changes
`

Note: 
pm run build (or 
pm run dev) for Vite assets. composer dump-autoload can take 40+ seconds with optimize-autoloader.

---

## 16. Git History & Development Workflow

### Git state
- The repository has 3 commits on master:
  - 59fe357 (2026-09-15) - Tahap 1: fondasi Laravel + SQLite + pipeline Word-PDF
  - dfb412f (2026-09-24) - Tahap 2-7: pengaturan, jenis SK, generator dokumen, riwayat, SK custom (36 files, 1986 insertions) - added after Tahap 8 cleanup (see Langkah 6)
  - 7f34841 - Tahap 8: update docs - git state, route count, cleanup notes
- Tag: tahap-7-selesai (created in Tahap 8)
- All development from Tahap 2 through Tahap 7 was initially uncommitted. Tahap 8 cleaned up debug files and committed all changes.

### Development phases
- Tahap 1 (committed): Laravel foundation, SQLite, TestPipeline
- Tahap 2-4B (committed in Tahap 8): Pipeline, Jenis SK, SK generation (DOCX+PDF)
- Tahap 5 (committed): Riwayat (history) feature
- Tahap 6/7 (committed): SK Custom builder
- Tahap 9A (committed): Document refinement - kop surat first page only, multi-poin mem/mengingat, Memperhatikan section, Tembusan, typo fix, LibreOffice web context fix, Nonaktifkan Semua/Hapus buttons
- Tahap 9B (working tree): Pengaturan Sekolah, editable redaksi for all jenis SK, hardcoded values moved to DB
- **Tahap 10A (working tree):** Bootstrap 5 local install, HomeController + homepage view, Bootstrap 5 pagination, riwayat table styling, layout navbar, error block cleanup, test update
- **Tahap 12 (working tree):** Penomoran/Numbering configuration - `penomoran_tahun` ledger table, `nomor_urut` on `sk_terbit`, `format_nomor` + `digit_nomor` on `pengaturan_sekolah`, `PenomoranService` service, `PenomoranController` + blade UI, 3 new routes
- **Tahap 11 (working tree):** Layout Dokumen Produksi — migrasi `jabatan_judul` ke Pejabat, refactoring `SkDocumentGenerator` untuk mendelegasikan layout ke class baru `SkLayoutBuilder` (pure layout, no DB knowledge), font Calibri Light (judul 12pt, isi 10pt), margin 1,55/1,5/1,5/1,0 cm, blok Menimbang/Mengingat/Pertama dengan titik dua di 2,50 cm, penanda di 3,00 cm, teks di 3,50 cm, blok tanda tangan di 10,16 cm, kop hanya di halaman pertama, tanpa halaman kosong di akhir, tabel header berulang di halaman berikutnya, support paragraf berlabel & tanpa label & tabel di SK Custom
- **Tahap 13 (working tree):** Riwayat - hapus surat (DELETE `/riwayat/{skTerbit}` per-baris, POST `/riwayat/hapus-terpilih` massal) + file .docx/.pdf ikut terhapus; filter pencarian (kata kunci, jenis_sk_id, tahun, bulan, rentang dari/sampai, urutkan). Penomoran tidak berubah (Tahap 12).
- **Tahap 14 (working tree):** Opsi surat (tanggal/kop/penandatangan) via `_opsi-surat.blade.php`, `$opsi` pada `generate`/`generateCustom`, private `siapkanOpsi`/`catatanOpsi`, kolom `kop_surat`+`penandatangan_*`+`tanggal_sk` pada `SkTerbit`, `OpsiSuratTest` (10). Lihat "Catatan Teknis Tahap 14" di akhir dokumen.
- **Tahap 15 (restore-point committed @ c51718a; blade+generator code in working tree):** 'Lainnya (isi manual)…' (`value="__manual__"`) pada setiap dropdown select form SK; `normalisasiPilihan()` private baru pada `SkDocumentGenerator`; seeder patch `Tahap15PatchSeeder` menghapus opsi 'Lainnya' lama dari Alasan PEMBERHENTIAN. `PilihanManualTest` + `OpsiSuratTest`: 15 test, 36 assertions OK; `test:pipeline` BERHASIL.

---

## 17. Key Gotchas & Workarounds

1. **PHP version:** Always use PHP 8.5.10 from WinGet path. Do NOT use Laragon PHP 8.1.10.
2. **php.ini extensions:** Must be listed without .dll suffix (e.g., extension=pdo_sqlite).
3. **platform_check.php bypass:** DISABLED via composer config platform-check false.
4. **Duplicate migrations:** Only 213437 and 213440 remain active (plus 3 default Laravel).
5. **Server binding:** Use --host=127.0.0.1 not localhost. Default test port: 8999.
6. **TestPipeline line 57:** Minor indentation issue (cosmetic only).
7. **composer dump-autoload timeout:** Can take 40+ seconds. Run in background if needed.
8. **Jenis SK data:** 12 entries via JenisSkSeeder. DatabaseSeeder does NOT call it - run separately.
9. **CSRF token expiry:** Laravel default 120-minute session. Get form first to obtain CSRF + cookie.
10. **PDF permissions:** LibreOffice may not convert if run as different user.
11. **Nomor surat collisions:** kode_klasifikasi-based sequencing. UNIQUE constraint on nomor_surat.
12. **Template placeholder matching:** Every placeholder must byte-match field_schema key. Verify with bin2hex().
13. **.env.example LIBREOFFICE_BINARY:** (FIXED in Tahap 8) .env.example now includes the path.
14. **is_custom flag:** JenisSk has is_custom boolean. SK Custom auto-creates kode='CUSTOM'.
15. **Root / route (UPDATE - Tahap 10A):** No longer returns `welcome.blade.php`. Now uses `HomeController@index` (`route('home')`) with system readiness panel, SK cards, and recent SK table. Requires `jenis_sk` seeder + DB tables.
16. **Nomor surat format:** 4-digit year (e.g., 2026), Roman month (I-XII), 3-digit seq (KEP.001).
17. **Git workflow:** Tahap 1 committed; Tahap 2-7 committed in Tahap 8 after cleanup. Tag: tahap-7-selesai.
18. **LibreOffice in web context (php artisan serve):** PHP built-in server uses `max_execution_time=30` from php.ini (CLI overrides to 0). The `new Process([...])` array form doesn't pass USERPROFILE/APPDATA env vars to LibreOffice on Windows. Fix: use `Process::fromShellCommandline()` + `setEnv(getenv())` + `set_time_limit(0)`.
19. **Bootstrap 5 local files (NEW - Tahap 10A):** Bootstrap 5.3.3 CSS (~227 KB) and JS bundle (~79 KB) are loaded from `public/vendor/bootstrap/` — not from CDN. Application works offline. If files become corrupted, re-download with `Invoke-WebRequest`.
20. **Route cache after route changes (NEW - Tahap 10A):** After changing route `/`, run `php artisan optimize:clear` to avoid `MethodNotAllowedHttpException` from stale route cache.
21. **`jabatan_judul` field on Pejabat (NEW - Tahap 11):** PejabatPenandatangan now has `jabatan_judul` (nullable, after `jabatan`). Used as the title in the SK header (e.g., "KEPALA SMK MUHAMMADIYAH 3 BANJARMASIN"), while `jabatan` is used in the signature block (e.g., "Kepala Sekolah"). Falls back to `jabatan` if `jabatan_judul` is empty.
22. **SkLayoutBuilder delegation (NEW - Tahap 11):** `SkDocumentGenerator` delegates all layout to `SkLayoutBuilder`. `SkLayoutBuilder::build()` receives a `$spec` array and returns a `PhpWord` object — it has NO knowledge of database/Laravel. Layout rules: Calibri Light font (judul 12pt, isi 10pt), margin atas 1,55 cm / kiri&kanan 1,5 cm / bawah 1,0 cm, Menimbang/Mengingat/Pertama colon at 2,50 cm, penanda at 3,00 cm, text at 3,50 cm, tanda tangan block at 10,16 cm from left margin, kop surat only on first page, no empty trailing page, table header repeats on subsequent pages.
23. **SkDocumentGenerator method count (NEW - Tahap 11, reviewed Tahap 12):** Class contains exactly **11 methods**: `generate`, `generateCustom`, `siapkanOpsi`, `catatanOpsi`, `specDasar`, `barisTemplate`, `diktumDariTemplate`, `tabelDariField`, `formatTanggalIsian`, `simpanDanKonversi`, `isiTemplate` (`siapkanOpsi` and `catatanOpsi` added Tahap 14 for per-surat opsi). Note: the original Tahap 11 docs listed `buatNomorSurat` in place of `formatTanggalIsian` — `formatTanggalIsian` was always present in the code. **`buatNomorSurat` was DELETED in Tahap 12** — nomor generation moved to `PenomoranService::buat()`. Methods `simpanDanKonversi` and `isiTemplate` are UNCHANGED from prior versions — do not modify. (`simpanDanKonversi` now also persists `nomor_urut` from `$spec['nomor_urut']`.)
24. **Field name `jabatan_judul` in `_form.blade.php` (NEW - Tahap 11):** The Pejabat form now includes a separate input named `jabatan_judul` (labeled "Jabatan untuk Judul SK") alongside `jabatan` (labeled "Jabatan"). Both are fillable on the PejabatPenandatangan model.
25. **Penomoran/nomor_urut field naming (NEW - Tahap 12):** The SkTerbit model field is `nomor_urut` (not `nomor`). It is auto-incremented per year via `PenomoranService::berikutnya()`. If manually overriding the SK nomor (e.g., in tests or data migration), ensure `nomor_urut` is set on the SkTerbit record, not `nomor`.
26. **Penomoran route names & service class spelling (NEW - Tahap 12):** Three numbering routes use the `pengaturan.penomoran.*` naming convention (`edit`, `format`, `atur`). The service class is `PenomoranService` (note: NOT `PenomahanService` or `PemohnoranService`). Verify that controller imports and type-hints exactly match `PenomoranService` - a single-character difference causes a 500 error due to Laravel DI resolution failure.

## 18. Riwayat SK — Tahap 13 (Hapus Surat + Filter Pencarian)

Extends the Riwayat fitur (Section 12, originally Tahap 5) with per-surat and bulk deletion, plus an advanced search/filter panel. **No migrations**; `PenomoranService`, `SkDocumentGenerator`, and `SkLayoutBuilder` are untouched. Penomoran behavior from Tahap 12 is unchanged: deleting the highest `nomor_urut` makes the next number reuse it; deleting a middle record does not shift the next number.

- **Routes (new):** `DELETE /riwayat/{skTerbit}` -> `RiwayatController::destroy()` (delete one); `POST /riwayat/hapus-terpilih` -> `RiwayatController::hapusTerpilih()` (bulk delete). `GET /riwayat` -> `index()` is extended. `php artisan route:list --path=riwayat` now shows 3 routes.
- **Controller (`app/Http/Controllers/RiwayatController.php`):** `index()` validates & applies filters `cari` (LIKE on `nomor_surat` and `data_isian`), `jenis_sk_id`, `tahun` (`whereYear`), `bulan` (`whereMonth`), `dari`/`sampai` (`whereDate`), and `urut` ordering (terbaru / nomor-terbesar / nomor-terkecil); paginate 15 + `withQueryString()`; plucks distinct tahun via SQLite `strftime('%Y', tanggal_sk)`. `destroy()` and `hapusTerpilih()` remove the `.docx`/`.pdf` files via `Storage::disk('public')` then the `sk_terbit` row.
- **View (`resources/views/riwayat/index.blade.php`):** filter card (keyword, jenis SK, tahun, bulan, date range, sort) + Reset; table with a checkbox per row and a "cek-semua" toggle; **Hapus** per row and **Hapus yang Dicentang** (confirm dialogs, warn if nothing checked); Word/PDF links.
- **Test (`tests/Feature/RiwayatTest.php`):** 7 tests using `RefreshDatabase` + `Storage::fake('public')` -> `7 passed, 24 assertions`.

> Note (Tahap 13): the spec's test text called `PenomoranService::berikutna()`, but the actual method (used by `PenomoranController` and the passing `PenomoranTest`) is `berikutnya()`. Corrected the test to call `berikutnya()`; `PenomoranService` is unchanged. Also fixed typo in the empty-state text "cocup" -> "cocok".

---

## 19. Opsi Surat per-Surat — Tahap 14

Menambahkan pemilihan **kop surat (letterhead), penandatangan (pejabat), dan tanggal penetapan** pada formulir buat SK (12 standar + Custom), persist ke tabel `sk_terbit`, dan menambah kolom + filter **Penandatangan** pada Daftar Riwayat.

- **Migrasi (`2026_10_19_022456_add_opsi_surat_to_sk_terbit_table.php`):** tambah 5 kolom — `kop_mode` (string: `tanpa`/`kosong`/`kop`), `letterhead_asset_id` (nullable, **tanpa FK** — disengaja agar rekaman SK lama tak putus bila aset/kop atau pejabat dihapus), `penandatangan_id`, `penandatangan_nama` (terindeks), `penandatangan_jabatan`. Nilai kolom ini diset pada `simpanDanKonversi()` lewat `catatanOpsi()`. `down()` drop kolom + index by-name. Backup DB sebelum migrasi: `database/backup-sebelum-tahap14.sqlite`.
- **Model `SkTerbit`:** kelima kolom di atas + `tanggal_sk` (cast Carbon) pada `$fillable`. **Tidak ada relasi baru** (disengaja menghindari FK).
- **`SkDocumentGenerator`:** `generate(JenisSk, array, array $opsi = [])` dan `generateCustom(JenisSk, string, array, array $opsi = [])` kini menerima `$opsi`. Private baru: `siapkanOpsi($opsi)` (parse/validate `tanggal` lewat `Carbon::parse` → melempar `Exception('Tanggal penetapan tidak valid.')` bila tak valid; resolve `kop` = id/`aktif`/`tanpa`/`kosong`; resolve `penandatangan` = id/`aktif`/`manual` dengan fallback `jabatan_judul`→`jabatan`; manual wajib nama+jabatan) dan `catatanOpsi($siap)` (memetakan ke kolom persist). Tanggal pilihan mengalir ke `PenomoranService::buat($jenisSk, $siap['tanggal'])` sehingga nomor mencerminkan bulan/tahun yang dipilih (bukan `today`). `specDasar()` direfaktor agar `kop_path`/`kop_ruang` berasal dari `$siap`. **Enam method yang dilarang (`simpanDanKonversi`, `isiTemplate`, `barisTemplate`, `diktumDariTemplate`, `tabelDariField`, `formatTanggalIsian`) tidak berubah** — Pint hanya menyelaraskan spasi.
- **`SkLayoutBuilder`:** bila `spec['kop_ruang']` diset (opsi kop = `kosong`), pancarkan 5 baris kosong 12 pt (~2,6 cm) di atas halaman pertama untuk kertas yang sudah berkop (pre-printed).
- **Controller `SkController` / `SkCustomController`:** `store()` mengekstrak `$opsi = (array) $request->input('opsi')` dan `$data = $request->except(['_token', 'opsi'])`, lalu panggil `generate(..., $opsi)` / `generateCustom(..., $opsi)`. Validasi opsi melempar `Exception` yang ditangkap → `back()->withErrors()` (pesan *wajib* / *belum diaktifkan* / *Tanggal penetapan tidak valid.*). `@include('sk._opsi-surat')` ada di `sk/create.blade.php` & `sk-custom/create.blade.php`.
- **`AppServiceProvider`:** `View::composer('sk._opsi-surat', …)` mendorong daftar aset kop (default *aktif*), daftar penandatangan (default *aktif*), dan tanggal hari ini.
- **Blade `sk/_opsi-surat.blade.php` (baru, di-`@include` ke 2 view create):** picker tanggal; dropdown kop (daftar + "Tanpa kop" + "Tanpa kop, sisihkan ruang" + pratinjau); dropdown penandatangan (daftar + "Isi manual…" dengan `nama`, `jabatan`, `jabatan_judul`, `label_id`, `nomor_id`). JS mengganti pratinjau + panel manual; field manual hanya `required` saat dipilih — **input manual tidak pernah disimpan ke tabel pejabat**.
- **Riwayat (`RiwayatController` + `riwayat/index.blade.php`):** kolom **Penandatangan** (nama; `—` untuk rekaman lama) + filter dropdown Penandatangan (distinct). Fitur hapus pilihan / hapus tunggal / urut tidak berubah. View pernah dihapus & dibuat kembali versi lengkap.
- **Test (`tests/Feature/OpsiSuratTest.php`, 10):** default pejabat *active*, seleksi daftar, fallback `jabatan_judul`, manual, penolakan (*"wajib"*/*"belum diaktifkan"*), kop `tanpa`/`kosong`/file hilang, tanggal custom+hari-ini+tidak-valid (`"bukan-tanggal"` → `Carbon\InvalidFormatException` → *Tanggal*), filter Riwayat via HTTP. **10/10 lulus.** Full suite **26/26 OK (67 asersi)**; `test:pipeline` menghasilkan DOCX+PDF via LibreOffice pada DB prod.

> Catatan Teknis Tahap 14: `Carbon::parse('bukan-tanggal')` melempar `InvalidFormatException` (subclass `\Throwable`) pada Carbon 3.14.0 — tertangkap lewat `catch (\Throwable)` dan dilempar kembali sebagai `Exception('Tanggal penetapan tidak valid.')`. Key array `$siap` memakai `kop_path`/`kop_ruang` (huruf kecil) agar cocok dengan akses `SkLayoutBuilder::build($spec['kop_path'])` / `$spec['kop_ruang']`.

---

## 18. Opsi Isi Manual & Patch PEMBERHENTIAN (Tahap 15)

Tahap 15 menambahkan kemampuan **isi manual / teks bebas** pada setiap dropdown `select` di form SK: pengguna pilih opsi 'Lainnya (isi manual)…' → isi teks → teks itu **dicetak di surat** dan **disimpan** ke `data_isian`. Selain itu, seeder patch kecil memurnikan opsi 'Lainnya' (catch-all lama) pada Alasan Pemberhentian — yang kini tidak lagi diperlukan karena opsi manual baru di atas penggantiannya.

- **Blade `resources/views/sk/create.blade.php`:** setiap `<select>` (di seluruh jenis SK, termasuk `PEMBERHENTIAN`) menerima opsi tambahan `<option value="__manual__">Lainnya (isi manual)…</option>` — diletakkan paling bawah, dipisah oleh `<option disabled>`. Setiap select dipasangkan dengan `<input type="text" name="{key}__manual" class="d-none" required>`: ketika `__manual__` dipilih, input ditampilkan & required; sebaliknya disembunyikan (`d-none`) & dikecualikan dari validasi. Nilai defaultnya kosong. Blade **tanpa BOM, LF**.
- **Konvensi nilai:** select manual memakai nilai `__manual__`; elemen teks manual bernama `{key}__manual` (mis. `alasan__manual`). `generate()` memakai data yang sudah dinormalisasikan → teks asli masuk ke dokumen & riwayat.
- **`App\Services\SkDocumentGenerator.php`:** `generate()` kini panggil `normalisasiPilihan()` (private, baru) pada `$data` **sebelum** `formatTanggalIsian` / `tabelDariField` / `data_isian`. `normalisasiPilihan()`:
  - `trim` setiap nilai;
  - melempar `\Exception("…wajib diisi")` bila field wajib kosong (kasus `__manual__` + input kosong);
  - bila nilai select == `__manual__`, ganti dengan teks dari `{key}__manual` (trimmed);
  - `unset` semua key `{key}__manual` agar tidak masuk ke `data_isian`/dokumen;
  - kembalikan `$data` yang sudah bersih, tanpa mengubah skema/field lain.
  - **12 method; enam method terlarang** (`simpanDanKonversi`, `isiTemplate`, `barisTemplate`, `diktumDariTemplate`, `tabelDariField`, `formatTanggalIsian`) **tidak berubah sama sekali.** Pint **tidak** diterapkan pada generator (working tree belum ter-commit & berisi perubahan Tahap 12, agar tidak menyentuh method terlarang pre-existing). `php -l` bersih.
  - `generate()` memakai `(new SkLayoutBuilder)` (tanpa argumen konstruktor) — gaya asli, dikonfirmasi via `git show HEAD:`.
- **`database/seeders/Tahap15PatchSeeder.php` (baru, di-commit @ c51718a sebagai restore-point):** menghapus opsi **'Lainnya'** (2-n) dari field `alasan` pada `JenisSk` kode `PEMBERHENTIAN` — hanya menyentuh `field_schema` via Eloquent, **tanpa migration**, tidak menyentuh `JenisSkSeeder.php` lain. Idempotent (re-seed aman; bila opsi sudah hilang, tidak berubah). Tanpa BOM, LF. Backup DB sebelum patch ada di `database/backup-sebelum-tahap15.sqlite` (git-ignored via `database/.gitignore`); backup memuat `PEMBERHENTIAN.alasan` asli = `Habis Masa Jabatan | Mengundurkan Diri | Mutasi | Lainnya` (4 opsi).
  - **Verifikasi prod (SELECT `field_schema`):** pasca-seeder, opsi `alasan` PEMBERHENTIAN = `Habis Masa Jabatan | Mengundurkan Diri | Mutati` (3 opsi; 'Lainnya' terhapus).
- **Test (`vendor/bin/phpunit` langsung, bukan `php artisan test`):**
  - `tests/Feature/PilihanManualTest.php` (baru, 5 tes): opsi biasa tidak berubah; `__manual__` diganti teks trim; `__manual__`+kosong → throw `Exception`; form create menampilkan `value="__manual__"` & `name="alasan__manual"`; seeder menghapus 'Lainnya' → `assertSame(['Mutati'], ...)`.
  - `tests/Feature/OpsiSuratTest.php` (10 tes, Tahap 14) tetap hijau.
  - `PilihanManualTest + OpsiSuratTest` bersama: **15 test, 36 assertions OK.**
  - `Pint` diterapkan hanya pada 2 file baru (seeder + test) untuk LF + formatting; **tidak** pada `SkDocumentGenerator.php`.
  - `test:pipeline` (generate → DOCX → PDF via LibreOffice): **BERHASIL** (dicek 2×, termasuk pasca-koreksi `(new SkLayoutBuilder)` no-parens).
  - `php artisan optimize:clear` sudah dijalankan.

> Catatan Teknis Tahap 15: `value="__manual__"` dipilih agar tidak bertabrakan dengan nilai opsi data real; `unset` key `{key}__manual` pada `normalisasiPilihan` memastikan field teks manual **tidak** tersimpan sebagai data_isian terpisah — hanya nilainya yang *merge* ke key utama. `trim` + throw eksplisit memastikan tidak ada isian kosong masuk dokumen. Opsi 'Lainnya (isi manual)…' ditambahkan ke **setiap** dropdown select; seeder pembersihan 'Lainnya' lama hanya berlaku pada Alasan PEMBERHENTIAN (satu-satunya field yang aslinya ada opsi catch-all 'Lainnya').

---

## 16. Launcher / Start Menu (Peluncur Aplikasi)

### Ringkasan
Tahap 16 menambahkan 4 file yang memungkinkan aplikasi dijalankan dengan klik dua kali (tanpa membuka terminal secara manual):

| File | Fungsi |
|---|---|
| `start.bat` | Memeriksa kesiapan, menjalankan aplikasi di latar belakang, membuka browser |
| `stop.bat` | Menghentikan proses server yang dijalankan oleh `start.bat` |
| `buat-shortcut.bat` | Membuat 2 shortcut di Desktop ("SK Generator" dan "Berhenti SK Generator") |
| `launcher/cek-awal.php` | Pemeriksa kesiapan otomatis (dipanggil oleh `start.bat`) |

### Pemeriksaan Kesiapan (cek-awal.php)
`launcher/cek-awal.php` memverifikasi:
1. **Folder project** - pastikan berada di folder yang sama dengan `artisan`
2. **PHP** - versi minimal 8.3 (menampilkan daftar PHP di PATH jika terlalu lama)
3. **Ekstensi PHP** - pdo_sqlite, mbstring, openssl, fileinfo, gd, zip, dom, xml, xmlwriter, libxml, curl, ctype, tokenizer
4. **Berkas dasar** - `vendor/`, `.env`, `APP_KEY`
5. **Database** - file ada, tabel lengkap, jumlah jenis SK; otomatis menerapkan migrasi tertunda dengan cadangan `database/backup-otomatis-<tanggal>.sqlite`
6. **Folder penyimpanan** - membuat folder yang hilang dan `storage:link` otomatis
7. **LibreOffice** - binary dikonfigurasi di `LIBREOFFICE_BINARY` `.env`
8. **Font Calibri Light** - dicek di `Windows\Fonts` dan `AppData\Local\Microsoft\Windows\Fonts`
9. **Port** - otomatis memilih port 8000-8010

Masalah fatal (PHP, ekstensi, .env/APP_KEY, vendor, database hilang) -> aplikasi **tidak** dijalankan.
Masalah tidak fatal (LibreOffice/font tidak ada, jenis SK kosong) -> hanya **[PERINGATAN]**, aplikasi tetap berjalan.

### Format File
- File `.bat` - **ASCII, akhiran baris CRLF** (diperlukan agar `goto` label berfungsi)
- `cek-awal.php` - **UTF-8 tanpa BOM, ASCII** (semua teks Indonesia agar tampil benar di cmd Windows)

### Verifikasi (Agent)
```
php -l launcher\cek-awal.php   -> No syntax errors detected
php launcher\cek-awal.php      -> [OK] semua, HASIL: semua siap (exit 0)
php launcher\cek-awal.php --tunggu 8000 2 -> exit 1 (aplikasi belum berjalan)
CRLF=True  ASCII=True untuk ketiga .bat
```

### .gitignore (Diperbarui)
```
/storage/app/launcher.txt
/storage/app/server.pid
/database/backup-otomatis-*.sqlite
```






## Tahap 17A - PHP Portabel untuk Peluncur

- Masalah: PC klien hanya punya PHP eRapor 8.2.29 (terlalu lama; ekstensi pdo_sqlite mati).
- Solusi: peluncur memakai PHP milik aplikasi di `runtime\php` (diunduh otomatis dari windows.php.net oleh `launcher\siapkan-php.ps1`; cabang 8.5, lalu 8.4, lalu 8.3; varian NTS x64; SHA256 diperiksa).
- `start.bat` selalu memanggil `runtime\php\php.exe` lewat path absolut dan mengatur `PHPRC` ke folder itu. PATH dan PHP lain di komputer TIDAK diubah.
- `php.ini` dibuat otomatis dari `php.ini-production` + ekstensi (curl, fileinfo, gd, mbstring, openssl, pdo_sqlite, zip), memory_limit 512M, upload 32M, timezone Asia/Makassar. Dibuat ulang bila isinya berbeda (mis. folder aplikasi dipindah).
- `siapkan-php.ps1` selalu dipanggil tiap `start.bat`; bila PHP sudah siap selesai dalam sekejap.
- Bila unduhan gagal: unduh PHP "NTS x64" zip dari windows.php.net, ekstrak ke `runtime\php` (php.exe langsung di dalamnya), jalankan ulang.
- Bila PHP gagal berjalan karena Visual C++ Redistributable belum ada: pesan GAGAL menampilkan tautan pemasangan (pemasangan otomatis dikerjakan di Tahap 17C).
- `/runtime/` masuk `.gitignore`.
- Belum dikerjakan (tahap berikutnya): vendor/composer, `.env`, database + seeder, junction storage, timezone aplikasi (config/app.php masih 'UTC'), update.bat/pasang.bat.

## Tahap 17B - Penyiapan Otomatis Aplikasi

- `launcher\siapkan-aplikasi.php` dipanggil `start.bat` sesudah `siapkan-php.ps1` dan sebelum `cek-awal.php`. Hanya mengerjakan yang belum ada; senyap bila semua sudah siap.
- Yang disiapkan: (1) `.env` dari `.env.example` dengan nilai pemakaian harian (APP_NAME "SK Generator", APP_ENV production, APP_DEBUG false, APP_URL http://127.0.0.1:8000, APP_TIMEZONE Asia/Makassar, LOG_LEVEL warning) dan APP_KEY acak (dibuat langsung di PHP, tanpa artisan); (2) folder storage, bootstrap/cache, database; (3) `vendor` lewat `composer install --no-dev --optimize-autoloader` (composer.phar diunduh ke `runtime\composer.phar` lewat PowerShell; butuh internet; GitHub/packagist harus terjangkau); (4) file `database\database.sqlite` kosong; (5) tautan `public\storage` berupa junction (PowerShell New-Item -ItemType Junction; tanpa Administrator).
- Migrasi tetap dikerjakan `cek-awal.php` (dengan cadangan otomatis bila database sudah berisi). Bila `jenis_sk` kosong, `cek-awal.php` kini menjalankan `php artisan db:seed --force` otomatis.
- `DatabaseSeeder` kini: bila `jenis_sk` kosong menjalankan `JenisSkSeeder`, `Tahap9aPatchSeeder`, `Tahap9bPatchSeeder`, `Tahap15PatchSeeder` berurutan; lalu `PengaturanAwalSeeder` (membuat Pengaturan Sekolah "SMK Muhammadiyah 3 Banjarmasin" bila belum ada). Seeder TIDAK menimpa template atau pengaturan yang sudah diedit (JenisSkSeeder memakai updateOrCreate, sehingga tidak boleh dijalankan ulang pada database yang sudah dipakai). User::factory dihapus dari DatabaseSeeder (faker tidak terpasang di klien).
- Zona waktu: `config/app.php` memakai `env('APP_TIMEZONE', 'Asia/Makassar')` (sebelumnya 'UTC' sehingga `now()` untuk tanggal default form mundur sehari pada pukul 00.00-08.00 WITA). Data lama (created_at) tersimpan dalam UTC dan tidak dikonversi.
- Perbaikan: `.env.example` memakai kutip TUNGGAL untuk `LIBREOFFICE_BINARY` karena kutip ganda + backslash membuat file .env dianggap rusak oleh pembaca .env Laravel.
- `.gitattributes`: `*.bat` dan `*.ps1` diset `eol=crlf` (ZIP GitHub sebelumnya berisi start.bat dengan LF).
- Tes baru `DatabaseSeederTest` (3 tes): instalasi baru lengkap, seeder tidak menimpa data yang diedit, timezone WITA.
- Selesai: pemasangan LibreOffice otomatis (+ Visual C++ Redistributable) di Tahap 17C; `update.bat` dan `pasang.bat` di Tahap 17D.

## Tahap 17C - LibreOffice dan Visual C++ Otomatis

- `launcher\siapkan-libreoffice.ps1` dipanggil `start.bat` sesudah `siapkan-aplikasi.php` dan sebelum `cek-awal.php`. TIDAK fatal (tanpa pemeriksaan errorlevel): bila gagal, aplikasi tetap jalan (Word jadi, PDF tidak) dan `cek-awal.php` menampilkan peringatan LibreOffice.
- Urutan: (1) lewati bila ada `storage\app\lewati-libreoffice.txt` atau `.env` belum ada; (2) selesai senyap bila `LIBREOFFICE_BINARY` di `.env` menunjuk berkas yang ada; (3) cari di `%ProgramFiles%` dan `%ProgramFiles(x86)%` (`LibreOffice\program\soffice.exe`) lalu registri `HKLM\SOFTWARE\LibreOffice\UNO\InstallPath` (+WOW6432Node); bila ketemu, `.env` diperbarui; (4) bila tidak ada: baca daftar versi dari `https://download.documentfoundation.org/libreoffice/stable/`, unduh `LibreOffice_<versi>_Win_x86-64.msi` (versi terbaru dulu, maksimal 3 percobaan bila MSI belum tersedia) ke `runtime\libreoffice.msi`, periksa SHA256 dari berkas `.msi.sha256` resmi, pasang dengan `msiexec /i ... /qn /norestart` elevasi UAC (`-Verb RunAs`) dan properti `REGISTER_NO_MSO_TYPES=1 CREATEDESKTOPLINK=0 ISCHECKFORPRODUCTUPDATES=0 QUICKSTART=0`; kode keluar 0 dan 3010 dianggap berhasil; log di `runtime\libreoffice-pasang.log`; MSI dihapus setelah berhasil.
- `.env` diisi `LIBREOFFICE_BINARY='<path>'` (kutip TUNGGAL).
- Windows 32-bit: LibreOffice tidak dipasang otomatis (peringatan).
- `launcher\siapkan-php.ps1` diperbarui: `Test-Php` mengembalikan ok/vc/gagal; bila `php.exe` gagal start dengan kode DLL/fungsi tidak ditemukan (0xC0000135/0xC0000139), `Install-VcRedist` mengunduh `https://aka.ms/vs/17/release/vc_redist.x64.exe`, memasangnya `/install /quiet /norestart` elevasi UAC (kode 0, 1638, 3010 dianggap berhasil), lalu PHP diuji ulang satu kali. Ditambah pemeriksaan Windows 64-bit.
- Perbaikan bug 17A: pembacaan keluaran `php -r` saat PHP gagal start tanpa keluaran menimbulkan error null; kini aman.
- Selesai: `update.bat`, `pasang.bat`, dan `launcher/perbarui.ps1` (Tahap 17D) — lihat Section 17D di bawah.

## Tahap 17D - Pemasangan dan Pembaruan dari GitHub

- Sumber: GitHub Releases repo `Richie-Syahputra/SK-Generator`. Rilis terbaru dicari lewat pengalihan `https://github.com/<pemilik>/<repo>/releases/latest` (tanpa API, tanpa batas laju); zip diambil dari `archive/refs/tags/<tag>.zip`. Pre-release dan draft TIDAK dianggap "terbaru". Cabang `main` hanya bila diminta eksplisit (`update.bat main` / `pasang.bat main`) dan tampil peringatan.
- `launcher\perbarui.ps1` = mesin (parameter: `-Versi`, `-Akar`, `-Pasang`, `-Paksa`, `-TanpaJalan`). `update.bat` menyalinnya ke `%TEMP%` lalu menjalankannya (agar aman menimpa dirinya sendiri), baris pemanggilan satu baris berakhir `exit /b`.
- Urutan pembaruan: validasi folder -> tentukan versi (bandingkan dengan `runtime\versi-terpasang.txt`; sama = tidak ada yang dikerjakan kecuali `-Paksa`) -> unduh + ekstrak + validasi berkas wajib (artisan, composer.json, composer.lock, start.bat) -> baru kemudian hentikan server (taskkill pada PID di `storage\app\server.pid`) -> cadangkan database (`database\backup-update-<tanggal>.sqlite`, simpan 5 terakhir) -> salin berkas -> bila `composer.lock` berubah, hapus `vendor\autoload.php` (memicu composer install oleh `siapkan-aplikasi.php` saat start.bat) -> catat versi -> jalankan `start.bat` (migrasi otomatis oleh `cek-awal.php`).
- TIDAK disentuh saat memperbarui: `.git`, `vendor`, `runtime`, `storage`, `bootstrap/cache`, `public/storage` (awalan jalur dari akar; jadi `public/vendor` TETAP diperbarui), serta berkas bernama `.env`, `*.sqlite`, `*.sqlite-journal`, `*.sqlite-wal`, `*.sqlite-shm`. Berkas yang dihapus di versi baru TIDAK otomatis dihapus dari klien.
- Mode pasang (`-Pasang`, dipakai `pasang.bat`): folder bawaan `C:\SK-Generator` (cadangan `%USERPROFILE%\SK-Generator` bila gagal dibuat dan folder tidak ditentukan); folder tujuan harus kosong/baru; bila sudah ada `artisan` di sana: tidak menimpa, arahkan ke `update.bat`. Membuat pintasan Desktop SK Generator, Berhenti SK Generator, Perbarui SK Generator.
- `pasang.bat` mandiri (bagian PowerShell setelah baris penanda `#PS-MULAI`): mencari tag rilis terbaru, mengunduh `launcher/perbarui.ps1` dari `raw.githubusercontent.com` pada tag yang sama, lalu menjalankannya dengan `-Pasang`. Untuk dibagikan sendirian, unggah `pasang.bat` dari PC pengelola sebagai lampiran (asset) di halaman Release (bukan lewat tautan raw: berkas di repo tersimpan LF).
- Cara merilis versi baru: (1) tes lulus dan uji manual selesai; (2) commit dan push ke `main`; (3) GitHub > Releases > Draft a new release > Tag baru berformat `v<mayor>.<minor>.<patch>` (mis. `v1.0.1`, tanpa spasi), target `main` > Publish release; (4) klien menjalankan `update.bat` atau pintasan "Perbarui SK Generator".
- Aturan perubahan data: pembaruan TIDAK menjalankan seeder. Perubahan struktur ATAU data pada database yang sudah dipakai harus lewat MIGRASI (otomatis dijalankan oleh `cek-awal.php` dengan cadangan). Seeder hanya untuk instalasi baru. Folder `storage` baru yang dibutuhkan versi berikutnya harus dibuat oleh aplikasi/`siapkan-aplikasi.php` (update tidak menyentuh `storage`).
- Klien lama (dipasang manual dari ZIP, belum punya `update.bat`): salin `update.bat` dan `launcher\perbarui.ps1` ke folder klien satu kali, lalu jalankan `update.bat`.
- Berkas bernama `.env` dan `*.sqlite` tidak pernah ditimpa; bila versi baru butuh kunci `.env` baru, kode harus punya nilai bawaan.
