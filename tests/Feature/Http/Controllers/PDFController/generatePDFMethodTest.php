<?php

namespace Tests\Feature\Http\Controllers\PDFController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use App\Models\User;

class generatePDFMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    protected function tearDown(): void
    {
        // 子クラスでのクリーンアップ処理
        parent::tearDown();
    }

    // /** @test */
    // function PDFをブラウザの新規タブで開く()
    // {
    //     // 世界の構築が不十分
    //     // この時点では備品、消耗品が１つもないので、
    //     // QRラベルが1枚も存在しない
    //     // QRラベル画像が実際に存在しないとPDFを生成できない

    //     // adminユーザーを作成
    //     $user = User::factory()->role(1)->create();
    //     $this->actingAs($user);

    //     // PDFを別タブで開く
    //     $response = $this->from(route('consumable_items'))
    //         ->get('/generate-pdf');
    //     $response->assertStatus(302); //別タブを開く

    //     $response = $this->followRedirects($response);

    //     // レスポンスのContent-TypeがPDFであることを確認
    //     // $response->assertHeader('Content-Type', 'application/pdf');        
    //     $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');        

    //     // レスポンスのContent-Dispositionが inline であることを確認
    //     // dd($response->headers->all());
    //     $response->assertHeader('Content-Disposition', 'inline; filename="消耗品QRコード.pdf');

    //     // レスポンスの内容がPDFデータであることを確認（例：特定のバイナリデータが含まれているか）
    //     $content = $response->getContent();
    //     // dd($content);
    //     $this->assertTrue(str_contains($content, '%PDF'), 'Response does not contain PDF header');
    // }
}
