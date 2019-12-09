<?php

namespace Tests\Browser\Views\site\browse\index;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PaginationTest extends DuskTestCase
{
    public function test_browse_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->assertSee('Browse Our Collection');
        });
    }

    public function test_url_should_not_contain_page_number()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse?sort_by=local_products&page=7')
                ->waitForText('NEWEST ITEMS', 15)
                ->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->waitForText('Newest After Effects Templates')
                ->assertVisible('.pagination ul li:nth-child(3).active');
        });
    }
}
