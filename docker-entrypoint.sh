#!/bin/bash
# docker-entrypoint.sh
# コンテナ起動時にS3から画像をダウンロードし、Apacheを起動する

set -e

echo "======================================"
echo "Container starting..."
echo "======================================"

# S3から画像を同期
echo "Syncing images from S3..."
aws s3 sync s3://bucket-portfolio-ems/ \
    /var/www/html/storage/app/public/ \
    --exclude ".gitkeep" \
    || echo "Warning: Failed to sync images from S3 (this is normal in local development)"

echo "✓ Images synced"

# シンボリックリンクを作成
echo "Creating storage symlink..."
php artisan storage:link 2>/dev/null || echo "✓ Symlink already exists"

# 権限設定
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/public/storage 2>/dev/null || true
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/public/storage 2>/dev/null || true

echo "======================================"
echo "Container ready!"
echo "======================================"

# Apacheを起動（PID 1として）
exec "$@"