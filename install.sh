#!/bin/bash

# ============================================================
# Script Instalasi Pertama - Laravel Absensi
# Server: VPS / aaPanel
# Jalankan SEKALI saat setup klien baru:
#   bash install.sh
# Setelah selesai, lanjutkan setup via browser: http://DOMAIN/install
# ============================================================

APP_PATH="$(cd "$(dirname "$0")" && pwd)"
WEB_USER="www"
GITHUB_OWNER="lutfifuadi"
GITHUB_REPO="absensi-digtal"

echo "=========================================="
echo "  Instalasi Laravel Absensi - VPS"
echo "=========================================="
echo "  Path terdeteksi: $APP_PATH"
echo "=========================================="

# Pastikan dijalankan dari direktori aplikasi
cd "$APP_PATH" || { echo "[ERROR] Path tidak ditemukan: $APP_PATH"; exit 1; }

# ----------------------------------------------------------
# 1. Persiapan folder wajib Laravel
# ----------------------------------------------------------
echo ""
echo "[1/6] Persiapan folder & permission..."
mkdir -p bootstrap/cache storage/framework/{sessions,views,cache/data} storage/logs
chmod -R 775 bootstrap/cache storage
echo "[OK] Folder siap."

# ----------------------------------------------------------
# 2. Install Composer dependencies
# ----------------------------------------------------------
echo ""
echo "[2/6] Install Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# ----------------------------------------------------------
# 2. Setup file .env
# ----------------------------------------------------------
echo ""
echo "[3/7] Setup file .env..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "[OK] File .env dibuat dari .env.example."
else
    echo "[OK] File .env sudah ada, dilewati."
fi

# Tulis GITHUB_REPO_OWNER & GITHUB_REPO_NAME ke .env
if grep -q "^GITHUB_REPO_OWNER=" .env; then
    sed -i "s|^GITHUB_REPO_OWNER=.*|GITHUB_REPO_OWNER=$GITHUB_OWNER|" .env
else
    echo "GITHUB_REPO_OWNER=$GITHUB_OWNER" >> .env
fi

if grep -q "^GITHUB_REPO_NAME=" .env; then
    sed -i "s|^GITHUB_REPO_NAME=.*|GITHUB_REPO_NAME=$GITHUB_REPO|" .env
else
    echo "GITHUB_REPO_NAME=$GITHUB_REPO" >> .env
fi
echo "[OK] GITHUB_REPO_OWNER=$GITHUB_OWNER, GITHUB_REPO_NAME=$GITHUB_REPO ditulis ke .env"

# ----------------------------------------------------------
# 3. Download public/build dari GitHub Release terbaru
# ----------------------------------------------------------
echo ""
echo "[4/7] Download public/build dari GitHub Release..."
LATEST_URL=$(curl -s "https://api.github.com/repos/$GITHUB_OWNER/$GITHUB_REPO/releases/latest" \
    | grep "browser_download_url" \
    | grep "absensi-siap-pakai.zip" \
    | cut -d '"' -f 4)

if [ -n "$LATEST_URL" ]; then
    echo "[INFO] Mengunduh: $LATEST_URL"
    wget -q -O /tmp/absensi-siap-pakai.zip "$LATEST_URL"
    if [ $? -eq 0 ]; then
        rm -rf public/build
        unzip -o /tmp/absensi-siap-pakai.zip 'public/build/*' -d "$APP_PATH" > /dev/null
        rm /tmp/absensi-siap-pakai.zip
        echo "[OK] public/build berhasil diekstrak."
    else
        echo "[WARN] Gagal mengunduh build asset. Lanjutkan instalasi, tapi tampilan mungkin rusak."
        echo "[WARN] Hubungi developer untuk mengirim build asset manual."
    fi
else
    echo "[WARN] Tidak ada release di GitHub. Hubungi developer untuk membuat release terlebih dahulu."
fi

# ----------------------------------------------------------
# 4. Publish Livewire assets
# ----------------------------------------------------------
echo ""
echo "[5/7] Publish Livewire assets..."
php artisan livewire:publish --assets --force
echo "[OK] Livewire assets dipublish ke public/vendor/livewire/"

# ----------------------------------------------------------
# 5. Buat symlink storage
# ----------------------------------------------------------
echo ""
echo "[6/7] Buat symlink storage..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo "[OK] Symlink public/storage dibuat."
else
    echo "[OK] Symlink public/storage sudah ada, dilewati."
fi

# ----------------------------------------------------------
# 6. Set permission & setup queue worker
# ----------------------------------------------------------
echo ""
echo "[7/7] Set permission & setup queue worker..."
chown -R "$WEB_USER":"$WEB_USER" "$APP_PATH"
chmod -R 755 "$APP_PATH"
chmod -R 775 storage bootstrap/cache

# Setup queue worker jika Supervisor tersedia
if command -v supervisorctl &> /dev/null; then
    echo "[INFO] Supervisor ditemukan, setup queue worker..."
    bash "$APP_PATH/setup-queue-worker.sh"
else
    echo ""
    echo "=========================================="
    echo "  [WARN] Supervisor tidak ditemukan!"
    echo "  Notifikasi WhatsApp tidak akan berjalan."
    echo "  Jalankan manual setelah install Supervisor:"
    echo "    bash $APP_PATH/setup-queue-worker.sh"
    echo "=========================================="
fi

# ----------------------------------------------------------
# Selesai
# ----------------------------------------------------------
echo ""
echo "=========================================="
echo "  Instalasi Selesai!"
echo "=========================================="
echo ""
echo "Langkah selanjutnya:"
echo "  Buka browser dan akses wizard instalasi:"
echo "  http://DOMAIN_KLIEN/install"
echo ""
echo "  Wizard akan memandu:"
echo "  - Konfigurasi database"
echo "  - Data sekolah / lembaga"
echo "  - Pembuatan akun admin"
echo ""
