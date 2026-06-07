# attendance-management
coachtech 勤怠管理アプリです。

## 開発環境
- **勤怠打刻**: http://localhost/attendance
- **ログイン**: http://localhost/login
- **会員登録**: http://localhost/register
- **管理者ログイン**: http://localhost/admin/login
- **メール認証**: http://localhost:8025
- **データベース管理 (phpMyAdmin)**: http://localhost:8080/

## 使用技術（実行環境）
- PHP 8.1
- Laravel 8.x
- MySQL 8.0.26
- nginx 1.21.1

## 環境構築
```bash
# 1. リポジトリをクローン
git clone git@github.com:androidpug/attendance-management.git
# 2. プロジェクトに移動
cd attendance-management
# 3. コンテナ起動
docker-compose up -d --build
# 4. コンテナ内に入る
docker-compose exec php bash
```

## Laravel環境構築 コンテナ内操作
```bash
# 1. ライブラリのインストール
composer install
# 2. 環境設定ファイルの作成
cp .env.example .env
# 3. アプリケーションキーの生成
php artisan key:generate
# 4. データベースのマイグレーション及びシーディング
php artisan migrate:fresh --seed
```

## 動作確認用ログイン情報
### 一般ユーザー1
- メールアドレス: user1@example.com
- パスワード: password

### 一般ユーザー2
- メールアドレス: user2@example.com
- パスワード: password

### 管理者ユーザー
- メールアドレス: user3@example.com
- パスワード: password

## ER図
![ER図](./er-diagram.drawio)