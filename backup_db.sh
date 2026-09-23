#!/usr/bin/env bash
# ==============================================================================
#  EKROM Shop - Automated SQLite Database Backup Script
# ==============================================================================
set -e

APP_DIR="/root/ekrom-shop"
BACKUP_DIR="${APP_DIR}/backups"
DB_FILE="${APP_DIR}/database.sqlite"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
TARGET_FILE="${BACKUP_DIR}/db_backup_${TIMESTAMP}.sqlite.gz"
TEMP_FILE="/tmp/ekrom_db_backup_$$.sqlite"

mkdir -p "$BACKUP_DIR"

if [ ! -f "$DB_FILE" ]; then
    echo "[ERROR] Database file not found: $DB_FILE"
    exit 1
fi

# 1. Flush WAL if possible and make online backup using sqlite3 CLI
if command -v sqlite3 >/dev/null 2>&1; then
    sqlite3 "$DB_FILE" "PRAGMA wal_checkpoint(PASSIVE);" >/dev/null 2>&1 || true
    sqlite3 "$DB_FILE" ".backup '$TEMP_FILE'"
else
    cp -f "$DB_FILE" "$TEMP_FILE"
fi

# 2. Compress backup
gzip -9 -c "$TEMP_FILE" > "$TARGET_FILE"
rm -f "$TEMP_FILE"
chmod 600 "$TARGET_FILE"

echo "[SUCCESS] Database backup created: $TARGET_FILE ($(du -h "$TARGET_FILE" | awk '{print $1}'))"

# 3. Purge backups older than 14 days
DELETED=$(find "$BACKUP_DIR" -name "db_backup_*.sqlite.gz" -type f -mtime +14 -print -delete | wc -l)
if [ "$DELETED" -gt 0 ]; then
    echo "[INFO] Cleaned up $DELETED old backup(s) (> 14 days)"
fi
