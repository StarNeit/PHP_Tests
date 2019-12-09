<?php

namespace Tests\Browser\Views\site\browse\index;

use MotionArray\Models\StaticData\Roles;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SortByTest extends DuskTestCase
{
    public function create_user()
    {
        $user = factory(\MotionArray\Models\User::class)->create();
        $user->roles()->attach(Roles::CUSTOMER_ID);

        return $user;
    }

    public function test_browse_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->assertSee('Browse Our Collection');
        });
    }

    public function test_filters_should_be_kept_when_clicking_requested_items()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->waitForText('MOST POPULAR ITEMS', 15)
                ->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->click('#sort-by .top-filter__select')
                ->click(".select2-results__options [id*='by_requests']")
                ->assertVisible('.al-hirearchy-widget ul li:nth-child(2) .checkbox.checked');
        });
    }

    public function test_filters_should_be_kept_when_clicking_recently_viewed()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $user = $this->create_user();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/account/login')
                ->type('email', $user->email)
                ->type('password', 'test1234')
                ->press('Log in')
                ->visit('/browse')
                ->waitForText('MOST POPULAR ITEMS', 15)
                ->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->click('#sort-by .top-filter__select')
                ->click(".select2-results__options [id*='recently_viewed']")
                ->assertVisible('.al-hirearchy-widget ul li:nth-child(2) .checkbox.checked');
        });
    }

    public function test_filters_should_be_kept_when_clicking_people_i_follow()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $user = $this->create_user();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/account/login')
                ->type('email', $user->email)
                ->type('password', 'test1234')
                ->press('Log in')
                ->visit('/browse')
                ->waitForText('MOST POPULAR ITEMS', 15)
                ->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->click('#sort-by .top-filter__select')
                ->click(".select2-results__options [id*='people_i_follow']")
                ->assertVisible('.al-hirearchy-widget ul li:nth-child(2) .checkbox.checked');
        });
    }

    public function test_filters_should_work_when_clicking_back_button()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse?sort_by=by_requests&categories=after-effects-templates')
                ->waitForText('REQUESTED ITEMS', 10)
                ->assertVisible('.al-hirearchy-widget ul li:nth-child(2) .checkbox.checked');
        });
    }
}
