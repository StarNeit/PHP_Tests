<?php

namespace Tests\Integration\Http\Controllers\API;

use MotionArray\Models\Product;
use MotionArray\Models\Resolution;
use MotionArray\Models\User;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class AutoDescriptionsControllerTest extends TestCase
{
    use RefreshAndSeedDatabase;

    /**
     * @var Product
     */
    protected $product;

    public function setUp()
    {
        parent::setUp();

        $seller = new User();
        $seller->email = 'test@test.test' . time();
        $seller->save();

        $resolution = Resolution::first();

        $this->product = new Product();
        $this->product->seller_id = $seller->id;
        $this->product->category_id = \MotionArray\Models\StaticData\Categories::AFTER_EFFECTS_TEMPLATES_ID;
        $this->product->product_status_id = \MotionArray\Models\StaticData\ProductStatuses::PUBLISHED_ID;
        $this->product->event_code_id = \MotionArray\Models\StaticData\EventCodes::READY_ID;
        $this->product->name = 'Test Name';
        $this->product->slug = 'test-name';
        $this->product->save();

        $this->product->resolutions()->save($resolution);
    }

    public function test_can_generate_stock_video_auto_descriptions()
    {
        $response = $this->get("/api/auto-descriptions/generate-stock-video-description/{$this->product->slug}");
        $response->assertSuccessful();
        $response->assertSee($this->product->name); // Auto descriptions will contain the product name.
        $response->assertSee('___'); // There will be space for authors or curators to fill in text.
    }
}
