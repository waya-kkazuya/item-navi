<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemControllerTest extends TestCase
{
    /** @test */
    function 備品一覧が表示される()
    {
        $this->get('items')
            ->assertOk();
    }
}
