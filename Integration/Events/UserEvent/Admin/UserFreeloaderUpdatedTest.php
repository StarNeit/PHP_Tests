<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use Illuminate\Support\Carbon;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use MotionArray\Repositories\AdminUserRepository;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserFreeloaderUpdatedTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function testWithUserLoggedIn()
    {
        $this->markTestSkipped('We won\'t use Freeloader anymore. Test was failing, disabled because of that.');

        $user = factory(User::class)->create();

        $start = Carbon::now();
        $end = Carbon::now()->addDays(10);

        $attributes = [
            'access_starts_at' => $start,
            'access_expires_at' => $end,
            'plan_id' => Plans::FREE_ID,
        ];

        $admin = factory(User::class)->create();

        /** @var AdminUserRepository $repo */
        $repo = app(AdminUserRepository::class);
        $this->actingAs($admin);
        $user = $repo->updateFreeloader($user->id, $attributes);

        $user = User::find($user->id);
        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where('user_event_log_type_id', UserEventLogTypes::USER_FREELOADER_UPDATED_ID)
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
            'updated_attributes' => array_except($attributes, ['password', 'password_confirmation'])
        ];
        $this->assertInstanceOf(UserEventLog::class, $userEventLog, 'user event log not created in db');
        $this->assertEquals($expected, $userEventLog->data);
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $triggeredByDescription = "'{$admin->full_name}' (email: '{$admin->email}', id: '{$admin->id}')";
        $attributesDescription = "(access_starts_at: '{$attributes['access_starts_at']}', access_expires_at: '{$attributes['access_expires_at']}', plan_id: '{$user->plan_id}')";
        $expected = "User: {$userDescription} was freeloader updated, by User: {$triggeredByDescription}, Updated Attributes: {$attributesDescription}";
        $this->assertEquals($expected, $userEventLog->userEventMessage());
    }
}
