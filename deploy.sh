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
echo "[1/8] Pull kode terbaru dari Git..."

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
echo "[2/8] Install Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# ----------------------------------------------------------
# 3. Download public/build dari GitHub Release terbaru
# ----------------------------------------------------------
echo ""
echo "[3/8] Download public/build dari GitHub Release..."

ASSET_FILE="absensi-siap-pakai.zip"
DOWNLOAD_OK=false

# --- Tentukan direktori temporary yang writable ---
TEMP_DIR=""
for dir in "/tmp" "$APP_PATH/storage/tmp" "$APP_PATH"; do
    if [ -w "$dir" ] || (mkdir -p "$dir" 2>/dev/null && [ -w "$dir" ]); then
        TEMP_DIR="$dir"
        break
    fi
done

if [ -z "$TEMP_DIR" ]; then
    echo "[ERROR] Tidak ada direktori writable untuk download."
    exit 1
fi

TEMP_FILE="$TEMP_DIR/$ASSET_FILE"

# Hapus file lama jika ada (mencegah permission denied karena ownership berbeda)
rm -f "$TEMP_FILE"

# --- Metode 1: gh CLI (paling reliable) ---
if command -v gh &>/dev/null; then
    echo "[INFO] Mencoba download dengan gh CLI..."
    TAG=$(gh release view --repo "$GITHUB_OWNER/$GITHUB_REPO" --json tagName --jq '.tagName' 2>/dev/null)
    if [ -n "$TAG" ]; then
        gh release download "$TAG" --repo "$GITHUB_OWNER/$GITHUB_REPO" \
            --pattern "$ASSET_FILE" --dir "$TEMP_DIR" --clobber 2>/dev/null
        if [ $? -eq 0 ] && [ -f "$TEMP_FILE" ]; then
            echo "[OK] Download berhasil via gh CLI (tag: $TAG)."
            DOWNLOAD_OK=true
        fi
    fi
fi

# --- Metode 2: Download URL publik (tanpa token) ---
if [ "$DOWNLOAD_OK" = false ]; then
    echo "[INFO] Mencoba download dari URL publik GitHub..."
    # Ambil tag release terbaru dari API (grep+cut, universal tanpa python/jq)
    if [ -n "$GITHUB_TOKEN" ]; then
        API_RESP=$(curl -s -H "Authorization: token $GITHUB_TOKEN" \
            "https://api.github.com/repos/$GITHUB_OWNER/$GITHUB_REPO/releases/latest")
    else
        API_RESP=$(curl -s "https://api.github.com/repos/$GITHUB_OWNER/$GITHUB_REPO/releases/latest")
    fi
    
    TAG=$(echo "$API_RESP" | grep '"tag_name"' | cut -d '"' -f 4)

    if [ -n "$TAG" ]; then
        DOWNLOAD_URL="https://github.com/$GITHUB_OWNER/$GITHUB_REPO/releases/download/$TAG/$ASSET_FILE"
        echo "[INFO] Mengunduh: $DOWNLOAD_URL"
        
        rm -f "$TEMP_FILE"
        if wget -q --timeout=60 -O "$TEMP_FILE" "$DOWNLOAD_URL"; then
            echo "[OK] Download berhasil via URL publik (tag: $TAG)."
            DOWNLOAD_OK=true
        else
            echo "[WARN] Gagal download dari URL publik."
        fi
    else
        echo "[WARN] Tidak dapat membaca tag release dari API."
    fi
fi

# --- Metode 3: Cari browser_download_url langsung dari API ---
if [ "$DOWNLOAD_OK" = false ] && [ -n "$API_RESP" ]; then
    echo "[INFO] Mencari URL download dari API response..."
    DOWNLOAD_URL=$(echo "$API_RESP" | grep "browser_download_url" | grep "$ASSET_FILE" | cut -d '"' -f 4 | head -1)
    
    if [ -n "$DOWNLOAD_URL" ]; then
        echo "[INFO] Mengunduh: $DOWNLOAD_URL"
        rm -f "$TEMP_FILE"
        wget -q --timeout=60 -O "$TEMP_FILE" "$DOWNLOAD_URL"
        if [ $? -eq 0 ]; then
            echo "[OK] Download berhasil via browser_download_url."
            DOWNLOAD_OK=true
        fi
    fi
fi

# --- Ekstrak jika berhasil download ---
if [ "$DOWNLOAD_OK" = true ] && [ -f "$TEMP_FILE" ]; then
    rm -rf public/build
    unzip -o "$TEMP_FILE" 'public/build/*' -d "$APP_PATH" > /dev/null 2>&1
    rm -f "$TEMP_FILE"
    echo "[OK] public/build berhasil diperbarui."
else
    echo "[WARN] Gagal mengunduh build asset. public/build tetap menggunakan versi sebelumnya."
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
# 7. Perbaiki permission folder (Full Project for Dashboard Update)
# ----------------------------------------------------------
echo ""
echo "[8/8] Set permission folder & ownership..."
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
