#!/bin/bash
# Deploy VSUZI hub update + create new WooCommerce products
#
# NOTE: Prefer `git push production main` for standard deploys.
# This script is for VSUZI-specific tasks (product creation, targeted upload).
#
# Run locally: bash scripts/deploy-vsuzi.sh

set -e

SERVER="root@85.198.96.28"
REMOTE_DIR="/var/www/tdpuls.com/public"
THEME_DIR="$REMOTE_DIR/wp-content/themes/flatsome"
UPLOADS_DIR="$REMOTE_DIR/wp-content/uploads/vsuzi"
LOCAL_DIR="$(cd "$(dirname "$0")/.." && pwd)"

echo ""
echo "=== VSUZI Hub Deploy ==="
echo "Local:  $LOCAL_DIR"
echo "Remote: $THEME_DIR"
echo ""

# ─── 1. Upload images ───
echo "=== 1. Загрузка изображений ==="
ssh $SERVER "mkdir -p $UPLOADS_DIR"
scp "$LOCAL_DIR/uploads/vsuzi/"*.webp "$SERVER:$UPLOADS_DIR/"
echo "  [+] Изображения загружены в $UPLOADS_DIR"
echo ""

# ─── 2. Upload PHP files ───
echo "=== 2. Загрузка PHP файлов ==="
scp "$LOCAL_DIR/theme/page-vsuzi-hub.php" "$SERVER:$THEME_DIR/page-vsuzi-hub.php"
echo "  [+] page-vsuzi-hub.php"
scp "$LOCAL_DIR/theme/functions.php" "$SERVER:$THEME_DIR/functions.php"
echo "  [+] functions.php"
scp "$LOCAL_DIR/theme/vsuzi-hub.css" "$SERVER:$THEME_DIR/vsuzi-hub.css"
echo "  [+] vsuzi-hub.css"
echo ""

# ─── 3. Create WooCommerce products ───
echo "=== 3. Создание товаров WooCommerce ==="

# Upload and run the import script on server
scp "$LOCAL_DIR/scripts/import-vsuzi-products.sh" "$SERVER:/tmp/import-vsuzi-products.sh"
ssh $SERVER "bash /tmp/import-vsuzi-products.sh"

echo ""

# ─── 4. Clear cache ───
echo "=== 4. Очистка кеша ==="
ssh $SERVER "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
echo "  [+] nginx cache очищен"

echo ""
echo "=== Готово! ==="
echo "Проверьте: https://tdpuls.com/product-category/interventsionnaya-rentgenologiya/vsuzi/"
echo ""
