<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use Illuminate\Support\Carbon;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use MotionArray\Repositories\AdminUserRepository;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;
use Faker\Generator as Faker;

class UserUpdatedTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function testWithUserLoggedIn()
    {
        $user = factory(User::class)->create();

        $faker = app(Faker::class);
        $attributes = [
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastname,
            'plan_id' => \MotionArray\Models\StaticData\Plans::FREE_ID,
            'email' => $faker->unique()->safeEmail,
            'password' => 'test1234',
            'company_name' => $faker->company,
            'paypal_email' => $faker->email,
            'payoneer_id' => $faker->unique()->randomNumber(8)
        ];

        $admin = factory(User::class)->create();

        /** @var AdminUserRepository $repo */
        $repo = app(AdminUserRepository::class);
        $this->actingAs($admin);
        $user = $repo->update($user->id, $attributes);

        $user = User::find($user->id);
        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where('user_event_log_type_id', UserEventLogTypes::USER_UPDATED_ID)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $expected = [
            'triggered_by_user' => [
                'firstname' => $admin->firstname,
                'lastname' => $admin->lastname,
                'email' => $admin->email,
                'id' => $admin->id
            ],
            'new_attributes' => array_except($attributes, ['password', 'password_confirmation'])
        ];

        $this->assertInstanceOf(UserEventLog::class, $userEventLog, 'user event log not created in db');
        $this->assertEquals($expected, $userEventLog->data);
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $triggeredByDescription = "'{$admin->full_name}' (email: '{$admin->email}', id: '{$admin->id}')";
        $attributesDescription = "(email: '{$attributes['email']}', plan_id: '{$user->plan_id}', lastname: '{$attributes['lastname']}', firstname: '{$attributes['firstname']}', payoneer_id: '{$attributes['payoneer_id']}', company_name: '{$attributes['company_name']}', paypal_email: '{$attributes['paypal_email']}')";
        $expected = "User: {$userDescription} was updated, by User: {$triggeredByDescription}, New Attributes: {$attributesDescription}";
        $this->assertEquals($expected, $userEventLog->userEventMessage());
    }
}
