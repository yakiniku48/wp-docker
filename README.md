# WordPress Docker 開発環境

## ファイル構成

```
project/
├── .env                        # バージョン・ポート・パス設定
├── docker-compose.yml
├── docker/
│   ├── Dockerfile
│   ├── wp-config.php
│   ├── gen-salts.php           # ビルド時に認証ソルトを生成
│   └── .htaccess
└── dist/
    └── app/
        └── wp-content/
            ├── themes/         # 開発テーマを置く
            └── plugins/        # 開発プラグインを置く
```

## セットアップ

### 1. distディレクトリを作成する

```bash
mkdir -p dist/app/wp-content/themes
mkdir -p dist/app/wp-content/plugins
```

### 2. テーマを最低1つ配置する（必須）

`dist/app/wp-content/themes/` はコンテナ内の themes ディレクトリに丸ごとマウントされ、
**WordPress同梱のデフォルトテーマ（Twenty Twenty-xx）を覆い隠す**。
そのため、開発対象のテーマ、もしくは確認用のテーマを最低1つ置いておくこと。
空のままだと、インストール直後に有効化できるテーマが無く、フロントが表示できない。

```
dist/app/wp-content/themes/
└── my-theme/          # style.css と index.php を含む任意のテーマ
```

> 開発対象がプラグインのみの場合でも、表示確認用に既存テーマを1つ置いておくとよい。
> 起動後に WP-CLI でデフォルトテーマを入れる方法もある（後述）。

### 3. .env を確認・編集する

```env
WP_VERSION=6.7      # WordPressのバージョン
PHP_VERSION=8.3     # PHPのバージョン
WP_PORT=8888        # ブラウザでアクセスするポート
PMA_PORT=8080       # phpMyAdminのポート
WP_BASE=            # HOMEを置くサブディレクトリ（空ならルート直下）
WP_SUBDIR=app       # WPコアを置くサブディレクトリ名（既定: app）
DIST_PATH=dist/app  # ホスト側のdistディレクトリ
```

`WP_BASE` と `WP_SUBDIR` で公開URLが決まる。

| `WP_BASE` | `WP_SUBDIR` | HOME（公開ルート） | SITE（WPコア）   |
| --------- | ----------- | ------------------ | ---------------- |
| 空（既定）| `app`       | `/`                | `/app`           |
| `blog`    | `app`       | `/blog`            | `/blog/app`      |
| 空        | 空          | `/`                | `/`（ルート直下）|

`WP_BASE=blog` にすると、コンテナ内は次のようになる。
ルート直下（`/var/www/html/`）は空くので、`/index.html` などの静的ファイルを別途置ける。

```
/var/www/html/
├── index.html          # 任意の静的ページ（自分で配置）
└── blog/               # HOME = /blog（WP_BASE）
    ├── index.php       # ローダ（自動生成）
    ├── .htaccess       # RewriteBase /blog/（自動生成）
    └── app/            # WPコア本体 = /blog/app（WP_SUBDIR）
```

> `WP_BASE` を変える場合は、`.env` の `DIST_PATH` も実態に合わせて調整すること
> （例: `WP_BASE=blog` なら `DIST_PATH=dist/blog/app`）。

### 4. ビルドして起動する

```bash
docker compose up -d --build
```

初回はWordPressのダウンロードとPHP拡張のインストールがあるため数分かかる。

### 5. WordPressの初期設定を行う

ブラウザで http://localhost:8888 を開き、インストール画面の指示に従う。


## 日常的な操作

### 起動・停止

```bash
docker compose up -d                     # 起動
docker compose stop                      # 停止（データは保持）
docker compose down                      # 停止＋コンテナ削除（データは保持）
docker compose down --rmi all --volumes  # 停止＋コンテナ・DBデータをすべて削除
```

### ログを確認する

```bash
docker compose logs -f wordpress   # WordPressのログをリアルタイム表示
docker compose logs -f mysql       # MySQLのログ
```

### WP-CLIを使う

WP-CLI は `wordpress` コンテナに同梱しているため、起動中のコンテナに対して実行する。
`-u www-data` を付けるのは、root実行を避けるためと、生成ファイルの所有者をWPに合わせるため。
WPコアのパス（`WP_SUBDIR`）は `wp-cli.yml` で固定済みなので `--path` の指定は不要。

```bash
docker compose exec -u www-data wordpress wp plugin list
docker compose exec -u www-data wordpress wp theme list
docker compose exec -u www-data wordpress wp user list
```

確認用にデフォルトテーマを入れたい場合:

```bash
docker compose exec -u www-data wordpress wp theme install twentytwentyfour --activate
```

### phpMyAdminを起動する

通常は起動しない設定（`profiles: tools`）のため、使うときだけ起動する。

```bash
docker compose --profile tools up -d phpmyadmin
```

http://localhost:8080 でアクセスできる。


## バージョンを変更するとき

`.env` を編集してリビルドする。

```env
WP_VERSION=6.6
PHP_VERSION=8.2
```

```bash
docker compose up -d --build
```

> DBデータはボリュームに保持されているため、リビルドしても消えない。


## 環境をリセットするとき

```bash
docker compose down -v                      # コンテナとDBを削除
docker compose up -d --build                # 再ビルドして起動
```


## コンテナ内のファイル構成

`WP_SUBDIR=app` の場合、コンテナ内は以下の構成になる。

```
/var/www/html/
├── index.php          # ルートのエントリポイント（自動生成）
├── .htaccess
├── wp-cli.yml         # WP-CLI のパス設定（自動生成）
└── app/                # WPコア本体（WP_SUBDIR の値）
    ├── wp-admin/
    ├── wp-includes/
    ├── wp-config.php
    ├── wp-salts.php   # 認証ソルト（ビルド時に自動生成）
    ├── wp-content/
    │   ├── themes/    ← dist/app/wp-content/themes/ をマウント
    │   └── plugins/   ← dist/app/wp-content/plugins/ をマウント
    └── wp-login.php
```


## 設定値の流れ

```
.env
 └─▶ docker-compose.yml（変数展開・environment: セクション）
       └─▶ コンテナの環境変数
             └─▶ wp-config.php（getenv() で取得）
```

`.env` はdocker compose専用の設定ファイルであり、PHPが直接読むわけではない。


## トラブルシューティング

### ブラウザに「wp-config.phpが見つからない」と表示される

DBの起動が間に合っていない可能性がある。少し待ってからリロードする。
解消しない場合はログを確認する。

```bash
docker compose logs wordpress
```

### テーマ・プラグインが反映されない

マウント先のパスを確認する。`dist/app/wp-content/themes/` 以下にテーマのディレクトリが存在するか確認する。

### DBに接続できない

MySQLの起動を待たずにWordPressが起動した可能性がある。

```bash
docker compose restart wordpress
```
