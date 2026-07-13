<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Support\DisplayOrder;
use App\Support\PublicMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AgendaController extends Controller
{
    public function index()
    {
        return view('admin.agendas.index', ['agendas' => Agenda::orderBy('display_order')->get()]);
    }

    public function create()
    {
        return view('admin.agendas.form', ['agenda' => new Agenda, 'maxOrder' => DisplayOrder::maxForForm(Agenda::class)]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request);
        $data['slug'] = $this->slug($data['title']);
        if ($request->hasFile('image')) {
            $data['image_path'] = PublicMedia::store($request->file('image'), 'agendas');
        }
        try {
            DisplayOrder::save(new Agenda, $data);
        } catch (\Throwable $e) {
            PublicMedia::delete($data['image_path'] ?? null);
            throw $e;
        }

        return redirect()->route('admin.agendas.index')->with('ok', 'Agenda ditambahkan.');
    }

    public function edit(Agenda $agenda)
    {
        return view('admin.agendas.form', compact('agenda') + ['maxOrder' => DisplayOrder::maxForForm(Agenda::class, $agenda)]);
    }

    public function update(Request $request, Agenda $agenda)
    {
        $data = $this->data($request, $agenda);
        $data['slug'] = $this->slug($data['title'], $agenda->id);
        $oldImage = $agenda->image_path;
        $newImage = null;
        if ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        } elseif ($request->hasFile('image')) {
            $newImage = PublicMedia::store($request->file('image'), 'agendas');
            $data['image_path'] = $newImage;
        }
        try {
            DisplayOrder::save($agenda, $data);
        } catch (\Throwable $e) {
            PublicMedia::delete($newImage);
            throw $e;
        }
        if (($newImage || $request->boolean('remove_image')) && $oldImage !== $newImage) {
            PublicMedia::delete($oldImage);
        }

        return redirect()->route('admin.agendas.index')->with('ok', 'Agenda diperbarui.');
    }

    public function destroy(Agenda $agenda)
    {
        $image = $agenda->image_path;
        DisplayOrder::delete($agenda);
        PublicMedia::delete($image);

        return back()->with('ok', 'Agenda dihapus.');
    }

    private function data(Request $request, ?Agenda $agenda = null): array
    {
        $startRules = ['required', 'date'];

        // Agenda tidak boleh dijadwalkan ke masa lalu. Tapi agenda yang sudah
        // berjalan tetap harus bisa disunting (mis. memperbaiki judul) selama
        // waktu mulainya tidak digeser — karena itu aturan ini hanya berlaku
        // saat membuat baru atau saat waktu mulai benar-benar diubah.
        if (! $this->keepsExistingStart($request, $agenda)) {
            $startRules[] = 'after:now';
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'venue' => ['nullable', 'string', 'max:255'],
            'starts_at' => $startRules,
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'registration_url' => ['nullable', 'url:http,https', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'display_order' => ['required', 'integer', 'min:1'],
        ], [
            'starts_at.after' => 'Waktu mulai tidak boleh di masa lalu.',
            'ends_at.after' => 'Waktu selesai harus lebih besar dari waktu mulai (tidak boleh sama atau lebih awal).',
        ]);
        unset($data['image'], $data['remove_image']);

        return $data + ['is_active' => $request->boolean('is_active')];
    }

    /** Waktu mulai agenda lama dipertahankan apa adanya (tidak digeser admin)? */
    private function keepsExistingStart(Request $request, ?Agenda $agenda): bool
    {
        if (! $agenda?->exists || ! $agenda->starts_at) {
            return false;
        }

        try {
            $submitted = Carbon::parse((string) $request->input('starts_at'));
        } catch (\Throwable) {
            return false; // Format tidak valid → biarkan validator yang menolak.
        }

        return $agenda->starts_at->equalTo($submitted);
    }

    private function slug(string $title, ?int $ignore = null): string
    {
        $base = Str::slug($title) ?: 'agenda';
        $slug = $base;
        $i = 2;
        while (Agenda::where('slug', $slug)->when($ignore, fn ($q) => $q->whereKeyNot($ignore))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
