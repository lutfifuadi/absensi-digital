#!/bin/bash

# ============================================================
# Script Setup Queue Worker - Laravel Presensi
# Server: aaPanel
# ============================================================

APP_PATH="$(cd "$(dirname "$0")" && pwd)"
LOG_PATH="$APP_PATH/storage/logs"
USER="www"

echo "=========================================="
echo "Setup Queue Worker - Laravel Presensi"
echo "  Path terdeteksi: $APP_PATH"
echo "=========================================="

# 1. Ganti ke path aplikasi
cd $APP_PATH

# 2. Clear cache dulu
echo "[1/5] Clearing cache..."
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear

# 3. Buat file supervisor config
echo "[2/5] Membuat config supervisor..."
cat > /etc/supervisor/conf.d/laravel-worker.conf <<EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_PATH/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$USER
numprocs=2
redirect_stderr=true
stdout_logfile=$LOG_PATH/worker.log
stopwaitsecs=360
EOF

# 4. Buat direktori log jika belum ada
echo "[3/5] Setup direktori log..."
mkdir -p $LOG_PATH

# 5. Register & start supervisor
echo "[4/5] Starting supervisor..."
supervisorctl reread
supervisorctl update
supervisorctl start laravel-worker

# 6. Cek status
echo "[5/5] Cek status..."
supervisorctl status

echo ""
echo "=========================================="
echo "Setup Selesai!"
echo "=========================================="
echo ""
echo "Cek logs:"
echo "  - Supervisor: supervisorctl status"
echo "  - Worker: tail -f $LOG_PATH/worker.log"
echo ""
echo "Commands berguna:"
echo "  - Restart worker: supervisorctl restart laravel-worker"
echo "  - Stop worker: supervisorctl stop laravel-worker"
echo "  - Start worker: supervisorctl start laravel-worker"