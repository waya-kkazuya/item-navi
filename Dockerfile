# ベースイメージとしてphp:8.2-apacheを使用
FROM php:8.2-apache

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

# ライブラリファイルを取得
RUN wget http://archive.ubuntu.com/ubuntu/pool/main/o/openssl/libssl1.1_1.1.0g-2ubuntu4_amd64.deb && \
    wget http://archive.ubuntu.com/ubuntu/pool/main/libj/libjpeg-turbo/libjpeg-turbo8_2.0.3-0ubuntu1_amd64.deb && \
    wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.bionic_amd64.deb

# ライブラリをインストール
RUN dpkg -i libssl1.1_1.1.0g-2ubuntu4_amd64.deb && \
    dpkg -i libjpeg-turbo8_2.0.3-0ubuntu1_amd64.deb && \
    dpkg -i wkhtmltox_0.12.6-1.bionic_amd64.deb

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

# Composerの依存関係をインストール
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# シンボリックリンクを作成（追加）
RUN php artisan storage:link

# ログディレクトリの所有者と権限を設定 
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

# コンテナのポートを公開
EXPOSE 80