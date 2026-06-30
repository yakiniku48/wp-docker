ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-apache

ARG WP_VERSION=6.7
ARG WP_SUBDIR=app
# WP_BASE: HOME（公開ルート）を置くサブディレクトリ。空ならWEBROOT直下
ARG WP_BASE=

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
COPY .htaccess /tmp/htaccess.tmpl
COPY gen-salts.php /tmp/gen-salts.php

# --- ディレクトリ配置・ソルト生成・WP-CLI設定 ---
#   WEBROOT/[WP_BASE]/[WP_SUBDIR] の二段構成。
#     HOME_DIR … 公開ルート（index.php と .htaccess を置く）。WP_BASE が空ならWEBROOT
#     WP_DIR   … WPコア本体。WP_SUBDIR が空なら HOME_DIR と同じ
RUN set -e; \
    WEBROOT=/var/www/html; \
    BASE="${WP_BASE}"; \
    SUBDIR="${WP_SUBDIR}"; \
    if [ -n "$BASE" ]; then HOME_DIR="${WEBROOT}/${BASE}"; else HOME_DIR="${WEBROOT}"; fi; \
    if [ -n "$SUBDIR" ]; then WP_DIR="${HOME_DIR}/${SUBDIR}"; else WP_DIR="${HOME_DIR}"; fi; \
    mkdir -p "$WP_DIR"; \
    find /tmp/wordpress -maxdepth 1 -mindepth 1 -exec mv {} "${WP_DIR}/" \; ; \
    # .htaccess の RewriteBase（WP_BASE が空なら "/"、blog なら "/blog/"） \
    if [ -n "$BASE" ]; then BASE_PATH="/${BASE}/"; else BASE_PATH="/"; fi; \
    sed "s#__WP_BASE_PATH__#${BASE_PATH}#g" /tmp/htaccess.tmpl > "${HOME_DIR}/.htaccess"; \
    # HOME とWPコアが別ディレクトリの場合のみローダ index.php を生成 \
    if [ "$WP_DIR" != "$HOME_DIR" ]; then \
      printf '<?php\ndefine("WP_USE_THEMES", true);\nrequire __DIR__ . "/%s/wp-blog-header.php";\n' \
        "$SUBDIR" > "${HOME_DIR}/index.php"; \
    fi; \
    # WEBROOT から見たWPコアの相対パス（WP-CLI が常にコアを見つけられるよう固定） \
    REL_WP="$BASE"; \
    if [ -n "$SUBDIR" ]; then REL_WP="${REL_WP:+$REL_WP/}${SUBDIR}"; fi; \
    if [ -n "$REL_WP" ]; then printf 'path: %s\n' "$REL_WP" > "${WEBROOT}/wp-cli.yml"; fi; \
    php /tmp/gen-salts.php "${WP_DIR}/wp-salts.php"; \
    rm -rf /tmp/wordpress /tmp/gen-salts.php /tmp/htaccess.tmpl

RUN chown -R www-data:www-data /var/www/html
