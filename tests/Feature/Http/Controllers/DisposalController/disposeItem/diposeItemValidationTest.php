<?php
namespace Tests\Feature\Http\Controllers\DisposalController\disposeItem;

use App\Models\Item;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class diposeItemValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // 備品廃棄モーダルでのバリデーションテスト
    /** @test */
    public function 廃棄モーダルバリデーションdisposal_dateがnullで無効値な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['disposal_date' => null]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.disposal_date')
                ->where('errors.disposal_date', '廃棄実施日は必ず指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdisposal_dateが文字列で無効値な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['disposal_date' => 'あ']);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.disposal_date')
                ->where('errors.disposal_date', '廃棄実施日には有効な日付を指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdisposal_dateが数字で無効値な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['disposal_date' => 1]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.disposal_date')
                ->where('errors.disposal_date', '廃棄実施日には有効な日付を指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdisposal_personがnullで無効値な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['disposal_person' => null]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.disposal_person')
                ->where('errors.disposal_person', '廃棄実施者は必ず指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdisposal_personが1文字で有効値な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['disposal_person' => str_repeat('あ', 1)]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->missing('errors.disposal_person')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdisposal_personが10文字で有効境界値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['disposal_person' => str_repeat('あ', 10)]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->missing('errors.disposal_person')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdisposal_personが11文字で無効境界値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['disposal_person' => str_repeat('あ', 11)]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.disposal_person')
                ->where('errors.disposal_person', '廃棄実施者は、10文字以下で指定してください。')
                // ->dump()
        );
    }

    // 詳細情報のバリデーション
    /** @test */
    public function 廃棄モーダルバリデーションdetailsが空で無効値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['details' => '']);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.details')
                ->where('errors.details', '詳細情報は必ず指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdetailsが1文字で有効値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['details' => str_repeat('あ', 1)]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->missing('errors.details')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdetailsが200文字で有効境界値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['details' => str_repeat('あ', 200)]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->missing('errors.details')
                // ->dump()
        );
    }

    /** @test */
    public function 廃棄モーダルバリデーションdetailsが201文字で無効境界値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), ['details' => str_repeat('あ', 201)]);
        $response->assertRedirect('items/' . $item->id); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.details')
                ->where('errors.details', '詳細情報は、200文字以下で指定してください。')
                // ->dump()
        );
    }
}
