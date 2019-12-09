<?php

namespace Tests\Browser\Views\site\browse\index;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SearchInputTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_browse_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                    ->assertSee('Browse Our Collection');
        });
    }

    public function test_magnifying_icon_should_work_as_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->type('search-products', 'test key xxx')
                ->click('#search-input .icon--search')
                ->waitForText('TEST KEY XXX')
                ->assertValue('#search-input input', '');
        });
    }
}
