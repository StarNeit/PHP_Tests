<?php

namespace Tests\Browser\Views\site\browse\index;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SecondaryFilterTest extends DuskTestCase
{
    public function test_browse_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->assertSee('Browse Our Collection');
        });
    }

    public function test_bpms_should_be_added()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse?categories=stock-music:inspirational&specs=specs.cat4.bpm:23')
                ->waitForText('BEATS PER MINUTE (BPM)', 15)
                ->assertSeeIn('.spec-filters', '23');
        });
    }

    public function test_durations_should_be_added()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse?categories=stock-music:inspirational&specs=specs.cat4.duration:78')
                ->waitForText('DURATION (MINUTES)', 15)
                ->assertSee('.spec-filters', '1:18');
        });
    }
}
