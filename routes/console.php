<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agenda selesai tetap disimpan sebagai histori CMS. Homepage sudah menyaring
// agenda berdasarkan waktu selesai, jadi tidak perlu penghapusan otomatis.
Schedule::command('operational:purge')->dailyAt('02:15')->withoutOverlapping();
