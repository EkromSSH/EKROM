#!/usr/bin/env bash
# ==============================================================================
#  EKROM SHOP - Safe Installer on Port 8080 (Non-conflicting with VPN)
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
echo "      🚀 EKROM SHOP AUTO INSTALLER (PORT 8080 SAFE MODE)      "
echo "=============================================================="
echo -e "${NC}"

# 1. Check Root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] กรุณารันด้วยสิทธิ์ root${NC}"
    exit 1
fi

SERVER_IP=$(curl -s --max-time 5 https://api.ipify.org || curl -s --max-time 5 https://ifconfig.me || hostname -I | awk '{print $1}')
echo -e "${GREEN}✓ IP Address เครื่อง: ${SERVER_IP}${NC}"

# 2. Install dependencies
echo -e "\n${BLUE}[1/4] 📦 กำลังติดตั้ง Dependencies (PHP, SQLite3, Git, Curl, UFW)...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y >/dev/null 2>&1 || apt-get update -y
apt-get install -y php-cli php-sqlite3 php-curl php-mbstring git curl sqlite3 ufw >/dev/null 2>&1

echo -e "${GREEN}✓ ติดตั้ง Dependencies สำเร็จ${NC}"

# 3. Clone Repository
echo -e "\n${BLUE}[2/4] 📥 กำลังดาวน์โหลดไฟล์ระบบ EKROM Shop...${NC}"
TARGET_DIR="/root/ekrom-shop"
REPO_URL="https://github.com/EkromSSH/EKROM.git"

if [ -d "$TARGET_DIR/.git" ]; then
    echo -e "${YELLOW}พบโฟลเดอร์เดิม กำลังอัปเดตไฟล์...${NC}"
    cd "$TARGET_DIR"
    git fetch origin main >/dev/null 2>&1 || true
    git reset --hard origin/main >/dev/null 2>&1 || true
else
    rm -rf "$TARGET_DIR"
    git clone "$REPO_URL" "$TARGET_DIR" >/dev/null 2>&1
fi

cd "$TARGET_DIR"
chmod -R 775 "$TARGET_DIR"

# 4. Clean Database
echo -e "\n${BLUE}[3/4] 🗄️ กำลังสร้างฐานข้อมูลเริ่มต้น (Clean Database 100%)...${NC}"
rm -f "$TARGET_DIR/database.sqlite"
php "$TARGET_DIR/init_db.php" >/dev/null 2>&1
chmod 666 "$TARGET_DIR/database.sqlite" 2>/dev/null || true

# Turnstile disabled for instant admin login
php -r "require_once '$TARGET_DIR/api/db.php'; \$db = get_db(); \$db->exec(\"INSERT OR REPLACE INTO system_settings (key, value) VALUES ('turnstile_settings', '{\\\"enabled\\\":0,\\\"site_key\\\":\\\"\\\",\\\"secret_key\\\":\\\"\\\"}')\");" >/dev/null 2>&1 || true

# 5. Service on Port 8080 (Zero touch to Nginx / Port 80 / Port 443)
echo -e "\n${BLUE}[4/4] ⚙️ กำลังตั้งค่า Service ekrom-shop (พอร์ต 8080)...${NC}"
cat << 'EOF' > /etc/systemd/system/ekrom-shop.service
[Unit]
Description=EKROM-Shop Web Server (Port 8080)
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root/ekrom-shop
Environment=PHP_CLI_SERVER_WORKERS=8
ExecStart=/usr/bin/php -S 0.0.0.0:8080
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable ekrom-shop >/dev/null 2>&1
systemctl restart ekrom-shop

# Open firewall for port 8080
if command -v ufw >/dev/null 2>&1; then
    ufw allow 8080/tcp >/dev/null 2>&1 || true
fi
iptables -I INPUT -p tcp --dport 8080 -j ACCEPT 2>/dev/null || true

# Update command
chmod +x "$TARGET_DIR/update.sh" 2>/dev/null || true
ln -sf "$TARGET_DIR/update.sh" /usr/local/bin/update-shop 2>/dev/null || true

echo -e "\n${GREEN}${BOLD}==============================================================${NC}"
echo -e "${GREEN}${BOLD}   ✅ ติดตั้ง EKROM Shop บนพอร์ต 8080 เรียบร้อยแล้ว!           ${NC}"
echo -e "${GREEN}${BOLD}==============================================================${NC}"
echo -e "  🌐 ${BOLD}ลิงก์เข้าใช้งาน:${NC}"
echo -e "     👉 ${CYAN}${BOLD}http://${SERVER_IP}:8080/${NC}"
echo -e "     👉 ${CYAN}http://${SERVER_IP}:8080/admin-dash.php${NC} (จัดการหลังบ้าน)"
echo ""
echo -e "  🔑 ${BOLD}ข้อมูลบัญชีผู้ดูแลระบบ (Admin):${NC}"
echo -e "     Username : ${BOLD}admin${NC}"
echo -e "     Password : ${BOLD}admin123${NC}"
echo -e "     PIN Code : ${BOLD}123456${NC}"
echo ""
echo -e "  🛡️ ${YELLOW}พอร์ต 80, 443, 8880 ของระบบ VPN ปลอดภัยและทำงานตามปกติ 100%${NC}"
echo -e "${GREEN}${BOLD}==============================================================${NC}\n"
