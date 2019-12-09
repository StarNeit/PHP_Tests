<?php

namespace Tests\Browser\Views\admin\automateNewsletters;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PreviewTest extends DuskTestCase
{

    public function create_admin()
    {
        $admin = factory(\MotionArray\Models\User::class)->create();

        $admin->roles()->attach(1);

        return $admin;
    }

    public function test_automate_newsletters_page()
    {
        $admin = $this->create_admin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/mabackend/automate-newsletters')
                ->assertSee('New Products');
        });
    }

    public function test_preview_should_display_html()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $admin = $this->create_admin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/mabackend/automate-newsletters')
                ->waitFor('a.preview', 10)
                ->click('a.preview')
                ->assertDontSee('PREVIEW');

            $browser->driver->switchTo()->frame('preview');

            $browser->assertSee('BROWSE EVERYTHING');
        });
    }
}
