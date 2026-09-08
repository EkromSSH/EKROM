#!/bin/bash
# EKROM Shop - Server Start Script
cd "$(dirname "$0")"

# Initialize DB if not exists
if [ ! -f "database.sqlite" ]; then
    echo "Initializing database..."
    php init_db.php
fi

PORT=${1:-8000}
echo "================================================="
echo "  🚀 Starting EKROM Shop on http://0.0.0.0:$PORT"
echo "================================================="
echo "  👤 Buyer Account:    buyer / buyer123"
echo "  🛡️ Reseller Account: reseller / reseller123"
echo "  ⚙️ Admin Account:    admin / admin123 (PIN: 123456)"
echo "================================================="
php -S 0.0.0.0:$PORT
