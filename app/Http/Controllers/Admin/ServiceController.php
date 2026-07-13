<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\DisplayOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('display_order')->get();

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.form', ['service' => new Service, 'maxOrder' => DisplayOrder::maxForForm(Service::class)]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(4));
        DisplayOrder::save(new Service, $data);

        return redirect()->route('admin.services.index')->with('ok', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.form', compact('service') + ['maxOrder' => DisplayOrder::maxForForm(Service::class, $service)]);
    }

    public function update(Request $request, Service $service)
    {
        DisplayOrder::save($service, $this->validated($request));

        return redirect()->route('admin.services.index')->with('ok', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        DisplayOrder::delete($service);

        return back()->with('ok', 'Layanan dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'icon' => ['nullable', Rule::in(['halal', 'halal-reg', 'nib', 'akta', 'pajak', 'bpom', 'haki', 'desain'])],
            'summary' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'benefits' => ['nullable', 'string', 'max:1500'],
            'suitable_for' => ['nullable', 'string', 'max:1000'],
            'workflow_steps' => ['nullable', 'string', 'max:1500'],
            'whatsapp_message' => ['nullable', 'string', 'max:1000'],
            'display_order' => ['required', 'integer', 'min:1'],
        ]);
        $data['benefits'] = $this->normalizeLines($data['benefits'] ?? null);
        $data['workflow_steps'] = $this->normalizeLines($data['workflow_steps'] ?? null);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }

    private function normalizeLines(?string $value): ?string
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        return $lines->isEmpty() ? null : $lines->implode("\n");
    }
}
