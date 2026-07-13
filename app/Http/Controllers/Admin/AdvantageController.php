<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advantage;
use App\Support\DisplayOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdvantageController extends Controller
{
    public function index() { return view('admin.content-items.index', ['items' => Advantage::orderBy('display_order')->get(), 'module' => 'Keunggulan', 'description' => 'Alasan utama calon klien memilih perusahaan.', 'routeName' => 'advantages', 'titleField' => 'title']); }
    public function create() { return view('admin.advantages.form', ['advantage' => new Advantage(), 'maxOrder' => DisplayOrder::maxForForm(Advantage::class)]); }
    public function store(Request $request) { DisplayOrder::save(new Advantage(), $this->data($request)); return redirect()->route('admin.advantages.index')->with('ok', 'Keunggulan ditambahkan.'); }
    public function edit(Advantage $advantage) { return view('admin.advantages.form', compact('advantage') + ['maxOrder' => DisplayOrder::maxForForm(Advantage::class, $advantage)]); }
    public function update(Request $request, Advantage $advantage) { DisplayOrder::save($advantage, $this->data($request)); return redirect()->route('admin.advantages.index')->with('ok', 'Keunggulan diperbarui.'); }
    public function destroy(Advantage $advantage) { DisplayOrder::delete($advantage); return back()->with('ok', 'Keunggulan dihapus.'); }
    private function data(Request $request): array { return $request->validate(['title' => ['required','string','max:160'], 'icon' => ['nullable', Rule::in(array_keys(Advantage::ICONS))], 'description' => ['required','string','max:1000'], 'display_order' => ['required','integer','min:1']], ['icon.in' => 'Ikon yang dipilih tidak tersedia.']) + ['is_active' => $request->boolean('is_active')]; }
}
