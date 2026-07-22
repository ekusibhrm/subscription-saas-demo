# Subscription SaaS Demo

Stripe Checkout / Webhook を使ったサブスクリプション課金SaaSのポートフォリオ用デモです。
業務内容自体はダミー(シンプルなドキュメント/ノート管理)で、プラン(Free / Pro / Enterprise)に
応じて機能制限が変わる、よくあるサブスクリプションモデルの実装に主眼を置いています。

**これはデモ環境です。Stripeはテストモードのみで動作し、実際の課金は一切発生しません。**

## デモURL

🔗 **[https://subscription-saas-demo-production.up.railway.app](https://subscription-saas-demo-production.up.railway.app)**

> これはデモ環境です。実際の課金は発生しません。テストカード番号 `4242 4242 4242 4242`
> (有効期限・CVC・郵便番号は任意の値)でお試しいただけます。

## 技術スタック

- PHP 8.4 / Laravel 13
- MySQL(Laravel Sail / Docker)
- Stripe公式PHP SDK(`stripe/stripe-php`。Laravel Cashierは使わず直接統合)
- Pest
- Blade + Tailwind(Breeze / Blade stack。デザインより機能優先)

## セットアップ

```bash
cp .env.example .env
docker compose up -d
docker compose exec laravel.test composer install
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate --seed
docker compose exec laravel.test npm install && npm run build
```

`.env` に Stripeのテストモードキーと、Stripeダッシュボードで作成したPro/Enterprise用の
Price ID を設定してください(すべてテストモード `sk_test_...` / `price_...` です)。

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_PRO=price_...
STRIPE_PRICE_ENTERPRISE=price_...
```

ローカルでWebhookを受け取るには Stripe CLI を使います。

```bash
stripe listen --forward-to localhost/stripe/webhook
```

決済のテストには Stripe のテストカード番号 `4242 4242 4242 4242`(有効期限・CVC・郵便番号は任意)を使用してください。

## テスト

```bash
docker compose exec laravel.test ./vendor/bin/pest
```

## Railwayへのデプロイ

このリポジトリには本番用の `Dockerfile`(nginx + php-fpm を supervisord で起動)と
`railway.json` を同梱しており、Railwayが自動的にDockerfileビルドを検出します
(Nixpacksではなく明示的に `"builder": "DOCKERFILE"` を指定)。

> **ハマりどころ**
> - Railwayの公開ドメインが向く**ターゲットポート**は、コンテナ内で実際に待ち受けている
>   ポート(このDockerfileではnginxの`8080`)と一致している必要があります。Railwayが
>   Dockerfileから自動検出したポートが、php-fpmの内部ポート(`9000`)など意図しない値に
>   なっていることがあるため、502が返る場合はまず `railway domain status` でターゲット
>   ポートを確認してください。
> - Railwayはエッジでhttps終端後、コンテナへは平文httpで転送します。そのため
>   `bootstrap/app.php` で `$middleware->trustProxies(at: '*')` を設定しないと、
>   LaravelがURLをhttpとして生成してしまい、CSSやJSがmixed contentとしてブラウザに
>   ブロックされます(本リポジトリでは設定済み)。

### 1. 必要な環境変数

一覧は [`.env.production.example`](.env.production.example) にまとめてあります。
Railwayの対象サービス → Variables タブで、これらをすべて設定してください。

| 変数 | 値の例 | 補足 |
|---|---|---|
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | 本番でエラー詳細を出さない |
| `APP_KEY` | `base64:...` | ローカルで `php artisan key:generate --show` した値を貼り付け(コンテナ内で都度生成しない) |
| `APP_URL` | `https://xxxx.up.railway.app` | Railwayが割り当てたドメイン |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | 下記参照 | RailwayのMySQLプラグインの値を参照 |
| `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | `database` | コンテナのローカルディスクは再デプロイで消えるため必須 |
| `LOG_CHANNEL` | `stderr` | Railwayがstdout/stderrをログとして収集する |
| `STRIPE_KEY` | `pk_test_...` | **必ずテストモードキー** |
| `STRIPE_SECRET` | `sk_test_...` | **必ずテストモードキー** |
| `STRIPE_WEBHOOK_SECRET` | `whsec_...` | 下記「Webhookエンドポイントの登録」で発行される値 |
| `STRIPE_PRICE_PRO` / `STRIPE_PRICE_ENTERPRISE` | `price_...` | Stripeテストモードの価格ID |

APIキーなどの機密情報はコードや`.env`ファイルとしてリポジトリには含めず、Railwayの
Variables機能でのみ設定します(`.env`は`.gitignore`済みでリポジトリに含まれません)。

### 2. RailwayのMySQLプラグインとの接続

RailwayでMySQLプラグイン(サービス)を追加すると、そのサービス自身に
`MYSQLHOST` / `MYSQLPORT` / `MYSQLDATABASE` / `MYSQLUSER` / `MYSQLPASSWORD` /
`MYSQL_URL` などの変数が自動生成されます。アプリ側のサービスでは、Railwayの
**Variable Reference**(`${{ServiceName.VAR}}`)を使って以下のように設定してください
(`MySQL` の部分は実際に追加したプラグインのサービス名に置き換える)。

```
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

(`DB_URL=${{MySQL.MYSQL_URL}}` のように1本のDSNでまとめて渡す方法でも可)

### 3. Stripe Webhookエンドポイントの登録

Stripeダッシュボード(**テストモードのまま**)→ 開発者 → Webhooks → 「エンドポイントを追加」で、
以下の形式のURLを登録します。

```
https://<Railwayのドメイン>/stripe/webhook
```

例: `https://subscription-saas-demo-production.up.railway.app/stripe/webhook`

購読するイベントは `checkout.session.completed` / `customer.subscription.updated` /
`customer.subscription.deleted` / `invoice.payment_succeeded` / `invoice.payment_failed` です
(それ以外のイベントが届いても`StripeWebhookController`は無視するだけなので、
迷ったら「すべてのイベント」を選択しても問題ありません)。
登録すると発行される signing secret を `STRIPE_WEBHOOK_SECRET` に設定してください
(ローカルの `stripe listen` が発行するものとは別の値です)。

### 4. デプロイ時に自動実行されるコマンド

`docker/start.sh` がコンテナ起動のたびに以下を実行してから nginx/php-fpm を起動します
(Railwayは環境変数をビルド時ではなく起動時に注入するため、キャッシュ生成は起動時に行う必要があります)。

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=PlanSeeder --force   # plansテーブルをSTRIPE_PRICE_*と同期
```

いずれも冪等(何度実行しても安全)なので、再デプロイのたびに実行して問題ありません。

### 5. デプロイ後の動作確認チェックリスト

- [ ] `https://<ドメイン>/up` が200を返す(Railwayのヘルスチェックにも使用)
- [ ] トップページ `/` が表示され、デモバナーとテストカード番号の案内が出ている
- [ ] 新規登録 → 自動でFreeプランの契約が作成される(`/dashboard` に反映)
- [ ] `/plans` からProプランを選択 → Stripe Checkout(テストモード)にリダイレクトされる
- [ ] テストカード `4242 4242 4242 4242` で決済 → `/checkout/success` に戻る
- [ ] Stripeダッシュボードの Webhooks → 該当エンドポイントで `checkout.session.completed` が200(成功)になっている
- [ ] `/plans` の表示が Pro / trialing に更新されている(Webhook経由でDBに同期された証拠)
- [ ] `/billing` にStripeの請求書(¥0のトライアル分)が表示される
- [ ] `/plans` の「解約する」→ 期間終了時キャンセル予約になる
- [ ] Freeプランの上限(ドキュメント3件)が正しく効いている(`/documents`)

## DB設計の概要

- `plans`: Free/Pro/EnterpriseとStripe Price IDのマッピング、機能制限を`features`(json)に保持
- `subscriptions`: ユーザーごとの「現在の契約状態」を1行で保持(`user_id`にUNIQUE制約)
- `stripe_webhook_events`: 受信したStripe WebhookイベントのID・種別・処理状況を記録(冪等性の要)
- `documents`: プラン制限のデモ対象となるダミー業務データ

## 技術的な判断

**Stripe Webhookの冪等性**
`stripe_webhook_events`テーブルの`stripe_event_id`にUNIQUE制約を張り、各イベントに
`received`/`processed`/`failed`の状態を持たせています。既に`processed`なら即座にスキップし、
`failed`のまま再送されたイベントだけ再処理する設計です。UNIQUE制約があるため、同じイベントが
同時に並行して届いても二重処理はDBレベルで防げます(詳細は`App\Models\StripeWebhookEvent`)。

**テスト/本番のAPIキー切り替え**
アプリ側に「テストか本番か」を判定するコードは持たせていません。`STRIPE_KEY` /
`STRIPE_SECRET` / `STRIPE_WEBHOOK_SECRET`は環境変数から読むだけで、`config/services.php`は
モードを意識しません。切り替えは「環境変数にどの値を入れるか」だけに閉じ込め、本デモでは
常にテストモードキー(`pk_test_` / `sk_test_`)のみを設定する運用にしています。

**プランごとの機能制限(認可)**
`Plan`モデルの`features`(json)にドキュメント上限や添付ファイル可否などの差分を持たせ、
`DocumentPolicy`がログインユーザーの`currentPlan()`を見て`Gate::authorize()`で判定します。
プランの制限値を変えたい場合もコード変更は不要で、DBの`features`を書き換えるだけで反映されます。

## 主な技術判断

### Laravel Cashierを使わず、Stripe公式SDKを直接使った理由

Cashierを使えば早いですが、内部で何が起きているかがブラックボックスになりやすく、
「Checkoutセッションの組み立て」「Webhookの検証と同期」「冪等性の担保」といった
ポートフォリオとして見せたい部分を自分の設計として説明しづらくなります。
そのため `stripe/stripe-php` を直接使い、Checkout/Webhook/同期ロジックをすべて
アプリケーション側で明示的に実装しました。

### プラン変更・ダウングレード・解約の扱い

- Pro⇔Enterpriseのようにすでに有料契約中のユーザーがプランを変更する場合は、新しいCheckout
  セッションを作らず、既存のStripe Subscriptionのアイテムを直接更新(`proration_behavior:
  create_prorations`)しています。日割り計算をStripe側に任せられ、実装もシンプルになります。
- 有料プランからFree、あるいは単純な「解約」は即時解約ではなく、Stripe側で
  `cancel_at_period_end=true`を設定するスケジュール解約にしています。契約期間の終了時に
  Stripeから届く`customer.subscription.deleted`イベントを受けて、ローカルの契約情報をFreeプランに
  戻します。ユーザーは期間終了までは引き続き有料機能を使えます。

### 請求履歴・領収書をローカルに保存しない理由

`invoices`テーブルは持たず、請求履歴ページは表示のたびにStripe APIから直接取得しています。
Stripeが唯一の正データであるものをアプリ側に複製すると、同期漏れや不整合の温床になるため、
表示専用のデータはキャッシュせず都度取得する設計にしました。
