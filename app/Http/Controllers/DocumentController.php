<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Document::class);

        return view('documents.index', [
            'documents' => $request->user()->documents()->latest()->get(),
            'plan' => $request->user()->currentPlan(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Document::class);

        return view('documents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Document::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        // 添付不可なプランで先にドキュメントだけ作られてしまわないよう、作成前に判定する。
        if ($request->hasFile('attachment') && ! $request->user()->currentPlan()?->allowsAttachments()) {
            abort(403, '添付ファイル機能はPro以上のプランでご利用いただけます。');
        }

        $document = $request->user()->documents()->create([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
        ]);

        $this->storeAttachmentIfPresent($request, $document);

        return redirect()->route('documents.index')->with('status', 'ドキュメントを作成しました。');
    }

    public function show(Document $document): View
    {
        Gate::authorize('view', $document);

        return view('documents.show', ['document' => $document]);
    }

    public function edit(Document $document): View
    {
        Gate::authorize('update', $document);

        return view('documents.edit', ['document' => $document]);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('update', $document);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $document->update([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
        ]);

        $this->storeAttachmentIfPresent($request, $document);

        return redirect()->route('documents.index')->with('status', 'ドキュメントを更新しました。');
    }

    public function destroy(Document $document): RedirectResponse
    {
        Gate::authorize('delete', $document);

        $document->delete();

        return redirect()->route('documents.index')->with('status', 'ドキュメントを削除しました。');
    }

    private function storeAttachmentIfPresent(Request $request, Document $document): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        Gate::authorize('attach', $document);

        $path = $request->file('attachment')->store('attachments/'.$document->user_id, 'local');

        $document->update(['attachment_path' => $path]);
    }
}
