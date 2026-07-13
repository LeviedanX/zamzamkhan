@extends('layouts.admin')
@section('title', $module)

@section('content')
<x-admin.page-header eyebrow="Konten Website" :title="$module" :description="$description">
    <x-slot:actions>
        <a class="btn-primary" href="{{ route('admin.'.$routeName.'.create') }}">+ Tambah {{ $module }}</a>
    </x-slot:actions>
</x-admin.page-header>

@if ($items->isEmpty())
    @include('admin.partials.empty-state', [
        'title' => 'Belum ada '.$module,
        'description' => 'Tambahkan data pertama agar modul '.$module.' memiliki konten.',
        'action' => ['href' => route('admin.'.$routeName.'.create'), 'label' => 'Tambah '.$module],
        'icon' => 'M5 12h14M12 5v14',
    ])
@else
    <div class="admin-table-card overflow-x-auto">
        <table class="w-full min-w-[680px] text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left">Urutan</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr class="border-t">
                        <td class="px-4 py-3 text-[var(--admin-muted)]">{{ $item->display_order }}</td>
                        <td class="px-4 py-3 font-semibold text-[var(--admin-ink)]">{{ $item->{$titleField} }}</td>
                        <td class="px-4 py-3 text-center">@include('admin.partials.status-badge', ['active' => $item->is_active])</td>
                        <td class="px-4 py-3">@include('admin.partials.row-actions', [
                            'edit' => route('admin.'.$routeName.'.edit', $item),
                            'delete' => route('admin.'.$routeName.'.destroy', $item),
                            'name' => $item->{$titleField},
                        ])</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

