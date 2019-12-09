<?php

namespace Tests\Browser\Views\site\browse\index;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AlgoliaStatsTitleTest extends DuskTestCase
{
    public function product_repository()
    {
        return \App::make('MotionArray\Repositories\Products\ProductRepository');
    }

    public function test_browse_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->assertSee('Browse Our Collection');
        });
    }

    public function test_should_display_total_product_number()
    {
        $productRepo = $this->product_repository();

        $totalProductNumber = $productRepo->totalProductCount();

        $statsContent = "$totalProductNumber Unlimited Downloads Available";

        $this->browse(function (Browser $browser) use ($statsContent) {
            $browser->visit('/browse')
                ->waitFor('.ais-stats--body', 15)
                ->assertSeeIn('.ais-stats--body', $statsContent);

            $browser->visit('/browse?categories=after-effects-templates,stock-video')
                ->waitFor('.ais-stats--body', 15)
                ->assertSeeIn('.ais-stats--body', $statsContent);
        });
    }
}
