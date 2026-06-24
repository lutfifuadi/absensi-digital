#!/bin/bash

# ============================================================
# Script Setup Server - One-Click Deploy
# Server: VPS / aaPanel (Ubuntu/Debian)
# Jalankan sebagai root: bash server-setup.sh
# ============================================================

APP_PATH="$(cd "$(dirname "$0")" && pwd)"
WEB_USER="www"
LOG_PATH="$APP_PATH/storage/logs"

echo "=========================================="
echo "  Server Setup - Laravel Presensi"
echo "=========================================="
echo "  Path aplikasi: $APP_PATH"
echo "  User web:      $WEB_USER"
echo "=========================================="

# ----------------------------------------------------------
# 1. Cek & Install Tools
# ----------------------------------------------------------
echo ""
echo "[1/6] Cek & Install Tools..."

echo "  - git ........... $(which git 2>/dev/null || echo 'TIDAK ADA')"
echo "  - composer ...... $(which composer 2>/dev/null || echo 'TIDAK ADA')"
echo "  - mysqldump ..... $(which mysqldump 2>/dev/null || echo 'TIDAK ADA')"
echo "  - php ........... $(which php 2>/dev/null || echo 'TIDAK ADA')"

TOOLS_MISSING=0

if ! which git &>/dev/null; then
    echo "  >> Install git..."
    apt install git -y && echo "  [OK] git terinstall" || { echo "  [FAIL]"; TOOLS_MISSING=1; }
fi

if ! which composer &>/dev/null; then
    echo "  >> Install composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
    if which composer &>/dev/null; then
        echo "  [OK] composer terinstall"
    else
        echo "  [FAIL]"
        TOOLS_MISSING=1
    fi
fi

if ! which mysqldump &>/dev/null; then
    echo "  >> Install mysql-client..."
    apt install mysql-client -y && echo "  [OK] mysqldump terinstall" || { echo "  [FAIL]"; TOOLS_MISSING=1; }
fi

if ! which php &>/dev/null; then
    echo "  [ERROR] PHP tidak terinstall! Install PHP dulu."
    TOOLS_MISSING=1
fi

if [ $TOOLS_MISSING -eq 1 ]; then
    echo "  [WARN] Beberapa tools gagal diinstall. Cek log di atas."
fi

# Cek PHP extensions yang dibutuhkan
echo ""
echo "  PHP modules check:"
php -m | grep -i pcntl &>/dev/null && echo "  - pcntl ......... OK" || echo "  - pcntl ......... TIDAK ADA (required untuk queue)"
php -m | grep -i mysqli &>/dev/null && echo "  - mysqli ........ OK" || echo "  - mysqli ........ TIDAK ADA"
php -m | grep -i pdo_mysql &>/dev/null && echo "  - pdo_mysql ..... OK" || echo "  - pdo_mysql ..... TIDAK ADA"
php -m | grep -i mbstring &>/dev/null && echo "  - mbstring ...... OK" || echo "  - mbstring ...... TIDAK ADA"
php -m | grep -i xml &>/dev/null && echo "  - xml ........... OK" || echo "  - xml ........... TIDAK ADA"
php -m | grep -i curl &>/dev/null && echo "  - curl .......... OK" || echo "  - curl .......... TIDAK ADA"
php -m | grep -i gd &>/dev/null && echo "  - gd ............ OK" || echo "  - gd ............ TIDAK ADA"
php -m | grep -i zip &>/dev/null && echo "  - zip ........... OK" || echo "  - zip ........... TIDAK ADA"
php -m | grep -i bcmath &>/dev/null && echo "  - bcmath ........ OK" || echo "  - bcmath ........ TIDAK ADA"
php -m | grep -i intl &>/dev/null && echo "  - intl .......... OK" || echo "  - intl .......... TIDAK ADA"

# ----------------------------------------------------------
# 2. Setup Queue Worker (Supervisor)
# ----------------------------------------------------------
echo ""
echo "[2/6] Setup Queue Worker (Supervisor)..."

if command -v supervisorctl &>/dev/null; then
    echo "  Supervisor ditemukan, membuat config..."

    mkdir -p "$LOG_PATH"

    cat > /etc/supervisor/conf.d/laravel-worker.conf <<EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_PATH/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$WEB_USER
numprocs=2
redirect_stderr=true
stdout_logfile=$LOG_PATH/worker.log
stopwaitsecs=360
EOF

    echo "  Config supervisor dibuat di /etc/supervisor/conf.d/laravel-worker.conf"

    supervisorctl reread
    supervisorctl update
    supervisorctl start laravel-worker:*

    echo ""
    echo "  Status queue worker:"
    supervisorctl status laravel-worker:*
else
    echo "  [WARN] Supervisor tidak ditemukan."
    echo "  Install dengan: apt install supervisor -y"
    echo "  Setelah install, jalankan ulang script ini."
fi

# ----------------------------------------------------------
# 3. Setup Scheduler (Cron)
# ----------------------------------------------------------
echo ""
echo "[3/6] Setup Scheduler (Cron)..."

CRON_JOB="* * * * * cd $APP_PATH && php artisan schedule:run >> /dev/null 2>&1"
EXISTING_CRON=$(crontab -l 2>/dev/null | grep -F "$APP_PATH/artisan schedule:run")

if [ -z "$EXISTING_CRON" ]; then
    echo "  Menambahkan cron job ke crontab..."
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "  [OK] Cron scheduler terdaftar."
else
    echo "  [OK] Cron scheduler sudah terdaftar."
fi

echo "  Cron saat ini:"
crontab -l 2>/dev/null | grep -F "artisan schedule:run"

# ----------------------------------------------------------
# 4. Cek PATH Environment untuk user www
# ----------------------------------------------------------
echo ""
echo "[4/6] Cek PATH Environment untuk user $WEB_USER..."

if id "$WEB_USER" &>/dev/null; then
    WWW_PATH=$(sudo -u "$WEB_USER" bash -c 'echo $PATH' 2>/dev/null)
    echo "  PATH user $WEB_USER: $WWW_PATH"

    # Cek apakah tools ada di PATH www
    for tool in git composer mysqldump php; do
        TOOL_PATH=$(sudo -u "$WEB_USER" bash -c "which $tool" 2>/dev/null)
        if [ -n "$TOOL_PATH" ]; then
            echo "  - $tool: $TOOL_PATH [OK]"
        else
            echo "  - $tool: TIDAK ADA di PATH www [WARN]"
        fi
    done
else
    echo "  User $WEB_USER tidak ditemukan."
fi

# ----------------------------------------------------------
# 5. Storage permissions
# ----------------------------------------------------------
echo ""
echo "[5/6] Set Storage Permissions..."

if [ -d "$APP_PATH/storage" ]; then
    chmod -R 775 "$APP_PATH/storage"
    chown -R "$WEB_USER":"$WEB_USER" "$APP_PATH/storage"
    echo "  [OK] Permission storage diatur: 775, owner: $WEB_USER"
else
    echo "  [WARN] Folder storage tidak ditemukan di $APP_PATH/storage"
    mkdir -p "$APP_PATH/storage"
    chmod -R 775 "$APP_PATH/storage"
    chown -R "$WEB_USER":"$WEB_USER" "$APP_PATH/storage"
    echo "  [OK] Folder storage dibuat & permission diatur"
fi

if [ -d "$APP_PATH/bootstrap/cache" ]; then
    chmod -R 775 "$APP_PATH/bootstrap/cache"
    chown -R "$WEB_USER":"$WEB_USER" "$APP_PATH/bootstrap/cache"
    echo "  [OK] Permission bootstrap/cache diatur"
fi

# ----------------------------------------------------------
# 6. Verifikasi Final
# ----------------------------------------------------------
echo ""
echo "[6/6] Verifikasi Final..."
echo ""

echo "=========================================="
echo "  RINGKASAN SETUP"
echo "=========================================="

echo ""
echo "--- Tools ---"
echo "  git ......... $(which git 2>/dev/null && echo 'OK' || echo 'MISSING')"
echo "  composer .... $(which composer 2>/dev/null && echo 'OK' || echo 'MISSING')"
echo "  mysqldump ... $(which mysqldump 2>/dev/null && echo 'OK' || echo 'MISSING')"
echo "  php ......... $(which php 2>/dev/null && echo 'OK' || echo 'MISSING')"
echo "  supervisor .. $(which supervisorctl 2>/dev/null && echo 'OK' || echo 'MISSING')"

echo ""
echo "--- Queue Worker ---"
if command -v supervisorctl &>/dev/null; then
    supervisorctl status laravel-worker:* 2>/dev/null || echo "  Status: TIDAK JALAN"
fi

echo ""
echo "--- Scheduler (Cron) ---"
crontab -l 2>/dev/null | grep -F "artisan schedule:run" && echo "  Status: TERDAFTAR" || echo "  Status: TIDAK TERDAFTAR"

echo ""
echo "--- Storage Permissions ---"
ls -ld "$APP_PATH/storage" 2>/dev/null | awk '{print "  storage: " $1 " " $3 ":" $4}'
ls -ld "$APP_PATH/bootstrap/cache" 2>/dev/null | awk '{print "  bootstrap/cache: " $1 " " $3 ":" $4}'

echo ""
echo "=========================================="
echo "  Setup Selesai!"
echo "=========================================="
echo ""
echo "Jika ada yang MISSING, perbaiki manual lalu jalankan ulang script."
echo ""
echo "Log worker: tail -f $LOG_PATH/worker.log"
echo "Restart worker: supervisorctl restart laravel-worker:*"
