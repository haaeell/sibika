<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportChartExportTest extends TestCase
{
    use RefreshDatabase;

    private const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function test_bk_can_export_single_chart_to_pdf(): void
    {
        $response = $this->actingAs($this->bkUser())
            ->postJson(route('bk.reports.charts.export'), [
                'charts' => [
                    ['title' => 'Status Kelengkapan', 'image' => 'data:image/png;base64,'.self::TINY_PNG],
                ],
            ])
            ->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('attachment; filename=grafik-', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $this->responseContent($response));
    }

    public function test_bulk_export_contains_all_charts(): void
    {
        $response = $this->actingAs($this->bkUser())
            ->postJson(route('bk.reports.charts.export'), [
                'charts' => [
                    ['title' => 'Grafik Satu', 'image' => self::TINY_PNG],
                    ['title' => 'Grafik Dua', 'image' => self::TINY_PNG],
                ],
            ])
            ->assertOk();

        $content = $this->responseContent($response);

        $this->assertStringStartsWith('%PDF', $content);
        $this->assertGreaterThan(1000, strlen($content));
    }

    public function test_chart_export_rejects_invalid_payload(): void
    {
        $user = $this->bkUser();

        $this->actingAs($user)
            ->postJson(route('bk.reports.charts.export'), ['charts' => []])
            ->assertUnprocessable();

        $this->actingAs($user)
            ->postJson(route('bk.reports.charts.export'), [
                'charts' => [['title' => 'Rusak', 'image' => 'bukan-gambar'],
                ],
            ])
            ->assertUnprocessable();
    }

    public function test_guest_cannot_export_charts(): void
    {
        $this->postJson(route('bk.reports.charts.export'), ['charts' => []])
            ->assertUnauthorized();
    }

    private function responseContent($response): string
    {
        return (string) $response->getContent();
    }

    private function bkUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        return $user;
    }
}
