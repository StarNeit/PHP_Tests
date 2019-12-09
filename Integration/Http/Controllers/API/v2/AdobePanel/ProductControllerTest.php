<?php

namespace Tests\Integration\Http\Controllers\API\v2\AdobePanel;

use MotionArray\Models\Product;
use MotionArray\Models\PreviewUpload;
use MotionArray\Models\PreviewFile;
use MotionArray\Services\AdobePanel\AdobePanelService;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;
use App;

class ProductControllerTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function test_product_detail_endpoint()
    {
        $activePreview = factory(PreviewUpload::class)->create([
        ]);
        // Attach a file to preview
        factory(PreviewFile::class)->create([
            'preview_upload_id' => $activePreview->id,
            'label' => PreviewFile::MP4_HIGH,
            'url' => 'test.com/product'
        ]);
        $product = factory(Product::class)->create([
            'active_preview_id' => $activePreview->id,
            'free' => 1
        ]);

        $adobePanelService = App::make(AdobePanelService::class);
        $preparedProduct = $adobePanelService->prepareProduct($product);
        $request = $this->get(
            "adobe-panel/api/products/{$product->id}"
        );

        $request->assertStatus(200);

        $expected = [
            'id' => $product->id,
            'seller_id' => $product->seller_id,
            'music_id' => null,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'audio_placeholder' => null,
            'free' => 1,
            'is_editorial_use' => false,
            'category' => $product->category->name,
            'type' => 'video',
            'poster' => '/assets/images/site/thumb_placeholder.png',
            'source' => [],
            'specs' => [],
        ];

        $this->assertEquals($expected, $request->json());
    }
}
