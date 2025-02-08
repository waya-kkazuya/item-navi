<?php

namespace Tests\Feature\Models;

use App\Models\AcquisitionMethod;
use App\Models\Category;
use App\Models\Disposal;
use App\Models\Inspection;
use App\Models\Item;
use App\Models\Location;
use App\Models\Unit;
use App\Models\UsageStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function categoryリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Category::class, $item->category);
    }

    /** @test */
    public function unitリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Unit::class, $item->unit);
    }

    /** @test */
    public function usageStatusリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(UsageStatus::class, $item->usageStatus);
    }

    /** @test */
    public function locationOfUseリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Location::class, $item->locationOfUse);
    }

    /** @test */
    public function storageLocationリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Location::class, $item->storageLocation);
    }

    /** @test */
    public function acquisitionMethodリレーションを返す()
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(AcquisitionMethod::class, $item->acquisitionMethod);
    }

    /** @test */
    public function inspectionsリレーションを返す()
    {
        $item = Item::factory()->create();

        Inspection::withoutEvents(function () use ($item) {
            $inspections = Inspection::factory()->count(2)->create(['item_id' => $item->id]);
        });

        // hasMany1対多なので、Inspectionのインスタンスが単体で返ってくるわけではない
        $this->assertInstanceOf(Collection::class, $item->inspections);
        $this->assertInstanceOf(Inspection::class, $item->inspections[0]);
        $this->assertInstanceOf(Inspection::class, $item->inspections[1]);
    }

    /** @test */
    public function disposalリレーションを返す()
    {
        $item = Item::factory()->create();

        Disposal::withoutEvents(function () use ($item) {
            $disposal = Disposal::factory()->create(['item_id' => $item->id]);
        });

        $this->assertInstanceOf(Disposal::class, $item->disposal);
    }

    /** @test */
    public function stockTransactionsリレーションを返す()
    {
        $item = Item::factory()->create();

        // hasMany1対多なので、Inspectionのインスタンスが単体で返ってくるわけではない
        $this->assertInstanceOf(Collection::class, $item->stockTransactions);
    }
}
