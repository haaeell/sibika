<?php

if (! function_exists('status_badge')) {
    /**
     * @return array{label: string, class: string, variant: string}
     */
    function status_badge(string $status): array
    {
        return match ($status) {
            'verified', 'active', 'graduated' => [
                'label' => 'Terverifikasi',
                'class' => 'bg-emerald-50 text-emerald-700',
                'variant' => 'success',
            ],
            'submitted', 'pending' => [
                'label' => 'Menunggu',
                'class' => 'bg-amber-50 text-amber-700',
                'variant' => 'warning',
            ],
            'rejected', 'inactive' => [
                'label' => 'Ditolak',
                'class' => 'bg-rose-50 text-rose-700',
                'variant' => 'danger',
            ],
            default => [
                'label' => str($status)->headline()->toString(),
                'class' => 'bg-slate-100 text-slate-600',
                'variant' => 'neutral',
            ],
        };
    }
}
