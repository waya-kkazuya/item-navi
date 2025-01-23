# ベースイメージとしてphp:8.2-apacheを使用
FROM php:8.2-apache

# 作業ディレクトリを設定
WORKDIR /var/www/html

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 必要なPHP拡張をインストール
RUN apt-get update && apt-get install -y \
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# viおよびnanoエディタのインストールを分けて実行
RUN apt-get update && apt-get install -y vim
RUN apt-get update && apt-get install -y nano

# Node.jsのインストール (バージョン22.xを指定)
RUN curl -sL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# 必要なNPMパッケージをインストール
COPY package*.json ./
RUN npm install

# Apacheのmod_rewriteを有効化
RUN a2enmod rewrite

# Apacheの設定ファイルをコピー
COPY ./docker-config/apache2.conf /etc/apache2/sites-available/000-default.conf

# アプリケーションのファイルをコピー
COPY . /var/www/html

# Composerの依存関係をインストール
RUN composer install

# アプリケーションキーの生成
RUN php artisan key:generate

# アプリケーションのビルド
RUN npm run build

# ログディレクトリの所有者と権限を設定 
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

# コンテナのポートを公開
EXPOSE 80