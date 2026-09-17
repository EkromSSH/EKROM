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

# ถามชื่อโดเมนของลูกค้าสำหรับทำ HTTPS (Cloudflare Proxy พอร์ต 443) ทันที
DOMAIN="$1"
SETUP_HTTPS=0

if [ -n "$DOMAIN" ]; then
    SETUP_HTTPS=1
else
    echo ""
    echo -e "${CYAN}==============================================================${NC}"
    echo -e "${BOLD}🌐 ตั้งค่าโดเมนและระบบความปลอดภัย HTTPS (Cloudflare Proxy 443):${NC}"
    echo -e "   - กรุณากรอกชื่อโดเมนของคุณ เพื่อเปิดใช้งาน ${CYAN}${BOLD}HTTPS (พอร์ต 443)${NC} ทันที"
    echo -e "   - หากยังไม่มีโดเมน (จะใช้งานผ่าน IP ไปก่อน) ให้กด ${BOLD}[Enter]${NC} เพื่อข้าม"
    echo -e "${CYAN}==============================================================${NC}"

    if [ -t 0 ]; then
        read -p "👉 กรุณากรอกชื่อโดเมนของคุณ (เช่น yourdomain.com): " DOMAIN
    elif [ -e /dev/tty ]; then
        read -p "👉 กรุณากรอกชื่อโดเมนของคุณ (เช่น yourdomain.com): " DOMAIN < /dev/tty
    fi

    DOMAIN=$(echo "$DOMAIN" | tr -d '[:space:]')
    if [ -n "$DOMAIN" ]; then
        SETUP_HTTPS=1
        echo -e "${GREEN}✓ บันทึกโดเมน: ${DOMAIN} (ระบบจะติดตั้ง HTTPS พอร์ต 443 ให้อัตโนมัติ)${NC}"
    else
        echo -e "${YELLOW}✓ ไม่ได้ระบุโดเมน (ระบบจะตั้งค่าพอร์ต 80 ผ่าน IP ให้ชั่วคราว)${NC}"
    fi
fi

# 3. Update system & Install Dependencies
echo -e "\n${BLUE}[2/5] 📦 กำลังติดตั้ง Dependencies (PHP, SQLite3, Nginx, Git, Curl, OpenSSL, Certbot)...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y >/dev/null 2>&1
apt-get install -y php-cli php-sqlite3 php-curl php-mbstring nginx git curl sqlite3 ufw sshpass openssl certbot >/dev/null 2>&1

if ! command -v php >/dev/null 2>&1; then
    echo -e "${RED}[ERROR] การติดตั้ง PHP ไม่สำเร็จ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต${NC}"
    exit 1
fi
echo -e "${GREEN}✓ ติดตั้ง Dependencies สำเร็จแล้ว${NC}"

# 4. Clone / Update Repository
echo -e "\n${BLUE}[3/5] 📥 กำลังดาวน์โหลดไฟล์ระบบ EKROM-Shop...${NC}"
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
chmod +x "$TARGET_DIR/update.sh" 2>/dev/null || true
ln -sf "$TARGET_DIR/update.sh" /usr/local/bin/update-shop 2>/dev/null || true
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

# 6. Setup Nginx Reverse Proxy
if [ "$SETUP_HTTPS" -eq 1 ] && [ -n "$DOMAIN" ]; then
    echo -e "\n${BLUE}[5/5] 🔐 กำลังตั้งค่า Nginx พอร์ต 443 (Cloudflare HTTPS) สำหรับ $DOMAIN...${NC}"
    systemctl stop apache2 >/dev/null 2>&1 || true
    systemctl disable apache2 >/dev/null 2>&1 || true

    # 1. สร้างใบรับรอง SSL สำรอง 365 วัน (อายุไม่เกิน 398 วัน ป้องกันข้อผิดพลาด ERR_CERT_VALIDITY_TOO_LONG)
    mkdir -p /etc/ssl/ekrom-shop
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
      -keyout /etc/ssl/ekrom-shop/selfsigned.key \
      -out /etc/ssl/ekrom-shop/selfsigned.crt \
      -subj "/C=TH/ST=Bangkok/L=Bangkok/O=EKROM/OU=Shop/CN=$DOMAIN" >/dev/null 2>&1

    # 2. พยายามขอใบรับรอง Let's Encrypt แท้โดยอัตโนมัติ (หากโดเมนชี้มาที่ IP แล้ว)
    HAS_LE=0
    mkdir -p /var/www/shop
    cat << 'EOF' > /etc/nginx/conf.d/acme.conf
server {
    listen 80;
    server_name DOMAIN_PLACEHOLDER;
    location /.well-known/acme-challenge/ { root /var/www/shop; }
}
EOF
    sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" /etc/nginx/conf.d/acme.conf
    nginx -t >/dev/null 2>&1 && systemctl reload nginx || true

    EMAIL="admin@$DOMAIN"
    if certbot certonly --webroot -w /var/www/shop -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --keep-until-expiring >/dev/null 2>&1 || \
       certbot certonly --webroot -w /var/www/shop -d "$DOMAIN" --non-interactive --agree-tos --register-unsafely-without-email --keep-until-expiring >/dev/null 2>&1; then
        if [ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]; then
            HAS_LE=1
        fi
    fi
    rm -f /etc/nginx/conf.d/acme.conf

    if [ "$HAS_LE" -eq 1 ]; then
        SSL_CERT="/etc/letsencrypt/live/$DOMAIN/fullchain.pem"
        SSL_KEY="/etc/letsencrypt/live/$DOMAIN/privkey.pem"
        echo -e "${GREEN}✓ ติดตั้งใบรับรอง Let's Encrypt SSL แท้สำเร็จแล้ว (รองรับ Cloudflare ทุกโหมด)${NC}"
    else
        SSL_CERT="/etc/ssl/ekrom-shop/selfsigned.crt"
        SSL_KEY="/etc/ssl/ekrom-shop/selfsigned.key"
        echo -e "${GREEN}✓ ติดตั้งใบรับรอง SSL พอร์ต 443 สำเร็จแล้ว (สำหรับ Cloudflare Full Mode)${NC}"
    fi

    # ล้าง config default เดิม
    rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default 2>/dev/null || true
    rm -f /etc/nginx/conf.d/acme.conf 2>/dev/null || true

    # ตั้งค่า Nginx HTTPS (Port 443) และ HTTP Redirect (Port 80)
    cat << 'EOF' > /etc/nginx/conf.d/https.conf
# HTTPS Port 443
server {
    listen 443 ssl http2 default_server;
    listen [::]:443 ssl http2 default_server;
    server_name DOMAIN_PLACEHOLDER _;

    ssl_certificate     SSL_CERT_PATH;
    ssl_certificate_key SSL_KEY_PATH;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    client_max_body_size 100M;

    # ป้องกันไฟล์สำคัญ
    location ~* (\.sqlite|\.db|\.git|\.env|\.sh|README\.md)$ {
        return 404;
    }
    location ~ /\. {
        return 404;
    }

    # ส่งต่อไปยังเว็บร้านค้า EKROM Shop (พอร์ต 8000)
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }
}

# Redirect HTTP Port 80 -> HTTPS 443
server {
    listen 80;
    listen [::]:80;
    server_name DOMAIN_PLACEHOLDER;
    return 301 https://$host$request_uri;
}
EOF
    sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" /etc/nginx/conf.d/https.conf
    sed -i "s|SSL_CERT_PATH|$SSL_CERT|g" /etc/nginx/conf.d/https.conf
    sed -i "s|SSL_KEY_PATH|$SSL_KEY|g" /etc/nginx/conf.d/https.conf

    # ตั้งค่า Cloudflare Real-IP
    cat << 'EOF' > /etc/nginx/conf.d/cloudflare-realip.conf
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;
set_real_ip_from 2400:cb00::/32;
set_real_ip_from 2606:4700::/32;
set_real_ip_from 2803:f800::/32;
set_real_ip_from 2405:b500::/32;
set_real_ip_from 2405:8100::/32;
set_real_ip_from 2a06:98c0::/29;
set_real_ip_from 2c0f:f248::/32;
real_ip_header CF-Connecting-IP;
EOF

    # Firewall
    if command -v ufw >/dev/null 2>&1; then
        ufw allow 80/tcp >/dev/null 2>&1 || true
        ufw allow 443/tcp >/dev/null 2>&1 || true
        ufw allow 8000/tcp >/dev/null 2>&1 || true
    fi

    if nginx -t >/dev/null 2>&1; then
        systemctl restart nginx
        echo -e "${GREEN}✓ เปิดการทำงาน Nginx พอร์ต 443 (Cloudflare HTTPS) สำเร็จแล้ว!${NC}"
    else
        echo -e "${YELLOW}⚠️ Nginx config warning กรุณาตรวจสอบ /etc/nginx/${NC}"
    fi

else
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
fi

# Summary
sleep 1
echo ""
echo -e "${GREEN}${BOLD}=============================================================="
echo "    🎉 ติดตั้งระบบ EKROM SHOP สำเร็จเรียบร้อยแล้ว!"
echo "=============================================================="
echo -e "${NC}"

if [ "$SETUP_HTTPS" -eq 1 ] && [ -n "$DOMAIN" ]; then
    echo -e "  🌐 ${BOLD}ลิงก์เข้าสู่หน้าร้านค้า:${NC}"
    echo -e "     👉 ${CYAN}${BOLD}https://${DOMAIN}/${NC} (พอร์ต 443 HTTPS ผ่าน Cloudflare)"
    echo -e "     👉 ${CYAN}http://${SERVER_IP}/${NC} (พอร์ตตรง IP)"
    echo ""
    echo -e "  🔑 ${BOLD}ข้อมูลบัญชีสำหรับเข้าใช้งาน:${NC}"
    echo -e "     ---------------------------------------------------------"
    echo -e "     ⚙️ ${YELLOW}${BOLD}ผู้ดูแลระบบ (Admin):${NC}"
    echo -e "        Username : ${BOLD}admin${NC}"
    echo -e "        Password : ${BOLD}admin123${NC}"
    echo -e "        PIN Code : ${BOLD}123456${NC}"
    echo -e "        URL หลังบ้าน: ${CYAN}https://${DOMAIN}/admin-dash.php${NC}"
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
    echo -e "  ☁️ ${BOLD}ขั้นตอนบน Cloudflare Dashboard (ทำครั้งเดียว):${NC}"
    echo -e "     1. ${BOLD}DNS${NC}     : เพิ่ม A Record ชี้ ${CYAN}${DOMAIN}${NC} ไปที่ IP: ${YELLOW}${SERVER_IP}${NC} (เปิดเมฆส้ม ☁️)"
    echo -e "     2. ${BOLD}SSL/TLS${NC} : ตั้งค่าโหมดการเข้ารหัสเป็น ${BOLD}Full${NC}"
    echo ""
    echo -e "  📂 ตำแหน่งไฟล์ระบบ: ${BOLD}/root/ekrom-shop${NC}"
else
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
    echo -e "  📂 ตำแหน่งไฟล์ระบบ: ${BOLD}/root/ekrom-shop${NC}
  🔐 ${BOLD}วิธีเปิดใช้งาน HTTPS (SSL) ในภายหลัง:${NC}
     👉 ${CYAN}${BOLD}Cloudflare Proxy (พอร์ต 443 แนะนำ):${NC}
        ${YELLOW}bash /root/ekrom-shop/setup_cloudflare_https.sh โดเมนของคุณ.com${NC}
     👉 ${CYAN}${BOLD}Let's Encrypt SSL ฟรี (พอร์ต 80):${NC}
        ${YELLOW}bash /root/ekrom-shop/setup_https.sh โดเมนของคุณ.com${NC}"
fi

echo -e "  🛠️ ${BOLD}คำสั่งจัดการระบบ:${NC}"
echo -e "     - ตรวจสอบสถานะ: ${BOLD}systemctl status ekrom-shop${NC}"
echo -e "     - รีสตาร์ทระบบ : ${BOLD}systemctl restart ekrom-shop${NC}"
echo -e "     - ดู Log ระบบ : ${BOLD}journalctl -u ekrom-shop -f${NC}"
echo -e "     - อัปเดตระบบ   : ${CYAN}${BOLD}update-shop${NC}"
echo -e "${GREEN}${BOLD}==============================================================${NC}"
