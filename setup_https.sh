#!/usr/bin/env bash
# ==============================================================================
#  EKROM SHOP - One-Click Auto HTTPS (SSL) Setup Script
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
echo "          🔐 EKROM SHOP AUTO HTTPS SETUP SYSTEM               "
echo "=============================================================="
echo -e "${NC}"

if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] กรุณารันคำสั่งด้วยสิทธิ์ root (sudo -i หรือ root)${NC}"
    exit 1
fi

DOMAIN="$1"
if [ -z "$DOMAIN" ]; then
    read -p "🌐 กรุณากรอกชื่อโดเมนของคุณ (เช่น yourdomain.com): " DOMAIN
fi

if [ -z "$DOMAIN" ]; then
    echo -e "${RED}[ERROR] ไม่ได้ระบุชื่อโดเมน${NC}"
    exit 1
fi

# ตรวจสอบรูปแบบโดเมน (ต้องมีจุดคั่น เช่น shop.yourdomain.com)
if [[ ! "$DOMAIN" =~ \. ]]; then
    echo -e "${RED}[ERROR] รูปแบบโดเมนไม่ถูกต้อง: '$DOMAIN'${NC}"
    echo -e "${YELLOW}กรุณากรอกชื่อโดเมนเต็มรูปแบบพร้อมนามสกุล (เช่น shop.yourdomain.com)${NC}"
    exit 1
fi

EMAIL="admin@$DOMAIN"
WEBROOT="/var/www/shop"

echo -e "\n${BLUE}[1/4] 📦 กำลังติดตั้ง Nginx และ Certbot...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y >/dev/null 2>&1
apt-get install -y nginx certbot >/dev/null 2>&1
systemctl enable --now nginx >/dev/null 2>&1
mkdir -p "$WEBROOT"

echo -e "\n${BLUE}[2/4] 🌐 กำลังตั้งค่าพอร์ต 80 เพื่อขอใบรับรอง SSL (Let's Encrypt)...${NC}"
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

cat << 'EOF' > /etc/nginx/conf.d/acme.conf
server {
    listen 80;
    server_name DOMAIN_PLACEHOLDER;
    location /.well-known/acme-challenge/ { root /var/www/shop; }
    location / { return 301 https://$host$request_uri; }
}
EOF
sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" /etc/nginx/conf.d/acme.conf

nginx -t >/dev/null 2>&1
systemctl reload nginx

echo -e "\n${BLUE}[3/4] 📜 กำลังขอใบรับรอง SSL ฟรีจาก Let's Encrypt...${NC}"
certbot certonly --webroot -w "$WEBROOT" -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --keep-until-expiring || \
certbot certonly --webroot -w "$WEBROOT" -d "$DOMAIN" --non-interactive --agree-tos --register-unsafely-without-email --keep-until-expiring

echo -e "\n${BLUE}[4/4] 🚀 กำลังเปิดใช้งาน HTTPS และ Reverse Proxy ไปยัง EKROM Shop...${NC}"
cat << 'EOF' > /etc/nginx/conf.d/https.conf
server {
    listen 443 ssl http2;
    server_name DOMAIN_PLACEHOLDER;

    ssl_certificate     /etc/letsencrypt/live/DOMAIN_PLACEHOLDER/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/DOMAIN_PLACEHOLDER/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    client_max_body_size 100M;

    # Let's Encrypt renewal
    location /.well-known/acme-challenge/ {
        root /var/www/shop;
    }

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
EOF
sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" /etc/nginx/conf.d/https.conf

# ตั้งค่า Cloudflare Real-IP อัตโนมัติ (เพื่อให้ระบบ SlipOK และ Security ได้รับ IP จริงของลูกค้า)
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
fi

nginx -t && systemctl reload nginx

echo -e "\n${GREEN}${BOLD}=============================================================="
echo "  🎉 สำเร็จ! เว็บไซต์ของคุณเปิดใช้งาน HTTPS เรียบร้อยแล้ว"
echo "  🌐 ลิงก์ร้านค้า: https://$DOMAIN"
echo -e "==============================================================${NC}\n"
