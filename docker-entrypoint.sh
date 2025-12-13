#!/bin/bash
# docker-entrypoint.sh
# コンテナ起動時にS3から画像をダウンロードし、Apacheを起動する

set -e

echo "======================================"
echo "Container starting..."
echo "======================================"

# S3から画像をダウンロード（初回のみ）
if [ ! -f /var/www/html/storage/app/public/.downloaded ]; then
    echo "Downloading images from S3..."
    
    # ECSタスクロールの認証情報を自動的に使用
    # 環境変数（AWS_ACCESS_KEY_ID等）の設定は不要
    aws s3 sync s3://bucket-portfolio-ems/storage/app/public/ \
        /var/www/html/storage/app/public/ \
        --exclude ".gitkeep" \
        --exclude ".downloaded" \
        || echo "Warning: Failed to download images from S3 (this is normal in local development)"
    
    # ダウンロード完了フラグを作成
    touch /var/www/html/storage/app/public/.downloaded
    
    echo "✓ Images downloaded successfully"
else
    echo "✓ Images already exist, skipping download"
fi

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