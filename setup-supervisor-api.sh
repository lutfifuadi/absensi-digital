#!/bin/bash

# ============================================================
# Script Setup Supervisor API - Laravel Presensi
# Mengaktifkan XML-RPC API Supervisor untuk kontrol dari web
# ============================================================

APP_PATH="$(cd "$(dirname "$0")" && pwd)"
SUPERVISOR_CONF="/etc/supervisor/supervisord.conf"

echo "=========================================="
echo "Setup Supervisor API - Laravel Presensi"
echo "  Path: $APP_PATH"
echo "=========================================="

# 1. Generate password
echo "[1/5] Generate password..."
PASSWORD=$(openssl rand -base64 32 2>/dev/null || date +%s | sha256sum | base64 | head -c 32)
echo "  Password: $PASSWORD"

# 2. Backup supervisord.conf
echo "[2/5] Backup supervisord.conf..."
cp $SUPERVISOR_CONF ${SUPERVISOR_CONF}.backup.$(date +%Y%m%d_%H%M%S)

# 3. Tambah konfigurasi API
echo "[3/5] Menambahkan konfigurasi API..."
if grep -q "\[inet_http_server\]" $SUPERVISOR_CONF; then
    echo "  API sudah aktif, update password..."
    sed -i "s/^password=.*/password=$PASSWORD/" $SUPERVISOR_CONF
else
    sed -i "1i\[inet_http_server]\nport=127.0.0.1:9001\nusername=supervisor_api\npassword=$PASSWORD\n" $SUPERVISOR_CONF
fi

# 4. Update .env
echo "[4/5] Update .env..."
if grep -q "SUPERVISOR_API_PASSWORD" $APP_PATH/.env; then
    sed -i "s/SUPERVISOR_API_PASSWORD=.*/SUPERVISOR_API_PASSWORD=$PASSWORD/" $APP_PATH/.env
else
    echo "SUPERVISOR_API_PASSWORD=$PASSWORD" >> $APP_PATH/.env
fi

# 5. Restart supervisor
echo "[5/5] Restart supervisor..."
systemctl restart supervisord

# 6. Test koneksi
echo "[6/5] Test koneksi..."
sleep 2
curl -s -u supervisor_api:$PASSWORD http://127.0.0.1:9001/ | head -5

echo ""
echo "=========================================="
echo "Setup Supervisor API Selesai!"
echo "=========================================="
echo "Password: $PASSWORD"
echo ""
echo "Cek status: supervisorctl status"
