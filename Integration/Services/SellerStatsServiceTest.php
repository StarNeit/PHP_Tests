<?php

namespace Tests\Integration\Services;

use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;
use MotionArray\Models\Country;
use Carbon\Carbon;

class SellerStatsServiceTest extends TestCase
{
    use RefreshAndSeedDatabase;

    function create_seller_with_product_downloads()
    {
        // create a seller
        $seller = factory(\MotionArray\Models\User::class)->create();
        $seller->roles()->attach(3);

        // create a product
        $product = factory(\MotionArray\Models\Product::class)->create(
            [
                'seller_id' => $seller->id
            ]
        );

        // create a US downloader
        $downloader1 = factory(\MotionArray\Models\User::class)->create([
            "country_id" => Country::byCode("US")->id
        ]);
        $download1 = factory(\MotionArray\Models\Download::class)->create([
            "product_id" => $product->id,
            "user_id" => $downloader1->id,
            "first_downloaded_at" => Carbon::now(),
        ]);
        $download1->weight = 2; // Can't do during creation, will override
        $download1->save();

        // create a non-US downloader
        $downloader2 = factory(\MotionArray\Models\User::class)->create([
            "country_id" => Country::byCode("AR")->id
        ]);
        $download2 = factory(\MotionArray\Models\Download::class)->create([
            "product_id" => $product->id,
            "user_id" => $downloader2->id,
            "first_downloaded_at" => Carbon::now(),
        ]);
        $download2->weight = 1;
        $download2->save();

        return $seller;
    }
    function create_site_payout()
    {
        return factory(\MotionArray\Models\PayoutTotal::class)->create([
            'month' => Carbon::now()->month,
            'year' => Carbon::now()->year,
            'updated_at' => Carbon::now(),
            'amount' => 10000,
            'weight' => 1000
        ]);
    }
    function test_unlimited_seller_earning()
    {
        $seller = $this->create_seller_with_product_downloads();
        $sitePayout = $this->create_site_payout();

        $sellerStats = \App::make("\MotionArray\Services\SellerStats\UnlimitedSellerStatsService");

        $earning = $sellerStats->getSellerEarnings($seller, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), $sitePayout);
        $this->assertEquals($earning, 30, "Incorrect seller income calculation, (1+2)*10000 / 1000 should be $30");

        $earningInUS = $sellerStats->getSellerEarnings($seller, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), $sitePayout, Country::byCode('US'));
        $this->assertEquals($earningInUS, 20, "Incorrect seller income calculation for specific country (US), 2*10000 / 1000 should be $20");

    }
}
