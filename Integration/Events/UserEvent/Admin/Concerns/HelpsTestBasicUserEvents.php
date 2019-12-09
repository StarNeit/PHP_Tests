<?php

namespace Tests\Integration\Events\UserEvent\Admin\Concerns;

use Closure;
use Illuminate\Support\Carbon;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;

trait HelpsTestBasicUserEvents
{
    public function assertBasicUserEvent($userEventLogId, $action, Closure $performTransaction)
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class)->create();

        $this->actingAs($admin);
        $performTransaction($user);

        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where('user_event_log_type_id', $userEventLogId)
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

        $this->assertInstanceOf(UserEventLog::class, $userEventLog, 'user event log not created in db');
        $this->assertEquals($expected, $userEventLog->data);
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $triggeredByDescription = "'{$admin->full_name}' (email: '{$admin->email}', id: '{$admin->id}')";
        $expected = "User: {$userDescription} was {$action}, by User: {$triggeredByDescription}";
        $this->assertEquals($expected, $userEventLog->userEventMessage());
    }
}
