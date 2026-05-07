#!/bin/bash

# ============================================================
# Script Deploy - Laravel Absensi
# Server: VPS / aaPanel
# Jalankan: bash deploy.sh
# ============================================================

APP_PATH="$(cd "$(dirname "$0")" && pwd)"
WEB_USER="www"
GITHUB_OWNER="lutfifuadi"
GITHUB_REPO="absensi-digital"

echo "=========================================="
echo "  Deploy Laravel Absensi - VPS"
echo "=========================================="
echo "  Path terdeteksi: $APP_PATH"
echo "=========================================="

# Pastikan dijalankan dari direktori aplikasi
cd "$APP_PATH" || { echo "[ERROR] Path tidak ditemukan: $APP_PATH"; exit 1; }

# Muat variabel dari .env
if [ -f ".env" ]; then
    export $(grep -v '^#' .env | xargs)
fi

# ----------------------------------------------------------
# 1. Pull kode terbaru dari Git
# ----------------------------------------------------------
echo ""
echo "[1/8] Pull kode terbaru dari Git..."

if [ -n "$GITHUB_TOKEN" ]; then
    echo "[INFO] Menggunakan GITHUB_TOKEN untuk autentikasi Git..."
    # Update remote URL agar menggunakan token (jika menggunakan HTTPS)
    REMOTE_URL=$(git remote get-url origin)
    if [[ $REMOTE_URL == https://github.com* ]]; then
        NEW_URL="https://$GITHUB_TOKEN@${REMOTE_URL#https://}"
        git remote set-url origin "$NEW_URL"
    fi
fi

git pull origin main
if [ $? -ne 0 ]; then
    echo "[ERROR] Git pull gagal. Periksa koneksi, token, atau konflik."
    exit 1
fi

# ----------------------------------------------------------
# 2. Install / update Composer dependencies
# ----------------------------------------------------------
echo ""
echo "[2/8] Install Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# ----------------------------------------------------------
# 3. Download public/build dari GitHub Release terbaru
# ----------------------------------------------------------
echo ""
echo "[3/8] Download public/build dari GitHub Release..."

AUTH_HEADER=""
WGET_HEADER=""
if [ -n "$GITHUB_TOKEN" ]; then
    AUTH_HEADER="-H \"Authorization: token $GITHUB_TOKEN\""
    WGET_HEADER="--header=\"Authorization: token $GITHUB_TOKEN\""
    echo "[INFO] Menggunakan GITHUB_TOKEN untuk autentikasi API."
fi

LATEST_URL=$(curl -s $AUTH_HEADER "https://api.github.com/repos/$GITHUB_OWNER/$GITHUB_REPO/releases/latest" \
    | grep "browser_download_url" \
    | grep "absensi-siap-pakai.zip" \
    | cut -d '"' -f 4)

if [ -n "$LATEST_URL" ]; then
    echo "[INFO] Mengunduh: $LATEST_URL"
    if [ -n "$GITHUB_TOKEN" ]; then
        ASSET_ID=$(curl -s $AUTH_HEADER "https://api.github.com/repos/$GITHUB_OWNER/$GITHUB_REPO/releases/latest" \
            | grep -B 1 "absensi-siap-pakai.zip" \
            | grep "\"id\":" \
            | head -n 1 \
            | cut -d ':' -f 2 \
            | tr -d ' ,')
        
        wget -q $WGET_HEADER --header="Accept: application/octet-stream" \
            -O /tmp/absensi-siap-pakai.zip \
            "https://api.github.com/repos/$GITHUB_OWNER/$GITHUB_REPO/releases/assets/$ASSET_ID"
    else
        wget -q -O /tmp/absensi-siap-pakai.zip "$LATEST_URL"
    fi

    if [ $? -eq 0 ]; then
        rm -rf public/build
        unzip -o /tmp/absensi-siap-pakai.zip 'public/build/*' -d "$APP_PATH" > /dev/null
        rm /tmp/absensi-siap-pakai.zip
        echo "[OK] public/build berhasil diperbarui."
    else
        echo "[WARN] Gagal mengunduh build asset. public/build tetap menggunakan versi sebelumnya."
    fi
else
    echo "[WARN] Tidak ada release ditemukan, public/build tetap menggunakan versi sebelumnya."
fi

# ----------------------------------------------------------
# 4. Salin .env jika belum ada & update GitHub config
# ----------------------------------------------------------
echo ""
echo "[4/8] Cek file .env..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
    echo "[INFO] File .env dibuat dari .env.example. Harap sesuaikan konfigurasi database!"
else
    echo "[OK] File .env sudah ada."
fi

# Pastikan GITHUB_REPO_OWNER & GITHUB_REPO_NAME selalu terkini di .env
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

# ----------------------------------------------------------
# 5. Jalankan migrasi database
# ----------------------------------------------------------
echo ""
echo "[6/8] Migrasi database..."
php artisan migrate --force

# ----------------------------------------------------------
# 5b. Publish Livewire assets & pastikan storage symlink ada
# ----------------------------------------------------------
echo ""
echo "[5b] Publish Livewire assets..."
php artisan livewire:publish --assets --force

if [ ! -L "public/storage" ]; then
    echo "[INFO] Membuat symlink storage..."
    php artisan storage:link
fi

# ----------------------------------------------------------
# 6. Clear & cache ulang config
# ----------------------------------------------------------
echo ""
echo "[7/8] Optimasi Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ----------------------------------------------------------
# 7. Perbaiki permission folder
# ----------------------------------------------------------
echo ""
echo "[8/8] Set permission folder storage & bootstrap..."
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache public/vendor
chmod -R 775 storage bootstrap/cache

# ----------------------------------------------------------
# Selesai
# ----------------------------------------------------------
echo ""
echo "=========================================="
echo "  Deploy Selesai!"
echo "=========================================="
