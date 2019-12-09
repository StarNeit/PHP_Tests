<?php

namespace Tests\Browser\Views\site\browse\index;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddedFilterTest extends DuskTestCase
{
    public function test_browse_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->assertSee('Browse Our Collection');
        });
    }

    public function test_added_date_should_be_set_by_last_year()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse?sort_by=local_products_by_downloads&date_added=last-year')
                ->waitFor('#added .top-filter__select select', 15)
                ->assertSee('LAST YEAR');
        });
    }
}
