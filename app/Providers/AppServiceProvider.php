<?php

namespace App\Providers;

use App\Models\LetterheadAsset;
use App\Models\PejabatPenandatangan;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('sk._opsi-surat', function ($view) {
            $view->with([
                'opsiKopList' => LetterheadAsset::orderByDesc('created_at')->get(),
                'opsiKopAktifId' => optional(LetterheadAsset::aktif())->id,
                'opsiPejabatList' => PejabatPenandatangan::orderByDesc('is_active')->orderBy('nama_lengkap')->get(),
                'opsiPejabatAktifId' => optional(PejabatPenandatangan::aktif())->id,
                'opsiTanggalDefault' => now()->format('Y-m-d'),
            ]);
        });
    }
}
