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
    # Cara lebih aman membaca .env untuk menghindari error valid identifier
    set -a
    [ -f .env ] && . ./.env
    set +a
fi

# ----------------------------------------------------------
# 1. Pull kode terbaru dari Git
# ----------------------------------------------------------
echo ""
echo "[1/6] Pull kode terbaru dari Git..."

if [ -n "$GITHUB_TOKEN" ]; then
    echo "[INFO] Menggunakan GITHUB_TOKEN untuk autentikasi Git..."
    REMOTE_URL=$(git remote get-url origin)
    if [[ $REMOTE_URL == https://github.com* ]]; then
        # HTTPS: tinggal sisipkan token
        NEW_URL="https://$GITHUB_TOKEN@${REMOTE_URL#https://}"
        git remote set-url origin "$NEW_URL"
    elif [[ $REMOTE_URL == git@github.com* ]]; then
        # SSH: ubah ke HTTPS dengan token
        # Format: git@github.com:owner/repo.git -> https://token@github.com/owner/repo.git
        SSH_PATH="${REMOTE_URL#git@github.com:}"
        NEW_URL="https://$GITHUB_TOKEN@github.com/$SSH_PATH"
        git remote set-url origin "$NEW_URL"
        echo "[INFO] Remote URL diubah dari SSH ke HTTPS."
    else
        echo "[WARN] Remote URL tidak dikenal: $REMOTE_URL"
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
echo "[2/6] Install Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# ----------------------------------------------------------
# 3. Salin .env jika belum ada & update GitHub config
# ----------------------------------------------------------
echo ""
echo "[3/6] Cek file .env..."
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
# 4. Jalankan migrasi database
# ----------------------------------------------------------
echo ""
echo "[4/6] Migrasi database..."
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
# 5. Clear & cache ulang config
# ----------------------------------------------------------
echo ""
echo "[5/6] Optimasi Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ----------------------------------------------------------
# 6. Perbaiki permission folder (Full Project for Dashboard Update)
# ----------------------------------------------------------
echo ""
echo "[6/6] Set permission folder & ownership..."
# Pastikan seluruh folder dimiliki oleh user web agar Dashboard bisa update file
chown -R "$WEB_USER":"$WEB_USER" "$APP_PATH"
chmod -R 775 storage bootstrap/cache

# ----------------------------------------------------------
# Selesai
# ----------------------------------------------------------
echo ""
echo "=========================================="
echo "  Deploy Selesai!"
echo "=========================================="
