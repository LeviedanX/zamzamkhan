<?php

namespace App\Support;

use App\Models\Agenda;

final class AgendaPurger
{
    /**
     * Hapus permanen agenda yang waktu selesainya sudah lewat, sekalian gambarnya
     * supaya tidak menumpuk di storage. Memakai DisplayOrder::delete agar urutan
     * agenda yang tersisa tetap rapat (1,2,3,...) tanpa nomor bolong.
     *
     * @return int Jumlah agenda yang dihapus.
     */
    public static function purgeFinished(): int
    {
        $finished = Agenda::finished()->get();

        foreach ($finished as $agenda) {
            $image = $agenda->image_path;
            DisplayOrder::delete($agenda);
            PublicMedia::delete($image);
        }

        return $finished->count();
    }
}
