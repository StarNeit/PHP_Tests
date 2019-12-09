<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use Illuminate\Support\Carbon;
use MotionArray\Events\UserEvent\Admin\UserDisabled;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use MotionArray\Repositories\AdminUserRepository;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserDisabledTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function testWithUserLoggedIn()
    {
        $user = factory(User::class)->create([
            'disabled' => 0
        ]);

        $admin = factory(User::class)->create();

        /** @var AdminUserRepository $repo */
        $repo = app(AdminUserRepository::class);
        $this->actingAs($admin);
        $repo->setDisabled($user->id);

        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where('user_event_log_type_id', UserEventLogTypes::USER_DISABLED_ID)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $expected = [
            'triggered_by_user' => [
                'firstname' => $admin->firstname,
                'lastname' => $admin->lastname,
                'email' => $admin->email,
                'id' => $admin->id
            ]
        ];

        $this->assertEquals($expected, $userEventLog->data);
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $triggeredByDescription = "'{$admin->full_name}' (email: '{$admin->email}', id: '{$admin->id}')";
        $expected = "User: {$userDescription} was disabled, by User: {$triggeredByDescription}";
        $this->assertEquals($expected, $userEventLog->userEventMessage());
        $this->assertEquals(1, $user->fresh()->disabled);

    }

    public function testWithSelf()
    {
        $user = factory(User::class)->create([
            'disabled' => 0
        ]);

        /** @var AdminUserRepository $repo */
        $repo = app(AdminUserRepository::class);
        $this->actingAs($user);
        $repo->setDisabled($user->id);

        $expected = [
            'user_event_log_type_id' => UserEventLogTypes::USER_DISABLED_ID,
            'user_id' => $user->id,
            'data' => [
                'triggered_by_user' => array_only($user->toArray(), [
                    'id',
                    'email',
                    'firstname',
                    'lastname',
                ]),
            ]
        ];

        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where([
                'user_event_log_type_id' => UserEventLogTypes::USER_DISABLED_ID,
                'user_id' => $user->id,
            ])
            ->first();

        $actual = array_only($userEventLog->toArray(), [
            'user_id',
            'user_event_log_type_id',
            'data'
        ]);

        $this->assertEquals($expected, $actual);
        $this->assertEquals($user->id, $userEventLog->user_id);
        $this->assertEquals(UserDisabled::class, $userEventLog->userEventClass());
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $expected = "User: {$userDescription} was disabled, by User: Self";
        $this->assertEquals($expected, $userEventLog->userEventMessage());

        $this->assertEquals(1, $user->fresh()->disabled);
    }
}
