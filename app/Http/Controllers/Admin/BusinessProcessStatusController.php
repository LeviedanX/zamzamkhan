<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessProcessStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BusinessProcessStatusController extends Controller
{
    public function index()
    {
        $statuses = BusinessProcessStatus::withCount([
            'applications',
            'newStatusHistories',
            'oldStatusHistories',
        ])->ordered()->get();

        return view('admin.process-statuses.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        DB::transaction(function () use ($data) {
            BusinessProcessStatus::query()->lockForUpdate()->get();
            if ($data['is_default']) {
                BusinessProcessStatus::where('is_default', true)->update(['is_default' => false]);
                $data['is_active'] = true;
            }
            BusinessProcessStatus::create($data);
        });

        return redirect()->route('admin.process-statuses.index')->with('ok', 'Jenis status proses ditambahkan.');
    }

    public function update(Request $request, BusinessProcessStatus $processStatus)
    {
        $data = $this->validated($request, $processStatus);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');
        $isUsed = $processStatus->applications()->exists()
            || $processStatus->newStatusHistories()->exists()
            || $processStatus->oldStatusHistories()->exists();

        if ($isUsed && $data['name'] !== $processStatus->name) {
            return redirect()->route('admin.process-statuses.index')->withInput()->with('error', 'Nama status yang sudah dipakai tidak dapat diubah. Tambahkan status baru lalu nonaktifkan status lama.');
        }
        if ($processStatus->is_default && ! $data['is_default']) {
            return redirect()->route('admin.process-statuses.index')->withInput()->with('error', 'Status default tidak dapat dilepas langsung. Jadikan status lain sebagai default terlebih dahulu.');
        }
        if ($processStatus->is_default && ! $data['is_active']) {
            return redirect()->route('admin.process-statuses.index')->withInput()->with('error', 'Status default wajib tetap aktif.');
        }

        DB::transaction(function () use ($data, $processStatus) {
            BusinessProcessStatus::query()->lockForUpdate()->get();
            if ($data['is_default']) {
                BusinessProcessStatus::whereKeyNot($processStatus->id)->update(['is_default' => false]);
                $data['is_active'] = true;
            }
            $processStatus->update($data);
        });

        return redirect()->route('admin.process-statuses.index')->with('ok', 'Jenis status proses diperbarui.');
    }

    public function destroy(BusinessProcessStatus $processStatus)
    {
        if ($processStatus->is_default) {
            return redirect()->route('admin.process-statuses.index')->with('error', 'Status default tidak dapat dihapus. Jadikan status lain sebagai default terlebih dahulu.');
        }

        if ($processStatus->applications()->exists()
            || $processStatus->newStatusHistories()->exists()
            || $processStatus->oldStatusHistories()->exists()) {
            return redirect()->route('admin.process-statuses.index')->with('error', 'Status "'.$processStatus->name.'" masih dipakai pengajuan atau riwayat. Nonaktifkan status ini agar tidak muncul pada pengajuan baru.');
        }

        $processStatus->delete();

        return redirect()->route('admin.process-statuses.index')->with('ok', 'Jenis status proses dihapus.');
    }

    private function validated(Request $request, ?BusinessProcessStatus $processStatus = null): array
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50', Rule::unique('business_process_statuses', 'name')->ignore($processStatus?->id)],
            'type' => ['required', Rule::in(array_keys(BusinessProcessStatus::TYPES))],
            'display_order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo(route('admin.process-statuses.index'));
        }

        return $validator->validated();
    }
}
