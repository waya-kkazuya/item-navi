<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // protected function setUp(): void
    // {
    //     parent::setUp();
        
    //     // 環境変数に基づいてCSRFミドルウェアを無効にするかどうかを決定
    //     if (env('DISABLE_CSRF', false)) {
    //         \Log::info('CSRFミドルウェア無効化');
    //         $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    //     }
    // }
}
