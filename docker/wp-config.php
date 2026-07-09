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

// === 自動更新を無効化（本番とバージョンを揃えるため） ===
define('WP_AUTO_UPDATE_CORE', false);
define('AUTOMATIC_UPDATER_DISABLED', true);

// === 認証キー ===
// あえて定義しない。未定義の場合、WordPress がランダムなキーを生成して
// DB に保存する（wp_salt() のフォールバック）。DB はボリュームで永続化
// されるため、リビルドしてもキーが変わらずログインセッションが維持される。

// === 開発用: 管理画面からの更新時にFTP情報を要求させない ===
define('FS_METHOD', 'direct');

$table_prefix = 'wp_';

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';