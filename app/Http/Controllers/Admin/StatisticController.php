<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Statistic;
use App\Support\DisplayOrder;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function index() { return view('admin.content-items.index', ['items' => Statistic::orderBy('display_order')->get(), 'module' => 'Statistik', 'description' => 'Angka pencapaian yang tampil pada homepage.', 'routeName' => 'statistics', 'titleField' => 'label']); }
    public function create() { return view('admin.statistics.form', ['statistic' => new Statistic(), 'maxOrder' => DisplayOrder::maxForForm(Statistic::class)]); }
    public function store(Request $request) { DisplayOrder::save(new Statistic(), $this->data($request)); return redirect()->route('admin.statistics.index')->with('ok', 'Statistik ditambahkan.'); }
    public function edit(Statistic $statistic) { return view('admin.statistics.form', compact('statistic') + ['maxOrder' => DisplayOrder::maxForForm(Statistic::class, $statistic)]); }
    public function update(Request $request, Statistic $statistic) { DisplayOrder::save($statistic, $this->data($request)); return redirect()->route('admin.statistics.index')->with('ok', 'Statistik diperbarui.'); }
    public function destroy(Statistic $statistic) { DisplayOrder::delete($statistic); return back()->with('ok', 'Statistik dihapus.'); }
    private function data(Request $request): array { return $request->validate(['value' => ['required','string','max:40'], 'label' => ['required','string','max:160'], 'description' => ['nullable','string','max:255'], 'display_order' => ['required','integer','min:1']]) + ['is_active' => $request->boolean('is_active')]; }
}
