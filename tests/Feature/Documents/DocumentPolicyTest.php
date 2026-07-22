<?php

use App\Models\Document;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->free = Plan::create([
        'slug' => 'free', 'name' => 'Free', 'stripe_price_id' => null,
        'price_jpy' => 0, 'trial_days' => 0,
        'features' => ['document_limit' => 3, 'attachments' => false, 'priority_support' => false],
        'sort_order' => 1,
    ]);

    $this->pro = Plan::create([
        'slug' => 'pro', 'name' => 'Pro', 'stripe_price_id' => 'price_pro_test',
        'price_jpy' => 1500, 'trial_days' => 14,
        'features' => ['document_limit' => null, 'attachments' => true, 'priority_support' => false],
        'sort_order' => 2,
    ]);
});

test('Freeプランはドキュメントを3件までしか作成できない', function () {
    $user = User::factory()->create();
    Document::factory()->count(3)->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('documents.store'), ['title' => '4件目'])
        ->assertForbidden();

    expect($user->documents()->count())->toBe(3);
});

test('Freeプランの3件目までは作成できる', function () {
    $user = User::factory()->create();
    Document::factory()->count(2)->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('documents.store'), ['title' => '3件目'])
        ->assertRedirect(route('documents.index'));

    expect($user->documents()->count())->toBe(3);
});

test('Proプランはドキュメント数の上限がない', function () {
    $user = User::factory()->create();
    $user->subscription->update([
        'plan_id' => $this->pro->id,
        'stripe_subscription_id' => 'sub_123',
        'status' => Subscription::STATUS_ACTIVE,
    ]);
    Document::factory()->count(10)->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('documents.store'), ['title' => '11件目'])
        ->assertRedirect(route('documents.index'));

    expect($user->documents()->count())->toBe(11);
});

test('Freeプランは添付ファイルを追加できない', function () {
    Storage::fake('local');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('documents.store'), [
        'title' => '添付テスト',
        'attachment' => UploadedFile::fake()->create('memo.txt', 10),
    ]);

    $response->assertForbidden();
});

test('他人のドキュメントは編集・削除できない', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $document = Document::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->put(route('documents.update', $document), ['title' => '改ざん'])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('documents.destroy', $document))
        ->assertForbidden();

    expect($document->fresh()->title)->not->toBe('改ざん');
});
