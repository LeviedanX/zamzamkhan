<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentReplacementRun;
use App\Services\ContentReplacementService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShortcutController extends Controller
{
    public function __construct(private readonly ContentReplacementService $replacements) {}

    public function index(Request $request)
    {
        $preview = null;
        $visibleToken = $request->session()->get('shortcut_preview_visible');
        $previewState = $request->session()->get('shortcut_preview');

        if (is_string($visibleToken)
            && is_array($previewState)
            && hash_equals((string) ($previewState['token'] ?? ''), $visibleToken)
            && (int) ($previewState['admin_id'] ?? 0) === (int) $request->user('admin')?->id) {
            $preview = $previewState;
        }

        $runs = ContentReplacementRun::query()
            ->with(['creator:id,name', 'reverter:id,name'])
            ->withCount(['changes as reverted_fields' => fn ($query) => $query->whereNotNull('reverted_at')])
            ->latest('executed_at')
            ->paginate(12);

        return view('admin.shortcuts.index', [
            'clusters' => $this->replacements->clusters(),
            'preview' => $preview,
            'runs' => $runs,
        ]);
    }

    public function preview(Request $request)
    {
        $data = $this->validatedReplacement($request);

        try {
            $result = $this->replacements->preview(
                $data['cluster'],
                $data['search_text'],
                $data['replacement_text'],
                $data['case_sensitive'],
            );
        } catch (DomainException $exception) {
            return redirect()->route('admin.shortcuts.index')->withInput()->with('error', $exception->getMessage());
        }

        $token = Str::random(64);
        $state = [
            'token' => $token,
            'admin_id' => $request->user('admin')?->id,
            'cluster' => $data['cluster'],
            'cluster_label' => $this->replacements->clusterLabel($data['cluster']),
            'search_text' => $data['search_text'],
            'replacement_text' => $data['replacement_text'],
            'case_sensitive' => $data['case_sensitive'],
            'fingerprint' => $result['fingerprint'],
            'result' => collect($result)->except(['fingerprint', 'changes'])->all(),
            'created_at' => now()->toIso8601String(),
        ];

        $request->session()->put('shortcut_preview', $state);

        return redirect()->route('admin.shortcuts.index')
            ->with('shortcut_preview_visible', $token);
    }

    public function apply(Request $request)
    {
        $request->validate([
            'preview_token' => ['required', 'string', 'size:64'],
        ]);

        $state = $request->session()->get('shortcut_preview');
        if (! is_array($state)
            || ! hash_equals((string) ($state['token'] ?? ''), (string) $request->input('preview_token'))
            || (int) ($state['admin_id'] ?? 0) !== (int) $request->user('admin')?->id) {
            return redirect()->route('admin.shortcuts.index')
                ->with('error', 'Pratinjau tidak valid atau sudah kedaluwarsa. Buat pratinjau baru.');
        }

        try {
            $run = $this->replacements->execute(
                $state['cluster'],
                $state['search_text'],
                $state['replacement_text'],
                (bool) $state['case_sensitive'],
                $state['fingerprint'],
                $request->user('admin')?->id,
            );
        } catch (DomainException $exception) {
            $request->session()->forget('shortcut_preview');

            return redirect()->route('admin.shortcuts.index')->with('error', $exception->getMessage());
        }

        $request->session()->forget('shortcut_preview');

        return redirect()->route('admin.shortcuts.index')->with(
            'ok',
            "Penggantian selesai: {$run->occurrence_count} kemunculan pada {$run->affected_fields} kolom berhasil diperbarui.",
        );
    }

    public function undo(Request $request, ContentReplacementRun $contentReplacementRun)
    {
        $result = $this->replacements->undo($contentReplacementRun, $request->user('admin')?->id);

        if ($result['conflicts'] > 0) {
            return redirect()->route('admin.shortcuts.index')->with(
                'error',
                "Undo memulihkan {$result['restored']} kolom. {$result['conflicts']} kolom dilewati karena sudah diedit lagi atau kontennya tidak tersedia.",
            );
        }

        return redirect()->route('admin.shortcuts.index')->with(
            'ok',
            "Undo selesai: {$result['restored']} kolom dikembalikan ke nilai sebelumnya.",
        );
    }

    private function validatedReplacement(Request $request): array
    {
        $clusters = array_keys($this->replacements->clusters());
        $plainText = function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string($value)) {
                return;
            }
            if (preg_match('/[<>]/', $value)) {
                $fail('Kolom :attribute tidak boleh memuat tag HTML.');
            }
            if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                $fail('Kolom :attribute memuat karakter kontrol yang tidak valid.');
            }
        };

        $validator = Validator::make($request->all(), [
            'cluster' => ['required', Rule::in($clusters)],
            'search_text' => ['required', 'string', 'max:2000', $plainText],
            'replacement_text' => ['nullable', 'string', 'max:5000', $plainText],
            'case_sensitive' => ['nullable', 'boolean'],
        ], [], [
            'cluster' => 'kluster',
            'search_text' => 'teks yang dicari',
            'replacement_text' => 'teks pengganti',
        ]);

        $validator->after(function ($validator) use ($request) {
            $search = (string) $request->input('search_text', '');
            $replacement = (string) $request->input('replacement_text', '');

            if (mb_strlen(trim($search)) < 2) {
                $validator->errors()->add('search_text', 'Teks pencarian minimal 2 karakter non-spasi.');
            }
            if ($search === $replacement) {
                $validator->errors()->add('replacement_text', 'Teks pengganti harus berbeda dari teks yang dicari.');
            }
        });

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo(route('admin.shortcuts.index'));
        }

        $data = $validator->validated();
        $data['replacement_text'] = (string) ($data['replacement_text'] ?? '');
        $data['case_sensitive'] = $request->boolean('case_sensitive');

        return $data;
    }
}
