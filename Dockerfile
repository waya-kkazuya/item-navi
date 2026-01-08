# ベースイメージとしてphp:8.2-apacheを使用
FROM php:8.2-apache-bullseye

# 作業ディレクトリを設定
WORKDIR /var/www/html

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 必要なPHP拡張をインストール
RUN apt-get update && apt-get install -y \
    wget \
    vim \
    nano \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libvpx-dev \
    libzip-dev \
    libmagickwand-dev \
    default-mysql-client \
    iputils-ping \
    unzip \
    xfonts-75dpi \
    xfonts-base

# AWS CLI v2 のインストール
RUN curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip" \
    && unzip awscliv2.zip \
    && ./aws/install \
    && rm -rf aws awscliv2.zip

RUN apt-get update && apt-get install -y \
    wkhtmltopdf \
    xfonts-75dpi \
    xfonts-base

# GDライブラリの設定とインストール
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Node.jsのインストール (バージョン22.xを指定)
RUN curl -sL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Apacheのmod_rewriteを有効化
RUN a2enmod rewrite

# Apacheの設定ファイルをコピー
COPY ./docker-config/apache2.conf /etc/apache2/sites-available/000-default.conf

# アプリケーションのファイルをコピー
COPY . /var/www/html

# Composerの依存関係をインストール前にgitを追加
RUN apt-get update && apt-get install -y git && rm -rf /var/lib/apt/lists/*

# Composerの依存関係をインストール
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# storage/app/public/ のディレクトリを作成
RUN mkdir -p /var/www/html/storage/app/public/items \
    && mkdir -p /var/www/html/storage/app/public/images \
    && mkdir -p /var/www/html/storage/app/public/fonts \
    && mkdir -p /var/www/html/storage/app/public/labels \
    && mkdir -p /var/www/html/storage/app/public/profile \
    && mkdir -p /var/www/html/storage/app/public/qrcode

# entrypointスクリプトをコピー
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# ログディレクトリの所有者と権限を設定
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

# コンテナのポートを公開
EXPOSE 80

# ENTRYPOINTを設定
ENTRYPOINT ["docker-entrypoint.sh"]

# デフォルトコマンド（Apacheを起動）
CMD ["apache2-foreground"]
