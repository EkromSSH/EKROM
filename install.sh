#!/usr/bin/env bash
# ==============================================================================
#  EKROM SHOP - One-Click Auto Installer for Debian / Ubuntu
# ==============================================================================

set -e

# Colors for terminal output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

clear
echo -e "${CYAN}${BOLD}"
echo "=============================================================="
echo "          🚀 EKROM SHOP AUTO INSTALLER SYSTEM                "
echo "=============================================================="
echo -e "${NC}"

# 1. Check Root Privileges
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] กรุณารันคำสั่งด้วยสิทธิ์ root (sudo -i หรือ root)${NC}"
    exit 1
fi

# 2. Detect IP
echo -e "${BLUE}[1/5] 🌐 กำลังตรวจสอบ IP Address...${NC}"
SERVER_IP=$(curl -s --max-time 5 https://api.ipify.org || curl -s --max-time 5 https://ifconfig.me || hostname -I | awk '{print $1}')
echo -e "${GREEN}✓ IP Address เครื่อง: ${SERVER_IP}${NC}"

# 3. Update system & Install Dependencies
echo -e "\n${BLUE}[2/5] 📦 กำลังติดตั้ง Dependencies (PHP, SQLite3, Nginx, Git, Curl)...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y >/dev/null 2>&1
apt-get install -y php-cli php-sqlite3 php-curl php-mbstring nginx git curl sqlite3 ufw >/dev/null 2>&1

if ! command -v php >/dev/null 2>&1; then
    echo -e "${RED}[ERROR] การติดตั้ง PHP ไม่สำเร็จ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต${NC}"
    exit 1
fi
echo -e "${GREEN}✓ ติดตั้ง Dependencies สำเร็จแล้ว${NC}"

# 4. Clone / Update Repository
echo -e "\n${BLUE}[3/5] 📥 กำลังดึงไฟล์ระบบ EKROM-Shop จาก GitHub...${NC}"
REPO_URL="https://github.com/EkromSSH/EKROM.git"
TARGET_DIR="/root/ekrom-shop"
IS_NEW_INSTALL=0

if [ -d "$TARGET_DIR/.git" ]; then
    echo -e "${YELLOW}พบโฟลเดอร์ระบบเดิม ทำการอัปเดตเวอร์ชันล่าสุด...${NC}"
    cd "$TARGET_DIR"
    # สำรองไฟล์ฐานข้อมูลเดิมไว้ก่อน เพื่อไม่ให้ข้อมูลสูญหายจากการอัปเดต
    if [ -f "$TARGET_DIR/database.sqlite" ]; then
        cp -f "$TARGET_DIR/database.sqlite" "/tmp/ekrom_db_preserve_$$.sqlite" 2>/dev/null || true
    fi
    git checkout HEAD -- database.sqlite 2>/dev/null || true
    git reset --hard HEAD >/dev/null 2>&1 || true
    git pull origin main >/dev/null 2>&1 || true
    # คืนค่าฐานข้อมูลเดิมกลับมาเสมอ
    if [ -f "/tmp/ekrom_db_preserve_$$.sqlite" ]; then
        cp -f "/tmp/ekrom_db_preserve_$$.sqlite" "$TARGET_DIR/database.sqlite"
        rm -f "/tmp/ekrom_db_preserve_$$.sqlite"
    fi
else
    if [ -d "$TARGET_DIR" ]; then
        mv "$TARGET_DIR" "${TARGET_DIR}_backup_$(date +%s)"
    fi
    git clone "$REPO_URL" "$TARGET_DIR" >/dev/null 2>&1
    IS_NEW_INSTALL=1
fi

cd "$TARGET_DIR"
chmod -R 775 "$TARGET_DIR"

# Initialize database or run migrations safely
echo -e "${YELLOW}กำลังเตรียมโครงสร้างฐานข้อมูล...${NC}"
if [ "$IS_NEW_INSTALL" -eq 1 ]; then
    # การติดตั้งใหม่ ล้างข้อมูลเก่าแล้วสร้างฐานข้อมูลเริ่มต้นที่สะอาด 100%
    rm -f "$TARGET_DIR/database.sqlite"
fi
php "$TARGET_DIR/init_db.php" >/dev/null 2>&1

# ปิดระบบ Turnstile ในการติดตั้งใหม่ เพื่อให้ผู้ใช้สามารถล็อกอินครั้งแรกได้ทันที
if [ "$IS_NEW_INSTALL" -eq 1 ]; then
    php -r "require_once '$TARGET_DIR/api/db.php'; \$db = get_db(); \$db->exec(\"INSERT OR REPLACE INTO system_settings (key, value) VALUES ('turnstile_settings', '{\\\"enabled\\\":0,\\\"site_key\\\":\\\"\\\",\\\"secret_key\\\":\\\"\\\"}')\");" >/dev/null 2>&1 || true
fi

chmod 666 "$TARGET_DIR/database.sqlite" 2>/dev/null || true
echo -e "${GREEN}✓ เตรียมไฟล์ระบบและฐานข้อมูลเรียบร้อยแล้ว${NC}"

# 5. Setup Systemd Service
echo -e "\n${BLUE}[4/5] ⚙️ ตั้งค่า Service ekrom-shop...${NC}"
cat << 'EOF' > /etc/systemd/system/ekrom-shop.service
[Unit]
Description=EKROM-Shop Web Server
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root/ekrom-shop
ExecStart=/usr/bin/php -S 0.0.0.0:8000
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable ekrom-shop >/dev/null 2>&1
systemctl restart ekrom-shop
echo -e "${GREEN}✓ เปิด Service สำเร็จ${NC}"

# 6. Setup Nginx Reverse Proxy (Port 80 -> 8000)
echo -e "\n${BLUE}[5/5] 🌐 กำลังตั้งค่า Nginx Port 80...${NC}"
systemctl stop apache2 >/dev/null 2>&1 || true
systemctl disable apache2 >/dev/null 2>&1 || true

mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled

cat << 'EOF' > /etc/nginx/sites-available/ekrom-shop
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;

    client_max_body_size 100M;

    # Block sensitive files
    location ~* (\.sqlite|\.db|\.git|\.env|\.sh|README\.md)$ {
        return 404;
    }
    location ~ /\. {
        return 404;
    }

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF

# Enable ekrom-shop site and remove default site safely
if [ -f /etc/nginx/sites-enabled/default ]; then
    rm -f /etc/nginx/sites-enabled/default
fi
ln -sf /etc/nginx/sites-available/ekrom-shop /etc/nginx/sites-enabled/ekrom-shop

if nginx -t >/dev/null 2>&1; then
    systemctl restart nginx
    echo -e "${GREEN}✓ เปิดการทำงาน Nginx สำเร็จ${NC}"
else
    echo -e "${YELLOW}⚠️ Nginx config warning กรุณาตรวจสอบ /etc/nginx/${NC}"
fi

# Firewall
if command -v ufw >/dev/null 2>&1; then
    ufw allow 80/tcp >/dev/null 2>&1 || true
    ufw allow 8000/tcp >/dev/null 2>&1 || true
fi

# Summary
sleep 1
echo ""
echo -e "${GREEN}${BOLD}=============================================================="
echo "    🎉 ติดตั้งระบบ EKROM SHOP สำเร็จเรียบร้อยแล้ว!"
echo "=============================================================="
echo -e "${NC}"
echo -e "  🌐 ${BOLD}ลิงก์เข้าสู่หน้าร้านค้า:${NC}"
echo -e "     👉 ${CYAN}${BOLD}http://${SERVER_IP}/${NC} (พอร์ต 80 แนะนำ)"
echo -e "     👉 ${CYAN}http://${SERVER_IP}:8000/${NC} (พอร์ตตรง)"
echo ""
echo -e "  🔑 ${BOLD}ข้อมูลบัญชีสำหรับเข้าใช้งาน:${NC}"
echo -e "     ---------------------------------------------------------"
echo -e "     ⚙️ ${YELLOW}${BOLD}ผู้ดูแลระบบ (Admin):${NC}"
echo -e "        Username : ${BOLD}admin${NC}"
echo -e "        Password : ${BOLD}admin123${NC}"
echo -e "        PIN Code : ${BOLD}123456${NC}"
echo -e "        URL หลังบ้าน: ${CYAN}http://${SERVER_IP}/admin-dash.php${NC}"
echo ""
echo -e "     🛡️ ${YELLOW}${BOLD}ตัวแทนจำหน่าย (Reseller):${NC}"
echo -e "        Username : ${BOLD}reseller${NC}"
echo -e "        Password : ${BOLD}reseller123${NC}"
echo ""
echo -e "     👤 ${YELLOW}${BOLD}ลูกค้าทั่วไป (Buyer):${NC}"
echo -e "        Username : ${BOLD}buyer${NC}"
echo -e "        Password : ${BOLD}buyer123${NC}"
echo -e "     ---------------------------------------------------------"
echo ""
echo -e "  📂 ตำแหน่งไฟล์ระบบ: ${BOLD}/root/ekrom-shop${NC}"
echo -e "  🛠️ ${BOLD}คำสั่งจัดการระบบ:${NC}"
echo -e "     - ตรวจสอบสถานะ: ${BOLD}systemctl status ekrom-shop${NC}"
echo -e "     - รีสตาร์ทระบบ : ${BOLD}systemctl restart ekrom-shop${NC}"
echo -e "     - ดู Log ระบบ : ${BOLD}journalctl -u ekrom-shop -f${NC}"
echo -e "${GREEN}${BOLD}==============================================================${NC}"
