<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use MotionArray\Jobs\SendProductToAlgolia;
use MotionArray\Models\Download;
use MotionArray\Models\Product;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\Resolutions;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\StaticData\SubCategories;
use MotionArray\Models\User;
use MotionArray\Repositories\Products\ProductRepository;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshAndSeedDatabase;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    public function setUp()
    {
        parent::setUp();

        // Get ReviewRepository
        $this->productRepository = \App::make(ProductRepository::class);
    }

    /**
     * Test the command that fills the products downloads counts table
     *
     * 1. Create Sample products
     * 2. Add a download to $product2, run command to generate the products count and test the order
     */
    public function test_get_seller_products_ordered_by_downloads_count()
    {
        $now = Carbon::now();

        // $this->product->getSellerProducts() ignores users.id < 4
        // it is a mess that needs to be cleaned up
        $test = factory(User::class)->create();
        $eri = factory(User::class)->create();
        $tyler = factory(User::class)->create();

        $seller = factory(User::class)->create();

        $seller->roles()->attach(3);

        // {1}
        $product1 = $this->create_a_product($seller);

        $product2 = $this->create_a_product($seller);

        $product3 = $this->create_a_product($seller);

        // {2}
        factory(Download::class)->create(['first_downloaded_at' => $now, 'product_id' => $product2->id]);

        Artisan::call('motionarray:update-downloads-count', ["--days" => 1]);

        $orderedProducts = $this->productRepository->getSellerProducts($seller->id, 1, 10, 'downloads');

        $first = $orderedProducts->first();
        $this->assertNotNull($first, 'has ordered product');
        $this->assertEquals($first->id, $product2->id, 'Should match the product with most downloads');
    }

    public function test_should_have_errors_when_make_fails()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->make([
            'seller_id' => $seller->id,
            'free' => 0,

            // This will cause `make()` to fail because `name` should be 40 characters max.
            'name' => str_repeat('.', 41)
        ]);

        $product['category_id[]'] = $product->category_id;

        $result = $this->productRepository->make($product->toArray());

        $this->assertFalse($result);
        $this->assertNotEmpty($this->productRepository->errors);
    }

    public function test_should_return_product_when_make_succeed()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $categoryId = Categories::AFTER_EFFECTS_PRESETS_ID;

        $product = factory(Product::class)->make([
            'seller_id' => $seller->id,
            'category_id[]' => $categoryId,
            "category_{$categoryId}_sub_category_id[]" => [
                SubCategories::AFTER_EFFECTS_PRESETS_OVERLAY_ID
            ],
            'free' => 0
        ]);

        $result = $this->productRepository->make($product->toArray());

        $this->assertInstanceOf(Product::class, $result);
    }

    public function test_should_set_slug_when_make_succeed()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $categoryId = Categories::AFTER_EFFECTS_PRESETS_ID;

        $product = factory(Product::class)->make([
            'seller_id' => $seller->id,
            'category_id[]' => $categoryId,
            "category_{$categoryId}_sub_category_id[]" => [
                SubCategories::AFTER_EFFECTS_PRESETS_OVERLAY_ID
            ],
            'slug' => null,
            'free' => 0
        ]);

        $result = $this->productRepository->make($product->toArray());

        $this->assertNotNull($result->slug);
    }

    public function test_should_set_audio_placeholder_for_stock_music_category_when_make_succeeds()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $categoryId = Categories::STOCK_MUSIC_ID;

        $product = factory(Product::class)->make([
            'seller_id' => $seller->id,
            'category_id[]' => $categoryId,
            "category_{$categoryId}_sub_category_id[]" => [
                SubCategories::STOCK_MUSIC_AMBIENT_ID
            ],
            'audio_placeholder' => null,
            'free' => 0
        ]);

        $result = $this->productRepository->make($product->toArray());

        $this->assertNotNull($result->audio_placeholder);
    }

    public function test_should_set_audio_placeholder_for_sound_effects_category_when_make_succeeds()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $categoryId = Categories::SOUND_EFFECTS_ID;

        $product = factory(Product::class)->make([
            'seller_id' => $seller->id,
            'category_id[]' => $categoryId,
            "category_{$categoryId}_sub_category_id[]" => [
                SubCategories::SOUND_EFFECTS_CARTOON_ID
            ],
            'audio_placeholder' => null,
            'free' => 0
        ]);

        $result = $this->productRepository->make($product->toArray());

        $this->assertNotNull($result->audio_placeholder);
    }

    public function test_should_set_description_for_stock_video_category_when_make_succeeds()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $categoryId = Categories::STOCK_VIDEO_ID;

        $product = factory(Product::class)->make([
            'seller_id' => $seller->id,
            'category_id[]' => $categoryId,
            "category_{$categoryId}_sub_category_id[]" => [
                SubCategories::STOCK_VIDEO_BACKGROUND_ID
            ],
            'resolution_id[]' => Resolutions::RES_1080X1080_SQUARE_ID,
            'description' => null,
            'free' => 0
        ]);

        $result = $this->productRepository->make($product->toArray());

        $this->assertNotNull($result->description);
    }

    public function test_should_set_weight_when_admin_update_succeeds()
    {
        $admin = factory(User::class)->create();
        $admin->roles()->attach(Roles::ADMIN_ID);

        $seller = factory(User::class)->create();
        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'weight' => 2
        ]);

        $productId = $product->id;

        $product = $this->productRepository->update($productId, [
            'name' => 'updated name',
            'weight' => 1
        ]);

        $this->assertEquals(2, $product->weight);

        $this->actingAs($admin);

        $product = $this->productRepository->update($productId, [
            'name' => 'updated name',
            'weight' => 1
        ]);

        $this->assertEquals(1, $product->weight);
    }

    public function test_should_dispatch_to_algolia_on_update()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'weight' => 2
        ]);

        \Bus::fake();

        $this->productRepository->update($product->id, [
            'name' => 'updated name',
            'weight' => 1
        ]);

        \Bus::assertDispatched(SendProductToAlgolia::class, 1);
    }

    public function test_should_have_errors_when_update_fails()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'weight' => 2
        ]);

        $result = $this->productRepository->update($product->id, [
            // This will cause `update()` to fail because `name` should be 40 characters max.
            'name' => str_repeat('.', 41)
        ]);

        $this->assertFalse($result);
        $this->assertNotEmpty($this->productRepository->errors);
    }

    public function test_should_create_exclusion_when_updating()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'weight' => 2
        ]);

        $this->assertFalse($product->productSearchExclusion()->exists());

        $product = $this->productRepository->update($product->id, [
           'excluded' => true
        ]);

        $this->assertTrue($product->productSearchExclusion()->exists());
    }

    public function test_should_remove_exclusion_when_updating()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'weight' => 2
        ]);

        $product->productSearchExclusion()->create();

        $product = $this->productRepository->update($product->id, []);

        $this->assertFalse($product->productSearchExclusion()->exists());
    }

    public function test_should_not_set_publish_at_if_product_has_one_when_updating()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'published_at' => Carbon::now()
        ]);

        $updatedProduct = $this->productRepository->update($product->id, [
            'published_at' => Carbon::create('-1 day')
        ]);

        $this->assertTrue($updatedProduct->published_at->eq($product->published_at));
    }

    public function test_should_set_publish_at_if_product_has_null_publish_at_when_updating()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'published_at' => null
        ]);

        $updatedProduct = $this->productRepository->update($product->id, [
            'published_at' => Carbon::now()
        ]);

        $this->assertNotNull($updatedProduct->published_at);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'published_at' => null
        ]);

        $updatedProduct = $this->productRepository->update($product->id, [
            'published_at' => Carbon::now()->format('m/d/Y')
        ]);

        $this->assertNotNull($updatedProduct->published_at);
    }

    public function test_can_change_placeholder_url_when_updating()
    {
        $seller = factory(User::class)->create();

        $seller->roles()->attach(Roles::SELLER_ID);

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id
        ]);

        $oldPreview = $product->activePreview()->create();

        $updatedProduct = $this->productRepository->update($product->id, [
            'placeholder_url' => 'https://placehold.it/300'
        ]);

        $this->assertNotEquals($oldPreview->placeholder_id, $updatedProduct->activePreview->placeholder_id);
    }

    private function create_a_product(User $seller)
    {
        // create a product
        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'free' => 0,
        ]);

        return $product;
    }
}
