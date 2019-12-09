<?php
namespace Tests\Browser\Views\site\browse\index;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
class DefaultFilterTest extends DuskTestCase
{
    public function test_sort_by_should_be_most_popular()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->waitFor('#sort-by .top-filter__select', 15)
                ->assertSee('MOST POPULAR ITEMS');
        });
    }
    public function test_time_filter_should_be_last_month()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->waitFor('#added .top-filter__select select', 15)
                ->assertSee('LAST MONTH');
        });
    }
    public function test_should_be_set_by_newest_and_any_time_on_first_filter()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->waitFor('#added .top-filter__select select', 15)
                ->assertSee('LAST MONTH');
            $browser->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->assertSee('NEWEST ITEMS');
            $browser->assertSee('ANY TIME');
        });
    }
}
