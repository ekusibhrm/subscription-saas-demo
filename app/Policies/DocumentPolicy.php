<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    /**
     * プランのドキュメント上限に達していなければ作成可能。
     */
    public function create(User $user): Response
    {
        $limit = $user->currentPlan()?->documentLimit();

        if ($limit === null) {
            return Response::allow();
        }

        $count = $user->documents()->count();

        return $count < $limit
            ? Response::allow()
            : Response::deny("現在のプランではドキュメントは最大{$limit}件までです。プランをアップグレードしてください。");
    }

    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    /**
     * 添付ファイルはPro/Enterpriseのみ。
     */
    public function attach(User $user, Document $document): Response
    {
        if ($user->id !== $document->user_id) {
            return Response::deny('権限がありません。');
        }

        return $user->currentPlan()?->allowsAttachments()
            ? Response::allow()
            : Response::deny('添付ファイル機能はPro以上のプランでご利用いただけます。');
    }

    public function restore(User $user, Document $document): bool
    {
        return false;
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}
