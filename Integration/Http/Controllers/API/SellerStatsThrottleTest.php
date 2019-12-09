<?php

namespace Tests\Integration\Http\Controllers\API;

use Tests\TestCase;

class SellerStatsThrottleTest extends TestCase
{
    /** @test */
    public function test_api_throttle()
    {
        $this->markTestSkipped('FIXME: Fails.');
        // create a seller
        $limit = 30;

        for($i = 0; $i<=$limit; $i++) {
            $response = $this->get("/api/site/stats?month=12&year=2018");
        }

        $response->assertSee('Too Many Attempts'); // Auto descriptions will contain the product name.
    }
}
