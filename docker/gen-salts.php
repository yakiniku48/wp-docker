<?php
/**
 * WordPress の認証用ソルトキーを生成し、指定パスへ書き出す。
 * Dockerfile のビルド時に実行され、wp-config.php から読み込まれる。
 *
 * 使い方: php gen-salts.php /path/to/wp-salts.php
 */

$target = $argv[1] ?? '';

if ($target === '') {
    fwrite(STDERR, "出力先パスを指定してください。\n");
    exit(1);
}

$keys = [
    'AUTH_KEY',
    'SECURE_AUTH_KEY',
    'LOGGED_IN_KEY',
    'NONCE_KEY',
    'AUTH_SALT',
    'SECURE_AUTH_SALT',
    'LOGGED_IN_SALT',
    'NONCE_SALT',
];

$lines = ['<?php'];

foreach ($keys as $key) {
    // base64 は英数字と + / = のみなのでシングルクオート内に安全に埋め込める
    $value = base64_encode(random_bytes(48));
    $lines[] = sprintf("define('%s', '%s');", $key, $value);
}

if (file_put_contents($target, implode("\n", $lines) . "\n") === false) {
    fwrite(STDERR, "ソルトファイルの書き込みに失敗しました: {$target}\n");
    exit(1);
}
