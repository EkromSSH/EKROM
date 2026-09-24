#!/usr/bin/env bash
# ==============================================================================
#  EKROM SHOP - Safe One-Click System Updater
# ==============================================================================
set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}=============================================================="
echo "          🚀 EKROM SHOP SYSTEM UPDATER                       "
echo -e "==============================================================${NC}"

TARGET_DIR="/root/ekrom-shop"
if [ ! -d "$TARGET_DIR/.git" ]; then
    echo -e "${RED}[ERROR] ไม่พบโฟลเดอร์ Git ที่ $TARGET_DIR${NC}"
    exit 1
fi

cd "$TARGET_DIR"

# 1. Backup database safely
echo -e "${YELLOW}[1/4] 💾 กำลังสำรองฐานข้อมูลเดิม...${NC}"
mkdir -p "$TARGET_DIR/backups" 2>/dev/null || true
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
if [ -f "$TARGET_DIR/database.sqlite" ] && [ -s "$TARGET_DIR/database.sqlite" ]; then
    cp -f "$TARGET_DIR/database.sqlite" "$TARGET_DIR/backups/db_backup_${TIMESTAMP}.sqlite" 2>/dev/null || true
    cp -f "$TARGET_DIR/database.sqlite" "$TARGET_DIR/database.sqlite.bak" 2>/dev/null || true
    cp -f "$TARGET_DIR/database.sqlite" "/tmp/ekrom_db_preserve_safe.sqlite" 2>/dev/null || true
fi

# 2. Reset tracked files and pull latest code
echo -e "${YELLOW}[2/4] 📥 กำลังดึงไฟล์อัปเดตเวอร์ชันล่าสุด...${NC}"
git fetch origin main
git checkout -f -B main origin/main
git reset --hard origin/main

# 3. Restore database safely and migrate schema
echo -e "${YELLOW}[3/4] 🔄 ตรวจสอบและอัปเดตโครงสร้างฐานข้อมูล...${NC}"
if [ -f "/tmp/ekrom_db_preserve_safe.sqlite" ] && [ -s "/tmp/ekrom_db_preserve_safe.sqlite" ]; then
    cp -f "/tmp/ekrom_db_preserve_safe.sqlite" "$TARGET_DIR/database.sqlite"
    rm -f "/tmp/ekrom_db_preserve_safe.sqlite" 2>/dev/null || true
elif [ -f "$TARGET_DIR/database.sqlite.bak" ] && [ -s "$TARGET_DIR/database.sqlite.bak" ]; then
    cp -f "$TARGET_DIR/database.sqlite.bak" "$TARGET_DIR/database.sqlite"
fi

php "$TARGET_DIR/init_db.php" >/dev/null 2>&1 || true
chmod -R 775 "$TARGET_DIR" 2>/dev/null || true
chmod 666 "$TARGET_DIR/database.sqlite" 2>/dev/null || true
ln -sf "$TARGET_DIR/update.sh" /usr/local/bin/update-shop 2>/dev/null || true

# 4. Restart service in background (graceful delay so web response completes)
echo -e "${YELLOW}[4/4] ⚙️ รีสตาร์ท Service ekrom-shop...${NC}"
if systemctl is-active --quiet ekrom-shop.service 2>/dev/null; then
    (sleep 2 && systemctl restart ekrom-shop.service) >/dev/null 2>&1 &
    echo -e "${GREEN}✓ ส่งคำสั่งรีสตาร์ท Service สำเร็จ (หน่วงเวลา 2 วินาที)${NC}"
fi

echo -e "\n${GREEN}=============================================================="
echo "    🎉 อัปเดตระบบ EKROM SHOP สำเร็จเรียบร้อยแล้ว!"
echo -e "==============================================================${NC}"
