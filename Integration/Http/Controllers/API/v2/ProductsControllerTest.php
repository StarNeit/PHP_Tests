<?php

namespace Tests\Integration\Http\Controllers\API\v2;

use MotionArray\Models\Role;
use MotionArray\Models\User;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class ProductsControllerTest extends TestCase
{
    use RefreshAndSeedDatabase;

    /**
     * @var User
     */
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = new User();
        $this->user->email = 'test@test.test' . time();
        $this->user->confirmed = 1;
        $this->user->save();
    }

    public function test_can_create_products_with_ma_slugs()
    {
        $data = [
            "product" => [
                "name" => "Test Product Name",
                "category_id" => 2,
                "compression" => "prores-4444",
                "fps" => "59-94-fps",
                "resolution" => "7680x4320-8k"
            ]
        ];
        $response = $this
            ->actingAs($this->user, 'api')
            ->json('POST', '/api/v2/products', $data);

        $response->assertSuccessful();
    }

    public function test_can_create_products_with_ffmpeg_slugs()
    {
        $data = [
            "product" => [
                "name" => "Test Product Name",
                "category_id" => 2,
                "compression" => "mjpeg-yuvj422p",
                "fps" => "25/1",
                "resolution" => "7680x4320"
            ]
        ];
        $response = $this
            ->actingAs($this->user, 'api')
            ->json('POST', '/api/v2/products?useEncoderSlugs=true', $data);

        $response->assertSuccessful();
    }

    public function test_can_create_products_with_mjpeg_yuvj420p_compression()
    {
        $data = [
            "product" => [
                "name" => "Test Product Name",
                "category_id" => 2,
                "compression" => "mjpeg-yuvj420p",
                "fps" => "25/1",
                "resolution" => "7680x4320"
            ]
        ];
        $response = $this
            ->actingAs($this->user, 'api')
            ->json('POST', '/api/v2/products?useEncoderSlugs=true', $data);

        $response->assertSuccessful();
    }

    public function test_can_not_create_products_with_wrong_resolution()
    {
        $data = [
            "product" => [
                "name" => "Test Product Name",
                "category_id" => 2,
                "compression" => "prores-yuv422p10le",
                "fps" => "25/1",
                "resolution" => "120x4320"
            ]
        ];

        $response = $this
            ->actingAs($this->user, 'api')
            ->json('POST', '/api/v2/products?useEncoderSlugs=true', $data);

        $response->assertStatus(422);
    }

    public function test_can_not_create_products_with_wrong_compression()
    {
        $data = [
            "product" => [
                "name" => "Test Product Name",
                "category_id" => 2,
                "compression" => "wrong-compression",
                "fps" => "25/1",
                "resolution" => "7680x4320"
            ]
        ];

        $response = $this
            ->actingAs($this->user, 'api')
            ->json('POST', '/api/v2/products?useEncoderSlugs=true', $data);

        $response->assertStatus(422);
    }

    public function test_can_not_create_products_with_wrong_fps()
    {
        $data = [
            "product" => [
                "name" => "Test Product Name",
                "category_id" => 2,
                "compression" => "prores-yuv422p10le",
                "fps" => "2566",
                "resolution" => "7680x4320"
            ]
        ];

        $response = $this
            ->actingAs($this->user, 'api')
            ->json('POST', '/api/v2/products?useEncoderSlugs=true', $data);

        $response->assertStatus(422);
    }
}
