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
PID=$$
if [ -f "$TARGET_DIR/database.sqlite" ]; then
    cp -f "$TARGET_DIR/database.sqlite" "/tmp/ekrom_db_preserve_${PID}.sqlite" 2>/dev/null || true
    cp -f "$TARGET_DIR/database.sqlite" "$TARGET_DIR/database.sqlite.bak" 2>/dev/null || true
fi

# 2. Reset tracked files (except DB) and pull latest code from GitHub
echo -e "${YELLOW}[2/4] 📥 กำลังดึงไฟล์อัปเดตเวอร์ชันล่าสุดจาก GitHub...${NC}"
git fetch origin main
git checkout HEAD -- database.sqlite 2>/dev/null || true
git checkout -f -B main origin/main
git reset --hard origin/main

# 3. Restore database safely and migrate schema
echo -e "${YELLOW}[3/4] 🔄 ตรวจสอบและอัปเดตโครงสร้างฐานข้อมูล...${NC}"
if [ -f "/tmp/ekrom_db_preserve_${PID}.sqlite" ]; then
    cp -f "/tmp/ekrom_db_preserve_${PID}.sqlite" "$TARGET_DIR/database.sqlite"
    rm -f "/tmp/ekrom_db_preserve_${PID}.sqlite"
fi

php "$TARGET_DIR/init_db.php" >/dev/null 2>&1 || true
chmod -R 775 "$TARGET_DIR" 2>/dev/null || true
chmod 666 "$TARGET_DIR/database.sqlite" 2>/dev/null || true

# 4. Restart service
echo -e "${YELLOW}[4/4] ⚙️ รีสตาร์ท Service ekrom-shop...${NC}"
if systemctl is-active --quiet ekrom-shop.service 2>/dev/null; then
    systemctl restart ekrom-shop.service
    echo -e "${GREEN}✓ รีสตาร์ท Service สำเร็จ${NC}"
fi

echo -e "\n${GREEN}=============================================================="
echo "    🎉 อัปเดตระบบ EKROM SHOP สำเร็จเรียบร้อยแล้ว!"
echo -e "==============================================================${NC}"
