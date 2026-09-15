<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\LoginSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Deteksi regresi lambat di production (tanpa infra tambahan).
        if (! app()->isLocal()) {
            DB::whenQueryingForLongerThan(500, function ($connection, $query): void {
                Log::warning('slow_query', ['sql' => $query->sql, 'time' => $query->time, 'connection' => $connection->getName()]);
            });
        }

        View::composer('components.layout.sidebar', function ($view) {
            $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get();
            $sessionYear = session('academic_year');
            $activeAcademicYear = $academicYears->firstWhere('name', $sessionYear)?->name
                ?? $academicYears->firstWhere('is_active', true)?->name
                ?? $academicYears->first()?->name
                ?? '2026 / 2027';

            $view->with(compact('academicYears', 'activeAcademicYear'));
        });

        View::composer(['components.layout.sidebar', 'components.layout.header'], function ($view) {
            $view->with('appSetting', LoginSetting::current());
        });
    }
}
