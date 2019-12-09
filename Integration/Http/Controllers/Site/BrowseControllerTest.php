<?php

namespace Tests\Integration\Http\Controllers\Site;

use MotionArray\Models\Product;
use MotionArray\Models\StaticData\Categories;
use MotionArray\Models\User;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class BrowseControllerTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function testBrowseRedirects()
    {
        $this->get('/browse/adobe-premiere-rush-templates')
            ->assertRedirect('/browse/premiere-rush-templates');

        $this->get('/browse/stock-music')
            ->assertRedirect('/browse/royalty-free-music');

        $this->get('/browse/royalty-free-music/easy-listening')
            ->assertStatus(200);
    }

    public function testProductRedirects()
    {
        $seller = factory(User::class)->create();

        $product = factory(Product::class)->create([
            'seller_id' => $seller->id,
            'category_id' => Categories::STOCK_MUSIC_ID,
            'slug' => uniqid(),
            'name' => uniqid(),
        ]);

        $this->get('/stock-music/' . $product->slug)
            ->assertRedirect('/royalty-free-music/' . $product->slug);

        $this->get('/royalty-free-music/' . $product->slug)
            ->assertSeeText($product->name)
            ->assertStatus(200);

        $this->followingRedirects();

        $this->get('/stock-music/' . $product->slug)
            ->assertSeeText($product->name)
            ->assertStatus(200);
    }
}
