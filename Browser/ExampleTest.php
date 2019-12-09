<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExampleTest extends DuskTestCase
{
    /**
     * If the result from Chrome Driver is this certain HTML,
     * it means that it couldn't fetch a page
     *
     * @return void
     */
    function testWebserverRunning()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $this->assertNotEquals($browser->driver->getPageSource(),
                '<html xmlns="http://www.w3.org/1999/xhtml"><head></head><body></body></html>',
                'ERROR: Web server not running on '.config('app.url'));

            // Different versions of Chrome
            $this->assertNotEquals($browser->driver->getPageSource(),
                '<html><head></head><body></body></html>',
                'ERROR: Web server not running on '.config('app.url'));
        });
    }
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testIsMotionArray()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
//             ->dump() // use to see HTML for debugging purposes.
            ->assertSee('Motion Array');

        });
    }
}
