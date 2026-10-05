#!/usr/bin/env bash
# ==============================================================================
#  EKROM SHOP - Safe Installer on Port 8090 (Non-conflicting with VPN)
# ==============================================================================

set -e

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
echo "      🚀 EKROM SHOP AUTO INSTALLER (PORT 8090 SAFE MODE)      "
echo "=============================================================="
echo -e "${NC}"

# 1. Check Root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] กรุณารันด้วยสิทธิ์ root${NC}"
    exit 1
fi

SERVER_IP=$(curl -s --max-time 5 https://api.ipify.org || curl -s --max-time 5 https://ifconfig.me || hostname -I | awk '{print $1}')
echo -e "${GREEN}✓ IP Address เครื่อง: ${SERVER_IP}${NC}"

TARGET_DIR="/root/ekrom-shop"
REPO_URL="https://github.com/EkromSSH/EKROM.git"

# 2. Install dependencies
echo -e "\n${BLUE}[1/4] 📦 กำลังตรวจสอบ Dependencies (PHP, SQLite3, Git, Curl, UFW)...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y >/dev/null 2>&1 || apt-get update -y
apt-get install -y php-cli php-sqlite3 php-curl php-mbstring git curl sqlite3 ufw >/dev/null 2>&1
echo -e "${GREEN}✓ ตรวจสอบ Dependencies สำเร็จ${NC}"

# 3. Clone Repository or Update
echo -e "\n${BLUE}[2/4] 📥 กำลังตรวจสอบไฟล์ระบบ EKROM Shop...${NC}"
if [ -d "$TARGET_DIR/.git" ]; then
    echo -e "${YELLOW}พบโฟลเดอร์ระบบเดิม กำลังอัปเดตไฟล์ล่าสุด...${NC}"
    cd "$TARGET_DIR"
    git fetch origin main >/dev/null 2>&1 || true
    git reset --hard origin/main >/dev/null 2>&1 || true
else
    rm -rf "$TARGET_DIR"
    git clone "$REPO_URL" "$TARGET_DIR" >/dev/null 2>&1
fi

cd "$TARGET_DIR"
chmod -R 775 "$TARGET_DIR"

# 4. Clean Database (only initialize if database doesn't exist)
echo -e "\n${BLUE}[3/4] 🗄️ กำลังตรวจสอบฐานข้อมูล...${NC}"
if [ ! -f "$TARGET_DIR/database.sqlite" ]; then
    php "$TARGET_DIR/init_db.php" >/dev/null 2>&1
    chmod 666 "$TARGET_DIR/database.sqlite" 2>/dev/null || true
    php -r "require_once '$TARGET_DIR/api/db.php'; \$db = get_db(); \$db->exec(\"INSERT OR REPLACE INTO system_settings (key, value) VALUES ('turnstile_settings', '{\\\"enabled\\\":0,\\\"site_key\\\":\\\"\\\",\\\"secret_key\\\":\\\"\\\"}')\");" >/dev/null 2>&1 || true
    echo -e "${GREEN}✓ สร้างฐานข้อมูลเริ่มต้นเรียบร้อย${NC}"
else
    echo -e "${GREEN}✓ พบฐานข้อมูลเดิม พร้อมใช้งาน${NC}"
fi

# 5. Service on Port 8090 (Zero touch to Nginx / Port 80 / Port 443 / Port 8080 / Port 8880)
echo -e "\n${BLUE}[4/4] ⚙️ กำลังตั้งค่า Service ekrom-shop (พอร์ต 8090)...${NC}"
cat << 'EOF' > /etc/systemd/system/ekrom-shop.service
[Unit]
Description=EKROM-Shop Web Server (Port 8090)
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root/ekrom-shop
Environment=PHP_CLI_SERVER_WORKERS=8
ExecStart=/usr/bin/php -S 0.0.0.0:8090
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable ekrom-shop >/dev/null 2>&1
systemctl restart ekrom-shop

# Open firewall for port 8090
if command -v ufw >/dev/null 2>&1; then
    ufw allow 8090/tcp >/dev/null 2>&1 || true
fi
iptables -I INPUT -p tcp --dport 8090 -j ACCEPT 2>/dev/null || true

# Update command
chmod +x "$TARGET_DIR/update.sh" 2>/dev/null || true
ln -sf "$TARGET_DIR/update.sh" /usr/local/bin/update-shop 2>/dev/null || true

sleep 1
CHECK_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8090/ || echo "error")

echo -e "\n${GREEN}${BOLD}==============================================================${NC}"
echo -e "${GREEN}${BOLD}   ✅ ติดตั้ง EKROM Shop บนพอร์ต 8090 เรียบร้อยแล้ว!           ${NC}"
echo -e "${GREEN}${BOLD}==============================================================${NC}"
if [ "$CHECK_STATUS" = "200" ] || [ "$CHECK_STATUS" = "302" ]; then
    echo -e "  📡 สถานะเว็บในเครื่อง: ${GREEN}ทำงานปกติ (HTTP $CHECK_STATUS)${NC}"
else
    echo -e "  📡 สถานะเว็บในเครื่อง: ${YELLOW}$CHECK_STATUS${NC}"
fi
echo -e "  🌐 ${BOLD}ลิงก์เข้าใช้งาน:${NC}"
echo -e "     👉 ${CYAN}${BOLD}http://${SERVER_IP}:8090/${NC}"
echo -e "     👉 ${CYAN}http://${SERVER_IP}:8090/admin-dash.php${NC} (จัดการหลังบ้าน)"
echo ""
echo -e "  🔑 ${BOLD}ข้อมูลบัญชีผู้ดูแลระบบ (Admin):${NC}"
echo -e "     Username : ${BOLD}admin${NC}"
echo -e "     Password : ${BOLD}admin123${NC}"
echo -e "     PIN Code : ${BOLD}123456${NC}"
echo ""
echo -e "  🛡️ ${YELLOW}พอร์ต VPN ทั้งหมด (80, 443, 8080, 8880) ปลอดภัยและไม่โดนรบกวน 100%${NC}"
echo -e "${GREEN}${BOLD}==============================================================${NC}\n"
