<?php

namespace Tests\Unit\Models;

use MotionArray\Models\User;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function test_has_role_method_without_loading_relation()
    {
        $user = factory(User::class)->create();
        $this->assertEquals(0, $user->roles()->count());

        \DB::enableQueryLog();
        $this->assertFalse($user->hasRole(1));
        $this->assertFalse($user->hasRole(2));
        $this->assertFalse($user->hasRole(3));
        \DB::disableQueryLog();

        $this->assertEquals(3, \count(\DB::getQueryLog()));
    }

    public function test_has_role_method_with_loading_relation()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->assertEquals(0, $user->roles()->count());
        $user->roles()->attach(3);

        $user->load('roles');
        $this->assertEquals(1, $user->roles->count());

        \DB::enableQueryLog();
        $this->assertFalse($user->hasRole(1));
        $this->assertFalse($user->hasRole(2));
        $this->assertTrue($user->hasRole(3));
        \DB::disableQueryLog();

        $this->assertEquals(0, \count(\DB::getQueryLog()));
    }
}
