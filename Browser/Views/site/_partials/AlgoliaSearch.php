<?php

namespace Tests\Browser\Views\site\_partials;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AlgoliaSearch extends DuskTestCase
{
    public function test_should_be_displayed_only_error_message()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse?q=vvvvvvvvvvvvvvvvvvvv')
                ->waitForText('Sorry, we didn’t find any results. You can try submitting a Request')
                ->assertDontSee('You have not viewed any products recently');

            $browser->assertDontSee('You have not viewed any products recently');
        });
    }
}
