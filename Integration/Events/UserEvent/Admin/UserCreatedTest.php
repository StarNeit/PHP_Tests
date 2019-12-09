<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use Faker\Generator as Faker;
use Illuminate\Support\Carbon;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use MotionArray\Repositories\AdminUserRepository;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserCreatedTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function testWithUserLoggedIn()
    {
        $faker = app(Faker::class);
        $attributes = [
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastname,
            'email' => $faker->unique()->safeEmail,
            'plan_id' => Plans::FREE_ID,
            'payout_method' => 'paypal',
            'paypal_email' => $faker->email,
            'payoneer_id' => $faker->unique()->randomNumber(8),
            'password' => 'test1234',
        ];

        $admin = factory(User::class)->create();

        /** @var AdminUserRepository $repo */
        $repo = app(AdminUserRepository::class);
        $this->actingAs($admin);
        $user = $repo->make($attributes, Roles::CUSTOMER_ID);
        $attributes['role_id'] = Roles::CUSTOMER_ID;

        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where('user_event_log_type_id', UserEventLogTypes::USER_CREATED_ID)
            ->where('user_id', $user->id)
            ->latest()
            ->first();


        $attributes = array_except($attributes, ['password']);
        $expected = [
            'triggered_by_user' => [
                'firstname' => $admin->firstname,
                'lastname' => $admin->lastname,
                'email' => $admin->email,
                'id' => $admin->id
            ],
            'attributes' => $attributes
        ];

        $this->assertInstanceOf(UserEventLog::class, $userEventLog, 'user event log not created in db');
        $this->assertEquals($expected, $userEventLog->data);
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $roleId = Roles::CUSTOMER_ID;
        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $triggeredByDescription = "'{$admin->full_name}' (email: '{$admin->email}', id: '{$admin->id}')";
        $attributesDescription = "(email: '{$user->email}', plan_id: '{$user->plan_id}', role_id: '{$roleId}', lastname: '{$user->lastname}', firstname: '{$user->firstname}', payoneer_id: '{$user->payoneer_id}', paypal_email: '{$user->paypal_email}', payout_method: '{$user->payout_method}')";
        $expected = "User: {$userDescription} was created, by User: {$triggeredByDescription}, Attributes: {$attributesDescription}";
        $this->assertEquals($expected, $userEventLog->userEventMessage());
    }
}
