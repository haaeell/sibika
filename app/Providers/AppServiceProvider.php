<?php

namespace App\Providers;

use App\Models\AcademicYear;
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
        View::composer('components.layout.sidebar', function ($view) {
            $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('start_year')->get();
            $sessionYear = session('academic_year');
            $activeAcademicYear = $academicYears->firstWhere('name', $sessionYear)?->name
                ?? $academicYears->firstWhere('is_active', true)?->name
                ?? $academicYears->first()?->name
                ?? '2026 / 2027';

            $view->with(compact('academicYears', 'activeAcademicYear'));
        });
    }
}
