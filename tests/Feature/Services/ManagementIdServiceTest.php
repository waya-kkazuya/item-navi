<?php

namespace Tests\Feature\Services;

use App\Services\ManagementIdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ManagementIdServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ManagementIdServiceがすべての接頭辞で管理IDを作成できるかテスト()
    {
        $idPrefixPairs = [
            1  => 'CO-',
            2  => 'IT-',
            3  => 'SA-',
            4  => 'EA-',
            5  => 'DP-',
            6  => 'OS-',
            7  => 'OF-',
            8  => 'TO-',
            9  => 'CL-',
            10 => 'GR-',
            11 => 'OT-',
        ];

        foreach ($idPrefixPairs as $category_id => $prefix) {
            $managementId = ManagementIdService::generate($category_id);
            $this->assertStringStartsWith($prefix, $managementId);
        }
    }

    /** @test */
    public function 無効境界値のIDでエラーがスローされるかテスト()
    {
        $this->expectException(ValidationException::class);
        ManagementIdService::generate(12);
    }
}
