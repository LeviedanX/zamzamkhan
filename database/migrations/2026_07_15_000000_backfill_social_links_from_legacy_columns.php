<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * social_links (JSON) was added after instagram_url/facebook_url/tiktok_url
 * already had data. The admin form only reads/writes social_links, so an
 * existing site_settings row kept showing "0 akun" even though those three
 * legacy columns were populated and still rendering on the public footer.
 *
 * This is a one-time, idempotent backfill: it only touches rows where
 * social_links is still empty, so it never overwrites a value an admin has
 * already saved through the new UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')
            ->whereNull('social_links')
            ->orderBy('id')
            ->get(['id', 'instagram_url', 'facebook_url', 'tiktok_url'])
            ->each(function ($row) {
                $links = collect([
                    ['label' => 'Instagram', 'url' => $row->instagram_url],
                    ['label' => 'Facebook', 'url' => $row->facebook_url],
                    ['label' => 'TikTok', 'url' => $row->tiktok_url],
                ])->filter(fn ($item) => filled($item['url']))->values();

                if ($links->isEmpty()) {
                    return;
                }

                DB::table('site_settings')
                    ->where('id', $row->id)
                    ->update(['social_links' => $links->toJson()]);
            });
    }

    public function down(): void
    {
        // Data backfill, tidak ada skema untuk dikembalikan.
    }
};
