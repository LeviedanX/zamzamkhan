<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Support\DisplayOrder;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('display_order')->get();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.form', ['faq' => new Faq, 'maxOrder' => DisplayOrder::maxForForm(Faq::class)]);
    }

    public function store(Request $request)
    {
        DisplayOrder::save(new Faq, $this->validated($request));

        return redirect()->route('admin.faqs.index')->with('ok', 'FAQ berhasil ditambahkan.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', compact('faq') + ['maxOrder' => DisplayOrder::maxForForm(Faq::class, $faq)]);
    }

    public function update(Request $request, Faq $faq)
    {
        DisplayOrder::save($faq, $this->validated($request));

        return redirect()->route('admin.faqs.index')->with('ok', 'FAQ berhasil diperbarui.');
    }

    public function destroy(Faq $faq)
    {
        DisplayOrder::delete($faq);

        return back()->with('ok', 'FAQ dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:2000'],
            'display_order' => ['required', 'integer', 'min:1'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
