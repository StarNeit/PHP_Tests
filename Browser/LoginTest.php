<?php

namespace Tests\Browser;

use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends DuskTestCase
{
    /**
     * Login as a user, then logout and try to login as another user
     * We had issues with this that the second user has to try logging in twice to work.
     *
     */
    function testLoginAfterLogout()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $this->browse(function ($browser) {
            $user1 = factory(User::class)->create( ['password'=>'123456'] );
            $user2 = factory(User::class)->create( ['password'=>'123456'] );

            $user1->roles()->attach(Roles::CUSTOMER_ID);
            $user2->roles()->attach(Roles::CUSTOMER_ID);

            $browser->visit('/account/login')
                ->type('email', $user1->email)
                ->type('password', '123456')
                ->press('Log in')
                ->assertSee($user1->firstname)
                ->assertPathIs('/account/upgrade');

            $browser->visit('/account/logout')
                ->assertPathIs('/account/login');

            $browser->visit('/account/login')
                ->type('email', $user2->email)
                ->type('password', '123456')
                ->press('Log in')
                ->assertPathIs('/account/upgrade')
                ->assertSee($user2->firstname);
        });
    }
}
