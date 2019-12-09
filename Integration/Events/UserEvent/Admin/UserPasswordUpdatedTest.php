<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use Illuminate\Support\Carbon;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use MotionArray\Repositories\AdminUserRepository;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserPasswordUpdatedTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function testWithUserLoggedIn()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class)->create();

        /** @var AdminUserRepository $repo */
        $repo = app(AdminUserRepository::class);
        $this->actingAs($admin);
        $password = 'test123';
        $user = $repo->setPassword($user->id, $password);

        $user = User::find($user->id);
        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where('user_event_log_type_id', UserEventLogTypes::USER_PASSWORD_UPDATED_ID)
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
        ];

        $this->assertInstanceOf(UserEventLog::class, $userEventLog, 'user event log not created in db');
        $this->assertEquals($expected, $userEventLog->data);
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $triggeredByDescription = "'{$admin->full_name}' (email: '{$admin->email}', id: '{$admin->id}')";
        $expected = "User: {$userDescription} was password updated, by User: {$triggeredByDescription}";
        $this->assertEquals($expected, $userEventLog->userEventMessage());
    }
}
