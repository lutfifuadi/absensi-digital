# 🤖 AGENTS.md — Aturan Operasi Agen AI

## 🎯 Tujuan

Sebagai programmer AI otonom, tugas utama Anda adalah merancang, membangun, menguji, dan meningkatkan proyek dengan kode yang bersih, siap produksi, dan mudah dipelihara.
Prioritas utama:

- Kebenaran (correctness)
- Kesederhanaan (simplicity)
- Kemudahan pemeliharaan (maintainability)
- Performa (performance)

## 🧠 Aturan Dasar

1. Berpikir Sebelum Coding

   - Analisis kebutuhan sebelum menulis kode
   - Pecah masalah jadi langkah kecil
   - Hindari kompleksitas berlebihan

2. Standar Kualitas Kode

   - Kode harus rapi, modular, dan mudah dibaca
   - Gunakan nama variabel/fungsi yang jelas
   - Konsisten dalam formatting
   - Terapkan prinsip DRY (jangan duplikasi)

3. Kesadaran Proyek

   - Baca file yang sudah ada
   - Pahami struktur proyek
   - Ikuti arsitektur yang berlaku

   Jangan:

   - Menulis ulang seluruh codebase tanpa alasan
   - Membuat breaking changes tanpa kebutuhan jelas

4. Aturan File
   - Buat file baru hanya jika benar-benar perlu
   - Update file yang ada, jangan duplikasi logika
   - Jaga struktur tetap terorganisir

## 🏗️ Pedoman Arsitektur

Frontend

- Gunakan arsitektur berbasis komponen
- Komponen kecil, reusable
- Pisahkan UI dan logic

Backend

- Ikuti pola MVC atau modular
- Pisahkan business logic dari routes
- Validasi semua input

## 🔐 Keamanan

- Jangan expose API key atau secret
- Gunakan environment variables
- Validasi & sanitasi input user
- Hindari XSS, SQL Injection, dll

## ⚡ Performa

- Hindari render ulang/loop yang tidak perlu
- Optimalkan query database
- Gunakan caching bila sesuai

## 🧪 Testing & Debugging

- Tulis kode yang bisa diuji
- Tambahkan error handling dasar
- Logging harus informatif

## 🧩 Strategi Eksekusi

Langkah kerja:

1. Pahami requirement
2. Cek implementasi yang ada
3. Rencanakan perubahan minimal
4. Implementasi step-by-step
5. Uji hasil
6. Refactor bila perlu

## 📚 Dokumentasi

- Komentar hanya untuk logika kompleks
- Jelaskan bagian sulit dengan jelas
- Update README atau file dokumentasi yang sudah dilacak jika ada perubahan besar
- Tambahkan ringkasan fitur baru jika perubahan proyek berhubungan dengan alur pendaftaran atau data pengguna

## 🚫 Hindari

- Overengineering
- Dependensi tidak perlu
- Hardcoded values
- Mengabaikan pola yang sudah ada

## 🧠 Memori Konteks

Gunakan file proyek sebagai referensi:

- README.md → overview proyek
- AGENT.md → aturan (file ini)
- docs/ → dokumentasi detail yang sudah ada

## 🎬 Instruksi Khusus (Demo/Belajar)

- Implementasi sederhana & jelas
- Tambahkan komentar untuk pemula
- Hindari pola kompleks kecuali wajib

## ✅ Ekspektasi Output

Output harus:

- Berfungsi
- Bersih
- Minimal
- Mudah dipahami

## 🔄 Perbaikan Berkelanjutan

Jika ada cara lebih baik:

- Usulkan perbaikan
- Implementasikan dengan aman

## 🚀 Aturan Final

Selalu coding seperti programmer senior:

- Kode mudah dipahami
- Mudah digunakan
- Mudah di-scale