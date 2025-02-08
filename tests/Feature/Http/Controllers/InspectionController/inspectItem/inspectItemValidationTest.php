<?php

namespace Tests\Feature\Http\Controllers\InspectionController\inspectItem;

use App\Models\Item;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class inspectItemValidationTest extends TestCase
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

    // 点検予定日のレコードがない場合でテスト
    /** @test */
    public function 点検モーダルバリデーションinspection_dateがnullで無効値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成
        $item = Item::factory()->create();

        // 備品をソフトデリート
        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['inspection_date' => null]);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.inspection_date')
                ->where('errors.inspection_date', '点検実施日は必ず指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションinspection_dateが文字列で無効値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成
        $item = Item::factory()->create();

        // 備品をソフトデリート
        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['inspection_date' => 'あ']);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.inspection_date')
                ->where('errors.inspection_date', '点検実施日には有効な日付を指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションinspection_dateが数字で無効値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成
        $item = Item::factory()->create();

        // 備品をソフトデリート
        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['inspection_date' => 1]);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.inspection_date')
                ->where('errors.inspection_date', '点検実施日には有効な日付を指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションinspection_personがnullの場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['inspection_person' => null]);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.inspection_person')
                ->where('errors.inspection_person', '点検実施者は必ず指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションinspection_personが1文字で有効値な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['inspection_person' => str_repeat('あ', 1)]);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->missing('errors.inspection_person')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションinspection_personが10文字で有効境界値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['inspection_person' => str_repeat('あ', 10)]);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->missing('errors.inspection_person')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションinspection_personが11文字で無効境界値の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['inspection_person' => str_repeat('あ', 11)]);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->has('errors.inspection_person')
                ->where('errors.inspection_person', '点検実施者は、10文字以下で指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションdetailsがnullで無効の場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['details' => null]);
        $response->assertRedirect('items/' . $item->id);
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
    public function 点検モーダルバリデーションdetailsが1文字で有効な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['details' => str_repeat('あ', 1)]);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->missing('errors.details')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションdetailsが200文字で有効境界値な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['details' => str_repeat('あ', 200)]);
        $response->assertRedirect('items/' . $item->id);
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Items/Show')
                ->missing('errors.details')
                // ->dump()
        );
    }

    /** @test */
    public function 点検モーダルバリデーションdetailsが201文字で無効境界値な場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/' . $item->id)
            ->put(route('inspect_item.inspectItem', $item->id), ['details' => str_repeat('あ', 201)]);
        $response->assertRedirect('items/' . $item->id);
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
