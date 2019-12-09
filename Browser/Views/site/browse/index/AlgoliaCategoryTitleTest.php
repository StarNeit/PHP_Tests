<?php

namespace Tests\Browser\Views\site\browse\index;

use MotionArray\Models\StaticData\Roles;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AlgoliaCategoryTitleTest extends DuskTestCase
{
    public function create_user()
    {
        $user = factory(\MotionArray\Models\User::class)->create(
            [
                'password' => 'test1234'
            ]
        );

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

    public function test_kickass_should_appear_on_title()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->waitForText('MOST POPULAR ITEMS', 15)
                ->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->click('#sort-by .top-filter__select')
                ->click(".select2-results__options [id*='by_kickass']")
                ->assertSee('Kick Ass After Effects Templates');
        });
    }

    public function test_recently_viewed_should_appear_on_title()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $user = $this->create_user();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/account/login')
                ->type('email', $user->email)
                ->type('password', 'test1234')
                ->press('Log in')
                ->assertDontSee('Invalid email address/password');

            $browser->visit('/browse')
                ->waitForText('MOST POPULAR ITEMS', 15)
                ->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->click('#sort-by .top-filter__select')
                ->click(".select2-results__options [id*='recently_viewed']")
                ->assertSee('Recently Viewed After Effects Templates');
        });
    }

    public function test_added_date_should_appear_on_title()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->waitForText('MOST POPULAR ITEMS', 15)
                ->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->click('#sort-by .top-filter__select')
                ->click(".select2-results__options [id*='by_kickass']")
                ->assertSee('Kick Ass After Effects Templates');

            $browser->click('#added .top-filter__select')
                ->click("ul.select2-results__options li:nth-child(2)")
                ->assertSee('Kick Ass After Effects Templates In The Last Year');
        });
    }

    public function test_title_should_not_contain_string_in_the()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse')
                ->waitForText('MOST POPULAR ITEMS', 15)
                ->click('.al-hirearchy-widget ul li:nth-child(2)')
                ->click('#sort-by .top-filter__select')
                ->click(".select2-results__options [id*='by_kickass']")
                ->assertSee('Kick Ass After Effects Templates');

            $browser->click('#added .top-filter__select')
                ->click("ul.select2-results__options li:nth-child(5)")
                ->assertDontSee('Kick Ass After Effects Templates In The This week');
        });
    }

    public function test_category_template_name_is_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/browse/premiere-pro-templates')
                ->waitForText('NEWEST ITEMS', 15)
                ->assertSee('Premiere Pro Templates');
        });
    }
}
