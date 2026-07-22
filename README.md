# Subscription SaaS Demo

Stripe Checkout / Webhook を使ったサブスクリプション課金SaaSのポートフォリオ用デモです。
業務内容自体はダミー(シンプルなドキュメント/ノート管理)で、プラン(Free / Pro / Enterprise)に
応じて機能制限が変わる、よくあるサブスクリプションモデルの実装に主眼を置いています。

**これはデモ環境です。Stripeはテストモードのみで動作し、実際の課金は一切発生しません。**

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

## DB設計の概要

- `plans`: Free/Pro/EnterpriseとStripe Price IDのマッピング、機能制限を`features`(json)に保持
- `subscriptions`: ユーザーごとの「現在の契約状態」を1行で保持(`user_id`にUNIQUE制約)
- `stripe_webhook_events`: 受信したStripe WebhookイベントのID・種別・処理状況を記録(冪等性の要)
- `documents`: プラン制限のデモ対象となるダミー業務データ

## 主な技術判断

### Laravel Cashierを使わず、Stripe公式SDKを直接使った理由

Cashierを使えば早いですが、内部で何が起きているかがブラックボックスになりやすく、
「Checkoutセッションの組み立て」「Webhookの検証と同期」「冪等性の担保」といった
ポートフォリオとして見せたい部分を自分の設計として説明しづらくなります。
そのため `stripe/stripe-php` を直接使い、Checkout/Webhook/同期ロジックをすべて
アプリケーション側で明示的に実装しました。

### Webhookの冪等性をどう担保したか

Stripeは同一イベントを複数回配信することがあり(ネットワーク断・タイムアウト後の自動リトライ等)、
何も対策しないと「同じ支払い成功イベントで二重にプランを昇格させる」ような不整合が起こり得ます。

対策として `stripe_webhook_events` テーブルに `stripe_event_id` のUNIQUE制約を持たせ、
`App\Models\StripeWebhookEvent::findOrCreateReceived()` で次のように扱っています。

1. 同じ`stripe_event_id`のレコードを検索する
2. なければ`status=received`で作成を試みる。ここで**DBのUNIQUE制約**により、
   同時に同じイベントが並行して届いても、INSERTに成功するのはどちらか一方だけになる
   (負けた側は一意制約違反を捕捉し、勝った側が作ったレコードを再取得する)
3. レコードの`status`が`processed`(処理済み)なら、実処理を一切行わず即座に200系を返して終了する
4. 未処理(`received`)または過去に失敗(`failed`)したイベントだけが実際の同期処理に進む
5. 処理が成功すれば`processed`、例外が発生すれば`failed`としてマークし、5xxを返す
   (Stripeは5xxに対して自動的に同じイベントIDで再送してくるため、次回は`failed`ステータスから
   再処理される)

「INSERTを一度成功させたら二度と処理しない」という単純な設計だと、処理中に例外が起きて
失敗したイベントがStripeから再送されてきても永久にスキップされてしまいます。
`processed`/`received`/`failed`の3状態を持たせることで、**「同じ成功イベントの二重処理は防ぎつつ、
失敗したイベントの再送は正しく再処理する」**という、Webhookの自動リトライの仕組みと矛盾しない
冪等性を実現しています。

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
