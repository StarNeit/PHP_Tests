<?php

namespace Tests\Integration\Http\Controllers\API\v2\AdobePanel;

use Carbon\Carbon;
use MotionArray\Models\User;
use MotionArray\Models\Product;
use MotionArray\Repositories\AutoDescriptionRepository;
use MotionArray\Models\Download;
use MotionArray\Models\PreviewUpload;
use MotionArray\Models\PreviewFile;
use MotionArray\Repositories\Products\ProductRepository;
use MotionArray\Models\StaticData\Helpers\CategoriesWithRelationsHelper;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\Concerns\SubscriptionTestsHelper;
use Tests\TestCase;
use App;
use Mockery;

class DownloadControllerTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use SubscriptionTestsHelper;

    public function test_user_can_download_free_product()
    {
        $downloadUrl = 'test.com/product';
        $user = $this->getConfirmedUser();
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);
        $this->mockProductRepository($downloadUrl);

        $request = $this->actingAs($user, 'api')
            ->get("adobe-panel/api/products/{$product->id}/download-url");

        $request->assertStatus(200);
        $request->assertExactJson([
            'url' => $downloadUrl
        ]);
    }

    public function test_non_paid_user_can_not_download_non_free_product()
    {
        $user = $this->getConfirmedUser();
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);
        $request = $this->actingAs($user, 'api')
            ->get("adobe-panel/api/products/{$product->id}/download-url");

        $request->assertStatus(400);

        $request->assertExactJson([
            'message' => 'user is not a paying member'
        ]);
    }

    public function test_user_can_get_downloaded_product_list()
    {
        $user = $this->getConfirmedUser();
        $firstDownloadedTime = Carbon::now()->subMinute(30);
        $secondDownloadedTime = Carbon::now()->subMinute(10);
        $firstDownloadedProduct = $this->getDownloadedProduct($user, $firstDownloadedTime);
        $secondDownloadProduct = $this->getDownloadedProduct($user, $secondDownloadedTime);

        $requestObject = [
            'page' => 1,
            'perPage' => 60
        ];

        $request = $this->actingAs($user, 'api')->postJson(
            "adobe-panel/api/products/user-downloads",
            $requestObject
        );

        $request->assertStatus(200);

        $expected = [
            'products' => [
                [
                    'id' => $secondDownloadProduct->id,
                    'seller_id' => $secondDownloadProduct->seller_id,
                    'music_id' => null,
                    'name' => $secondDownloadProduct->name,
                    'slug' => $secondDownloadProduct->slug,
                    'description' => $secondDownloadProduct->description,
                    'audio_placeholder' => null,
                    'free' => 1,
                    'is_editorial_use' => false,
                    'category' => $secondDownloadProduct->category->name,
                    'type' => 'video',
                    'poster' => '/assets/images/site/thumb_placeholder.png',
                    'source' => [],
                    'specs' => [],
                ],
                [
                    'id' => $firstDownloadedProduct->id,
                    'seller_id' => $firstDownloadedProduct->seller_id,
                    'music_id' => null,
                    'name' => $firstDownloadedProduct->name,
                    'slug' => $firstDownloadedProduct->slug,
                    'description' => $firstDownloadedProduct->description,
                    'audio_placeholder' => null,
                    'free' => 1,
                    'is_editorial_use' => false,
                    'category' => $firstDownloadedProduct->category->name,
                    'type' => 'video',
                    'poster' => '/assets/images/site/thumb_placeholder.png',
                    'source' => [],
                    'specs' => [],
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'per_page' => 60,
                'to' => 2,
                'total' => 2,
            ]
        ];

        $this->assertEquals($expected, $request->json());
    }

    public function test_download_list_does_not_contain_deleted_products()
    {
        $user = $this->getConfirmedUser();
        $firstDownloadedTime = Carbon::now()->subMinute(30);
        $secondDownloadedTime = Carbon::now()->subMinute(20);
        $trashedDate = Carbon::now()->subMinute(15);
        $thirdDownloadedTime = Carbon::now()->subMinute(10);
        $firstDownloadedProduct = $this->getDownloadedProduct($user, $firstDownloadedTime);
        $secondDownloadProduct = $this->getDownloadedProduct($user, $secondDownloadedTime, $trashedDate);
        $thirdDownloadedProduct = $this->getDownloadedProduct($user, $thirdDownloadedTime);

        $requestObject = [
            'page' => 1,
            'perPage' => 60
        ];

        $request = $this->actingAs($user, 'api')->postJson(
            "adobe-panel/api/products/user-downloads",
            $requestObject
        );

        $request->assertStatus(200);

        $expected = [
            'products' => [
                [
                    'id' => $thirdDownloadedProduct->id,
                    'seller_id' => $thirdDownloadedProduct->seller_id,
                    'music_id' => null,
                    'name' => $thirdDownloadedProduct->name,
                    'slug' => $thirdDownloadedProduct->slug,
                    'description' => $thirdDownloadedProduct->description,
                    'audio_placeholder' => null,
                    'free' => 1,
                    'is_editorial_use' => false,
                    'category' => $thirdDownloadedProduct->category->name,
                    'type' => 'video',
                    'poster' => '/assets/images/site/thumb_placeholder.png',
                    'source' => [],
                    'specs' => [],
                ],
                [
                    'id' => $firstDownloadedProduct->id,
                    'seller_id' => $firstDownloadedProduct->seller_id,
                    'music_id' => null,
                    'name' => $firstDownloadedProduct->name,
                    'slug' => $firstDownloadedProduct->slug,
                    'description' => $firstDownloadedProduct->description,
                    'audio_placeholder' => null,
                    'free' => 1,
                    'is_editorial_use' => false,
                    'category' => $firstDownloadedProduct->category->name,
                    'type' => 'video',
                    'poster' => '/assets/images/site/thumb_placeholder.png',
                    'source' => [],
                    'specs' => [],
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'per_page' => 60,
                'to' => 2,
                'total' => 2,
            ]
        ];

        $this->assertEquals($expected, $request->json());
    }

    private function getConfirmedUser()
    {
        $user = factory(User::class)->create([
            'confirmed' => 1,
        ]);

        return $user;
    }

    private function getDownloadedProduct(User $user, Carbon $downloadedDate, Carbon $trashedDate = null)
    {
        $activePreview = factory(PreviewUpload::class)->create();
        // Attach a file to preview
        factory(PreviewFile::class)->create([
            'preview_upload_id' => $activePreview->id,
            'label' => PreviewFile::MP4_HIGH,
            'url' => 'http://test.s3.amazonaws.com/test.mp4'
        ]);
        $product = factory(Product::class)->create([
            'active_preview_id' => $activePreview->id,
            'free' => 1,
            'package_file_path' => 'something-not-null',
            'deleted_at' => $trashedDate
        ]);
        // create a download
        $download = factory(Download::class)->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'first_downloaded_at' => $downloadedDate
        ]);

        return $product;
    }

    private function mockProductRepository(string $downloadUrl)
    {
        App::bind(ProductRepository::class, function () use ($downloadUrl) {
            $product = app(Product::class);
            $autoDescriptionRepository = app(AutoDescriptionRepository::class);
            $categoriesWithRelationHelper = app(CategoriesWithRelationsHelper::class);

            $mock = Mockery::mock(ProductRepository::class,
                [
                    $product,
                    $autoDescriptionRepository,
                    $categoriesWithRelationHelper
                ]
            )->makePartial();
            $mock->shouldReceive('getStorageDownloadUrl')
                ->andReturn($downloadUrl);
            $mock->shouldReceive('getCdnDownloadUrl')
                ->andReturn($downloadUrl);

            return $mock;
        });
    }
}
