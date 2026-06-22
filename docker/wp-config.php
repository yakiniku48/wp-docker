<?php
// === データベース ===
define('DB_NAME',    getenv('WORDPRESS_DB_NAME')     ?: 'wordpress');
define('DB_USER',    getenv('WORDPRESS_DB_USER')     ?: 'wordpress');
define('DB_PASSWORD',getenv('WORDPRESS_DB_PASSWORD') ?: '');
define('DB_HOST',    getenv('WORDPRESS_DB_HOST')     ?: 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// === URL ===
define('WP_HOME',    getenv('WP_HOME')    ?: 'http://localhost');
define('WP_SITEURL', getenv('WP_SITEURL') ?: 'http://localhost');

// === デバッグ ===
define('WP_DEBUG',         (bool)(getenv('WP_DEBUG')         ?: false));
define('WP_DEBUG_LOG',     (bool)(getenv('WP_DEBUG_LOG')     ?: false));
define('WP_DEBUG_DISPLAY', (bool)(getenv('WP_DEBUG_DISPLAY') ?: false));
define('SCRIPT_DEBUG',     (bool)(getenv('SCRIPT_DEBUG')     ?: false));
define('WP_ENVIRONMENT_TYPE', getenv('WP_ENVIRONMENT_TYPE') ?: 'production');

// === 認証キー ===
// ビルド時に docker/gen-salts.php が wp-salts.php を自動生成する。
// 何らかの理由で生成されなかった場合に備えてフォールバックを用意する。
$wp_salts = __DIR__ . '/wp-salts.php';
if (is_readable($wp_salts)) {
    require $wp_salts;
} else {
    define('AUTH_KEY',         'put your unique phrase here');
    define('SECURE_AUTH_KEY',  'put your unique phrase here');
    define('LOGGED_IN_KEY',    'put your unique phrase here');
    define('NONCE_KEY',        'put your unique phrase here');
    define('AUTH_SALT',        'put your unique phrase here');
    define('SECURE_AUTH_SALT', 'put your unique phrase here');
    define('LOGGED_IN_SALT',   'put your unique phrase here');
    define('NONCE_SALT',       'put your unique phrase here');
}

// === 開発用: 管理画面からの更新時にFTP情報を要求させない ===
define('FS_METHOD', 'direct');

$table_prefix = 'wp_';

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';