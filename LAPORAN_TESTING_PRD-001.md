# LAPORAN TESTING - PRD-001 Guide-Driven AI Knowledge System

**Tester:** Kang Asep (QA & Bug Hunter)  
**Tanggal:** 18 Juli 2026  
**Lingkungan:** 
- Backend: Laravel 11, PHP 8.2
- Database: MariaDB (produksi), SQLite (testing)
- AI: Google Gemini API (gemini-3-flash-preview)

---

## Ringkasan

| Metrik | Value |
|--------|-------|
| Total test case | 27 |
| Passed | 27 |
| Failed | 0 |
| Bugs Found | 4 |
| Critical | 1 |
| Major | 2 |
| Minor | 1 |

---

## A. Automated Test Results (PHPUnit)

### A.1 Unit Test: `GeminiServiceTest` (3 test cases)

| # | Test Case | Skenario | Status | Catatan |
|---|-----------|----------|--------|---------|
| 1 | `test_build_dynamic_system_instruction_contains_nama_lembaga_from_database` | Nama lembaga ada di database → instruction mengandung "Asisten {nama_lembaga}" | ✅ | Verifikasi bahwa `buildDynamicSystemInstruction()` membaca `nama_lembaga` dari tabel `pengaturan` dan menyisipkannya ke instruction |
| 2 | `test_build_dynamic_system_instruction_fallback_to_app_name_when_nama_lembaga_null` | Nama lembaga null → fallback ke `config('app.name')` | ✅ | Verifikasi fallback behavior ketika `nama_lembaga` tidak diset |
| 3 | `test_build_dynamic_system_instruction_mentions_user_role` | Role user berbeda → instruction menyebut role yang sesuai (guru, super_admin, siswa) | ✅ | Verifikasi bahwa role user disebutkan dalam instruction ("Role User Saat Ini: **{role}**") |

### A.2 Feature Test: `AiChatTest` (24 test cases)

#### Route Middleware (7 tests)

| # | Test Case | Skenario | Status | Catatan |
|---|-----------|----------|--------|---------|
| 4 | `test_super_admin_can_access_ai_chat_route` | Super admin buka `/admin/ai-chat` | ✅ | Middleware lulus (bukan 302/403) |
| 5 | `test_guru_can_access_ai_chat_route` | Guru buka `/admin/ai-chat` | ✅ | Middleware lulus |
| 6 | `test_siswa_can_access_ai_chat_route` | Siswa buka `/admin/ai-chat` | ✅ | Middleware lulus |
| 7 | `test_orang_tua_can_access_ai_chat_route` | Orang tua buka `/admin/ai-chat` | ✅ | Middleware lulus |
| 8 | `test_unauthenticated_user_cannot_access_ai_chat_route` | User tanpa login buka `/admin/ai-chat` | ✅ | Redirect ke login (302) |
| 9 | `test_super_admin_can_access_ai_chat_send_route` | Super admin POST ke `/admin/ai-chat/send` | ✅ | Middleware lulus |
| 10 | `test_siswa_can_access_ai_chat_send_route` | Siswa POST ke `/admin/ai-chat/send` | ✅ | Middleware lulus |

#### Tool `cari_panduan` (4 tests)

| # | Test Case | Skenario | Status | Catatan |
|---|-----------|----------|--------|---------|
| 11 | `test_tool_cari_panduan_with_valid_keyword_returns_max_3_results` | Keyword valid → return array max 3 hasil | ✅ | Membatasi hasil maksimal 3 guide |
| 12 | `test_tool_cari_panduan_without_results_returns_not_found` | Keyword tidak ditemukan → return "Tidak ditemukan" | ✅ | Mengembalikan pesan yang sesuai |
| 13 | `test_tool_cari_panduan_for_siswa_only_returns_siswa_guides` | Role siswa → hanya return guide dengan `role_target` mengandung 'siswa' atau 'public' | ✅ | Filter role bekerja dengan benar |
| 14 | `test_tool_cari_panduan_rejects_short_keyword` | Keyword 1 karakter → return error minimal 2 karakter | ✅ | Validasi panjang keyword berfungsi |

#### Tool `get_fitur_sistem` (2 tests)

| # | Test Case | Skenario | Status | Catatan |
|---|-----------|----------|--------|---------|
| 15 | `test_tool_get_fitur_sistem_returns_fitur_for_guru` | Role guru → return fitur untuk guru | ✅ | Hanya menampilkan kategori yang memiliki guide untuk guru |
| 16 | `test_tool_get_fitur_sistem_returns_fitur_for_siswa` | Role siswa → return fitur untuk siswa | ✅ | Guide guru tidak muncul untuk siswa |

#### Tiered Access (3 tests)

| # | Test Case | Skenario | Status | Catatan |
|---|-----------|----------|--------|---------|
| 17 | `test_tier_1_siswa_can_only_access_cari_panduan_and_get_fitur_sistem` | Role siswa: `cari_panduan` ✅, `get_fitur_sistem` ✅, `update_siswa` ❌, `get_siswa` ❌ | ✅ | Tier 1 hanya punya akses panduan dan fitur |
| 18 | `test_tier_2_guru_can_access_read_tools_but_not_update_tools` | Role guru: `cari_panduan` ✅, `get_siswa` ✅, `statistik_data` ✅, `update_siswa` ❌ | ✅ | Tier 2 read-only, update ditolak |
| 19 | `test_tier_3_super_admin_can_access_all_tools` | Role super_admin: semua tool bisa diakses | ✅ | Tier 3 full access |

#### Chat History Regression (5 tests)

| # | Test Case | Skenario | Status | Catatan |
|---|-----------|----------|--------|---------|
| 20 | `test_chat_history_is_saved_and_retrieved` | Chat log disimpan → bisa diambil via history API | ✅ | Riwayat chat tersimpan dan bisa diambil |
| 21 | `test_clear_chat_deletes_all_history` | Clear chat → semua history terhapus | ✅ | Fungsi clear bekerja |
| 22 | `test_chat_history_is_user_specific` | History user A tidak tercampur dengan user B | ✅ | Isolasi per user berfungsi |
| 23 | `test_send_message_validates_required_message` | Kirim pesan kosong → validation error | ✅ | Validasi `required` berfungsi |
| 24 | `test_send_message_validates_max_length` | Kirim pesan >2000 karakter → validation error | ✅ | Validasi `max:2000` berfungsi |

#### Tool Definitions Filtering (3 tests)

| # | Test Case | Skenario | Status | Catatan |
|---|-----------|----------|--------|---------|
| 25 | `test_get_tool_definitions_for_siswa_only_contains_basic_tools` | `getToolDefinitions('siswa')` hanya berisi `cari_panduan` dan `get_fitur_sistem` | ✅ |
| 26 | `test_get_tool_definitions_for_guru_contains_read_tools` | `getToolDefinitions('guru')` berisi read tools, tanpa update tools | ✅ |
| 27 | `test_get_tool_definitions_for_super_admin_contains_all_tools` | `getToolDefinitions('super_admin')` berisi semua tool | ✅ |

---

## B. Database & Migration Verification

| Item | Status | Detail |
|------|--------|--------|
| FULLTEXT INDEX di tabel `guides` | ✅ | Index `guides_title_content_fulltext` bertipe FULLTEXT pada kolom (title, content) |
| API Keys di tabel `pengaturan` | ✅ | Key `gemini_api_keys`: 9 keys tersimpan. Key `gemini_api_key`: ada (plain text) |
| `nama_lembaga` di pengaturan | ✅ | "MAN 1 Kota Bandung" |
| `nama_sekolah` di pengaturan | ✅ | "MAN 1 Kota Bandung" |

---

## C. Bugs / Issues Found

### Bug #1 — CRITICAL: Extra `@endif` di FloatingChat Blade View

**Lokasi:** `resources/views/livewire/admin/floating-chat.blade.php` (line 431)

**Deskripsi:**  
Terdapat `@endif` ekstra (line 431) yang tidak memiliki pasangan `@if`. Ini menyebabkan error `syntax error, unexpected token "endif"` saat view di-render. Akibatnya halaman `/admin/ai-chat` tidak bisa diakses oleh role manapun (500 error).

**Dampak:**  
- FloatingChat tidak bisa dirender sama sekali
- Halaman admin ai-chat tidak bisa dibuka
- Semua role mengalami error saat mengakses chat

**Rekomendasi:**  
Hapus `@endif` ekstra di line 431 pada `resources/views/livewire/admin/floating-chat.blade.php`.

---

### Bug #2 — MAJOR: Livewire Components Tidak Melewatkan Role ke `getToolDefinitions()`

**Lokasi:**
- `app/Livewire/Admin/FloatingChat.php` line 145
- `app/Livewire/Admin/AiChat.php` line 141

**Deskripsi:**  
Kedua Livewire component memanggil `$gemini->getToolDefinitions()` tanpa parameter role. Akibatnya Gemini hanya menerima tool definitions untuk tier 1 (`cari_panduan`, `get_fitur_sistem`) untuk **semua user**, termasuk super_admin dan guru yang seharusnya punya akses lebih.

Sebaliknya, `AiChatController.php` (HTTP controller untuk API endpoint) sudah benar memanggil `$gemini->getToolDefinitions($userRole)`.

**Dampak:**  
- Super admin yang menggunakan FloatingChat atau halaman AiChat (Livewire) hanya akan melihat tool tier 1
- Guru tidak bisa menggunakan tool read-only (get_siswa, statistik_data) dari Livewire chat
- Fitur tiered access tidak berfungsi optimal di Livewire components

**Rekomendasi:**  
Ubah pemanggilan menjadi `$gemini->getToolDefinitions($user->role)` pada kedua file.

---

### Bug #3 — MAJOR: FloatingChat Menggunakan `sendWithTools` Tanpa Tool Definitions Sesuai Role

**Lokasi:** `app/Livewire/Admin/FloatingChat.php` line 145-146

**Deskripsi:**  
Selain bug #2 (tidak passing role ke `getToolDefinitions`), FloatingChat juga menggunakan:
```php
$tools = $gemini->getToolDefinitions();
$response = $gemini->sendWithTools($userMessage, $tools, $history, $user->role);
```
Tool definitions sudah terbatas ke tier 1, tapi `sendWithTools` menerima parameter `$user->role`. Akibatnya terjadi inkonsistensi: Gemini hanya dikirim tool tier 1, tapi backend mengeksekusi berdasarkan role asli. Guru/super_admin tidak akan bisa memanggil tool tier 2/3 karena Gemini tidak tahu tool tersebut ada.

**Rekomendasi:**  
Pass `$user->role` ke `getToolDefinitions()` agar tool definitions yang dikirim ke Gemini sesuai dengan role user.

---

### Bug #4 — MINOR: Inconsistensi Key `nama_sekolah` vs `nama_lembaga`

**Lokasi:**
- `app/Livewire/Admin/FloatingChat.php` line 60 — menggunakan key `nama_sekolah`
- `app/Livewire/Admin/AiChat.php` line 61 — menggunakan key `nama_sekolah`
- `app/Services/GeminiService.php` line 232 — menggunakan key `nama_lembaga`

**Deskripsi:**  
Header chat menggunakan `nama_sekolah` untuk menampilkan nama sekolah, sementara AI instruction menggunakan `nama_lembaga`. Saat ini kedua nilai sama ("MAN 1 Kota Bandung"), tapi jika suatu saat admin mengubah salah satu saja, akan terjadi ketidakcocokan antara nama yang ditampilkan di header dan nama yang disebut AI.

**Dampak:**  
Potensi inkonsistensi tampilan di masa depan. Tidak ada dampak saat ini karena nilainya sama.

**Rekomendasi:**  
Konsistenkan penggunaan key. Pilih salah satu (`nama_lembaga` sudah digunakan di system instruction, sebaiknya Livewire components juga menggunakan `nama_lembaga` untuk konsistensi dengan AI).

---

## D. Manual Testing — Simulasi

Karena Bug #1 menghalangi render halaman chat, manual testing via browser tidak dapat dilakukan sepenuhnya. Berikut hasil simulasi berdasarkan analisis kode:

### D.1 Floating Chat — Semua Role
| Skenario | Hasil | Catatan |
|----------|-------|---------|
| Login sebagai siswa → floating chat muncul | ❌ Tidak bisa di-test | Terhalang Bug #1 |
| Login sebagai guru → floating chat muncul | ❌ Tidak bisa di-test | Terhalang Bug #1 |
| Login sebagai orang_tua → floating chat muncul | ❌ Tidak bisa di-test | Terhalang Bug #1 |
| Header chat menampilkan nama sekolah yang benar | ❌ Tidak bisa di-test | Bug #1 |
| Chip cepat sesuai role | ❌ Tidak bisa di-test | Bug #1 |

### D.2 Kirim Pesan ke AI
| Skenario | Hasil | Catatan |
|----------|-------|---------|
| "Bagaimana cara absen?" → AI merespon step-by-step | ⚠️ Simulasi | Logic `cari_panduan` sudah terverifikasi via test #11-14 |
| "Apa saja fitur yang tersedia?" → daftar fitur sesuai role | ⚠️ Simulasi | Logic `get_fitur_sistem` sudah terverifikasi via test #15-16 |
| "Tolong tambahkan data siswa baru" (sebagai siswa) → ditolak | ⚠️ Simulasi | Tier access sudah terverifikasi via test #17-19 |

### D.3 Verifikasi Nama AI
| Skenario | Hasil | Catatan |
|----------|-------|---------|
| Ubah `nama_lembaga` di pengaturan → header chat berubah | ❌ Tidak bisa di-test | Bug #1, tapi system instruction logic sudah terverifikasi via test #1-3 |

---

## E. Regression Test

### E.1 Fungsi Chat yang Sudah Ada

| Skenario | Hasil | Catatan |
|----------|-------|---------|
| Admin bisa CRUD siswa via chat (Tier 3) | ⚠️ Terbatas | Logic sudah terverifikasi via test #19 (tier 3 access all tools). Tapi untuk Livewire components, Bug #2 membatasi tool yang dikirim ke Gemini |
| Riwayat chat masih tersimpan | ✅ | Terverifikasi via test #20-22 |
| Clear chat masih berfungsi | ✅ | Terverifikasi via test #21 |
| Chat history user-specific | ✅ | Terverifikasi via test #22 |
| Validasi message (required, max length) | ✅ | Terverifikasi via test #23-24 |

### E.2 Database Regression

| Item | Hasil |
|------|-------|
| FULLTEXT INDEX di `guides` | ✅ Aman (migration sudah jalan) |
| API keys di `pengaturan` | ✅ Masih ada |
| `chat_logs` table tidak berubah | ✅ Tidak ada perubahan migration |

---

## F. Kesimpulan

**Status: LULUS BERSYARAT (Conditional Pass)**

### Ringkasan
- **27 test otomatis**: ✅ 27 passed (100%)
- **Database check**: ✅ FULLTEXT index dan API keys terverifikasi
- **Bug ditemukan**: 4 bugs (1 critical, 2 major, 1 minor)

### Syarat Kelulusan
Aplikasi **TIDAK LULOS** untuk production deployment sebelum Bug #1 diperbaiki, karena halaman chat tidak bisa diakses sama sekali oleh user manapun.

### Rekomendasi Prioritas Perbaikan
1. **🔥 Critical:** Fix Bug #1 (extra `@endif`) — agar chat bisa dirender
2. **🔥 Major:** Fix Bug #2 & #3 (role tidak di-pass ke `getToolDefinitions`) — agar tiered access berfungsi di Livewire
3. **📋 Minor:** Fix Bug #4 (konsistensi key `nama_sekolah`/`nama_lembaga`) — untuk maintainability

### Catatan Tambahan
- Setelah Bug #1 diperbaiki, perlu dilakukan **regression test ulang** untuk manual testing via browser
- Perlu memastikan `Gemini API key` masih valid dan memiliki quota yang cukup untuk testing chat secara real-time
- Source attribution ("📖 Sumber: ...") sudah diimplementasikan di view dan metadata, tapi belum bisa diverifikasi secara end-to-end karena Bug #1
