<?php

namespace Tests\Feature\Models;

use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Models\Unit;
use App\Models\UsageStatus;
use App\Models\AcquisitionMethod;
use App\Models\Disposal;
use App\Models\Inspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;


class ItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function categoryリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Category::class, $item->category);
    }

    /** @test */
    function unitリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Unit::class, $item->unit);
    }

    /** @test */
    function usageStatusリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(UsageStatus::class, $item->usageStatus);
    }
    
    /** @test */
    function locationOfUseリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Location::class, $item->locationOfUse);
    }

    /** @test */
    function storageLocationリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Location::class, $item->storageLocation);
    }

    /** @test */
    function acquisitionMethodリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(AcquisitionMethod::class, $item->acquisitionMethod);
    }

    /** @test */
    function inspectionsリレーションを返す()
    {
        $item = Item::factory()->create();

        // hasMany1対多なので、Inspectionのインスタンスが単体で返ってくるわけではない
        $this->assertInstanceOf(Collection::class, $item->inspections);
    }

    /** @test */
    function disposalリレーションを返す()
    {
        $item = Item::factory()->create();
        $disposal = Disposal::factory()->create(['item_id' => $item->id]);

        $this->assertInstanceOf(Disposal::class, $item->disposal);
    }

    /** @test */
    function stockTransactionsリレーションを返す()
    {
        $item = Item::factory()->create();

        // hasMany1対多なので、Inspectionのインスタンスが単体で返ってくるわけではない
        $this->assertInstanceOf(Collection::class, $item->stockTransactions);
    }
}
