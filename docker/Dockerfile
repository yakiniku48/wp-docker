ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-apache

ARG WP_VERSION=6.7
ARG WP_SUBDIR=app

# --- PHP拡張 ---
RUN apt-get update && apt-get install -y \
      libpng-dev libjpeg-dev libwebp-dev libzip-dev curl unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install mysqli gd zip exif \
    && rm -rf /var/lib/apt/lists/*

# --- mod_rewrite 有効化 ---
RUN a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# --- WP-CLI インストール ---
RUN curl -fsSL -o /usr/local/bin/wp \
      https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# --- WordPress ダウンロード ---
RUN curl -o /tmp/wordpress.tar.gz "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz" \
    && tar -xzf /tmp/wordpress.tar.gz -C /tmp/ \
    && rm /tmp/wordpress.tar.gz

# --- 設定ファイルをWPディレクトリへコピー ---
COPY wp-config.php /tmp/wordpress/wp-config.php
COPY .htaccess /var/www/html/.htaccess
COPY gen-salts.php /tmp/gen-salts.php

# --- ディレクトリ配置・ソルト生成・WP-CLI設定 ---
RUN set -e; \
    WEBROOT=/var/www/html; \
    SUBDIR="${WP_SUBDIR}"; \
    if [ -n "$SUBDIR" ]; then \
      mkdir -p "${WEBROOT}/${SUBDIR}"; \
      find /tmp/wordpress -maxdepth 1 -mindepth 1 \
        -exec mv {} "${WEBROOT}/${SUBDIR}/" \; ; \
      printf '<?php\ndefine("WP_USE_THEMES", true);\nrequire __DIR__ . "/%s/wp-blog-header.php";\n' \
        "$SUBDIR" > "${WEBROOT}/index.php"; \
      CONFIG_DIR="${WEBROOT}/${SUBDIR}"; \
      # WP-CLI が常にWPコアを見つけられるよう path を固定する \
      printf 'path: %s\n' "$SUBDIR" > "${WEBROOT}/wp-cli.yml"; \
    else \
      find /tmp/wordpress -maxdepth 1 -mindepth 1 \
        -exec mv {} "${WEBROOT}/" \; ; \
      CONFIG_DIR="${WEBROOT}"; \
    fi; \
    php /tmp/gen-salts.php "${CONFIG_DIR}/wp-salts.php"; \
    rm -rf /tmp/wordpress /tmp/gen-salts.php

RUN chown -R www-data:www-data /var/www/html
