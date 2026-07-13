<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advantage extends Model
{
    /**
     * Ikon yang benar-benar punya SVG di partials/keunggulan.blade.php.
     * Jadi satu sumber kebenaran untuk dropdown admin sekaligus validasi,
     * supaya admin tidak bisa menyimpan kode ikon yang tidak akan pernah tampil.
     */
    public const ICONS = [
        'clipboard' => 'Dokumen & Checklist',
        'chat' => 'Konsultasi & Komunikasi',
        'users' => 'Tim & Pelaku UMKM',
        'shield' => 'Legalitas & Perlindungan',
        'star' => 'Kualitas & Keunggulan',
        'pin' => 'Lokasi & Jangkauan',
    ];

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];
}
