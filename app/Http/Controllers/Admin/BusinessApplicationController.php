<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessApplicationRequest;
use App\Models\BusinessApplication;
use App\Models\BusinessCategory;
use App\Models\BusinessProcessStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BusinessApplicationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:255'],
            'applicant_type' => ['nullable', Rule::in(['company', 'individual'])],
            'process_status' => ['nullable', 'string', 'max:50', Rule::exists('business_process_statuses', 'name')],
            'business_category_id' => ['nullable', 'integer', Rule::exists('business_categories', 'id')],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $base = BusinessApplication::filtered($filters);
        $applications = (clone $base)->with('category')->latest('submitted_at')->latest('id')->paginate(15)->withQueryString();
        $summary = [
            'total' => (clone $base)->count(),
            'issued' => (clone $base)->whereIn('process_status', BusinessProcessStatus::namesForType('issued'))->count(),
            'ongoing' => (clone $base)->whereIn('process_status', BusinessProcessStatus::namesForType('ongoing'))->count(),
        ];
        $categories = BusinessCategory::where('is_active', true)->orderBy('name')->get();
        $statuses = BusinessProcessStatus::ordered()->get();

        return view('admin.applications.index', compact('applications', 'summary', 'filters', 'categories', 'statuses'));
    }

    public function create()
    {
        $application = new BusinessApplication;

        return view('admin.applications.form', [
            'application' => $application,
            'categories' => $this->categories($application),
            'statuses' => $this->statuses($application),
            'defaultStatus' => BusinessProcessStatus::defaultName(),
        ]);
    }

    public function store(BusinessApplicationRequest $request)
    {
        $data = $this->data($request) + ['created_by' => auth('admin')->id(), 'updated_by' => auth('admin')->id()];
        DB::transaction(function () use ($data, $request) {
            $application = BusinessApplication::create($data);
            $application->histories()->create(['new_status' => $application->process_status, 'note' => $request->input('status_note'), 'changed_by' => auth('admin')->id()]);
        });

        return redirect()->route('admin.applications.index')->with('ok', 'Data pengajuan ditambahkan.');
    }

    public function show(BusinessApplication $application)
    {
        return view('admin.applications.show', ['application' => $application->load(['category', 'histories.admin', 'creator', 'updater'])]);
    }

    public function edit(BusinessApplication $application)
    {
        return view('admin.applications.form', [
            'application' => $application,
            'categories' => $this->categories($application),
            'statuses' => $this->statuses($application),
            'defaultStatus' => BusinessProcessStatus::defaultName(),
        ]);
    }

    public function update(BusinessApplicationRequest $request, BusinessApplication $application)
    {
        $data = $this->data($request) + ['updated_by' => auth('admin')->id()];
        $old = $application->process_status;
        DB::transaction(function () use ($application, $data, $old, $request) {
            $application->update($data);
            if ($old !== $application->process_status) {
                $application->histories()->create(['old_status' => $old, 'new_status' => $application->process_status, 'note' => $request->input('status_note'), 'changed_by' => auth('admin')->id()]);
            }
        });

        return redirect()->route('admin.applications.show', $application)->with('ok', 'Data pengajuan diperbarui.');
    }

    public function destroy(BusinessApplication $application)
    {
        $application->delete();

        return redirect()->route('admin.applications.index')->with('ok', 'Data pengajuan dihapus.');
    }

    private function data(BusinessApplicationRequest $request): array
    {
        $data = $request->safe()->except(['status_note', 'new_business_category']);
        if ($name = trim((string) $request->input('new_business_category'))) {
            $data['business_category_id'] = BusinessCategory::firstOrCreate(['name' => $name], ['is_active' => true])->id;
        }

        return $data;
    }

    /**
     * Kategori aktif + kategori yang sedang dipakai pengajuan ini meski sudah
     * dinonaktifkan. Tanpa ini, membuka pengajuan berkategori nonaktif membuat
     * dropdown tidak punya opsi yang cocok, sehingga sekali disimpan kategorinya
     * hilang diam-diam (jadi kosong) tanpa admin sadar.
     */
    private function categories(BusinessApplication $application)
    {
        return BusinessCategory::where('is_active', true)
            ->when($application->business_category_id, fn ($q, $id) => $q->orWhere('id', $id))
            ->orderBy('name')
            ->get();
    }

    private function statuses(BusinessApplication $application)
    {
        return BusinessProcessStatus::active()
            ->when($application->process_status, fn ($q, $name) => $q->orWhere('name', $name))
            ->ordered()
            ->get();
    }
}
