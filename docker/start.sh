#!/bin/sh
set -e

# Railwayは環境変数をコンテナ起動時に注入するため、ビルド時ではなく
# ここ(起動時)でconfig/route/viewキャッシュとマイグレーションを実行する。
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# plansテーブルをSTRIPE_PRICE_PRO/ENTERPRISEの現在値と同期する(updateOrCreateなので毎回実行して安全)。
php artisan db:seed --class=PlanSeeder --force

# nginxはconfig内で環境変数を展開できないため、${PORT}だけをここで埋め込む。
export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

exec supervisord -c /etc/supervisord.conf
