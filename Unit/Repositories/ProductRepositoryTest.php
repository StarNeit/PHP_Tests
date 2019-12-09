<?php

namespace Tests\Unit\Repositories;

use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\StaticData\ProductChangeOptions;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\StaticData\SubCategories;
use MotionArray\Models\Submission;
use MotionArray\Models\User;
use MotionArray\Repositories\Products\ProductRepository;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MotionArray\Models\ProductChangeOption;
use MotionArray\Models\Product;

class ProductRepositoryTest extends TestCase
{
    use RefreshAndSeedDatabase;

    /**
     * @var ProductRepository
     */
    private $productRepo;

    public function setUp()
    {
        parent::setUp();

        // Get ReviewRepository
        $this->productRepo = \App::make(ProductRepository::class);
    }

    public function create_a_product()
    {
        // create a seller
        $seller = factory(User::class)->create();
        $seller->roles()->attach(Roles::SELLER_ID);

        // create a product
        $product = factory(Product::class)->create(
            [
                'seller_id' => $seller->id
            ]
        );

        // create a submission
        factory(Submission::class)->create(
            [
                'seller_id' => $seller->id,
                'product_id' => $product->id,
                'submission_status_id' => 4
            ]
        );

        return $product;
    }

    public function product_is_changed_with_option($productId, $option)
    {
        $product = Product::find($productId);

        $optionIds = [];

        $productChanges = $product->productChanges()->get();

        foreach ($productChanges as $productChange) {
            array_push($optionIds, $productChange->id);
        }

        $changedResult = in_array($option, $optionIds);

        return $changedResult;
    }

    public function test_should_be_created_a_product()
    {
        $product = $this->create_a_product();

        $this->assertNotNull($product, 'A product should be created.');
    }

    public function test_set_change_options_function()
    {
        $product = $this->create_a_product();

        $functionResult = $this->productRepo->setChangeOptions($product, ProductChangeOptions::PRODUCT_NAME_CHANGED_ID);

        $this->assertTrue($functionResult, 'SetChangeOptions function should work.');
    }

    public function test_change_product_name()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['name' => 'Changed Name']);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::PRODUCT_NAME_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which product name is changed.');
    }

    public function test_change_description()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['description' => 'Description Name']);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::DESCRIPTION_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which product description is changed.');
    }

    public function test_change_audio_placeholder()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['audio_placeholder' => 'https://ma-previews-dev.s3.amazonaws.com/preview-23297Dw8B3FVtu.png']);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::AUDIO_PLACEHOLDER_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which product audio placeholder is changed.');
    }

    public function test_change_free_checkbox()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['free' => 1]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::FREE_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which free check box is changed.');
    }

    public function test_change_track_duration()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['track_durations' => '5:00']);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::TRACK_DURATIONS_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which track duration is changed.');
    }

    public function test_change_music_url()
    {
        $product = $this->create_a_product();
        $musicProduct = $this->create_a_product();

        $this->productRepo->update($product->id, ['music_url' => config('app.url') .'/'. $musicProduct->slug]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::MUSIC_URL_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which music url is changed.');
    }

    public function test_change_sub_categories()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id,
            [
                'category_id[]' => Categories::AFTER_EFFECTS_TEMPLATES_ID,
                'category_1_sub_category_id[]' => SubCategories::AFTER_EFFECTS_TEMPLATES_LOWER_THIRDS_ID
            ]
        );

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::SUB_CATEGORY_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which sub categories are changed.');
    }

    public function test_change_compressions()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['compression_id[]' => [4]]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::COMPRESSION_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which compressions are changed.');
    }

    public function test_change_formats()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['format_id[]' => [2]]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::FORMAT_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which formats are changed.');
    }

    public function test_change_resolutions()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['resolution_id[]' => [3]]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::RESOLUTION_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which resolutions are changed.');
    }

    public function test_change_versions()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['version_id[]' => [3]]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::VERSION_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which versions are changed.');
    }

    public function test_change_bpms()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['bpm_id[]' => [3]]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::BPM_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which bpms are changed.');
    }

    public function test_change_fpss()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['fps_id[]' => [3]]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::FPS_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which FPS is changed.');
    }

    public function test_change_sample_rates()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['sample_rate_id[]' => [2]]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::SAMPLE_RATE_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which sample rates are changed.');
    }

    public function test_change_plugins()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['plugin_id[]' => [3]]);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::PLUGIN_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which plugins are changed.');
    }

    public function test_change_tags()
    {
        $product = $this->create_a_product();

        $this->productRepo->update($product->id, ['tags' => 'test, haa, my test tag']);

        $changedResult = $this->product_is_changed_with_option($product->id, ProductChangeOptions::TAG_CHANGED_ID);

        $this->assertTrue($changedResult, 'Should update status which tags are changed.');
    }
}
