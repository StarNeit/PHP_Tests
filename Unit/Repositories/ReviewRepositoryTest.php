<?php

namespace Tests\Unit\Repositories;

use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewRepositoryTest extends TestCase
{
    use RefreshAndSeedDatabase;

    private $reviewRepo;

    public function setUp()
    {
        parent::setUp();

        // Get ReviewRepository
        $this->reviewRepo = \App::make('MotionArray\Repositories\ReviewRepository');
    }

    public function create_user_comment()
    {
        // Create user comment
        $userComment = factory(\MotionArray\Models\ProjectComment::class)->create();

        $user = $userComment->previewUpload->uploadable->user;

        // create user site
        $userSite = factory(\MotionArray\Models\UserSite::class)->create(['user_id' => $user->id]);

        // create review
        factory(\MotionArray\Models\Review::class)->create(['user_site_id' => $userSite->id]);

        return $userComment;
    }

    public function create_owner_comment()
    {
        // Create owner comment
        $admin = factory(\MotionArray\Models\User::class)->create();
        $admin->roles()->attach(4);

        $project = factory(\MotionArray\Models\Project::class)->create([
            'user_id' => $admin->id
        ]);

        $previewUpload = factory(\MotionArray\Models\PreviewUpload::class)->create([
            'uploadable_id' => $project->id
        ]);

        $commentAuthor = factory(\MotionArray\Models\ProjectCommentAuthor::class)->create([
            'name' => "$admin->firstname $admin->lastname",
            'email' => $admin->email
        ]);

        $ownerComment = factory(\MotionArray\Models\ProjectComment::class)->create([
            'preview_upload_id' => $previewUpload->id,
            'author_id' => $commentAuthor->id
        ]);

        // create user site
        $userSite = factory(\MotionArray\Models\UserSite::class)->create(['user_id' => $admin->id]);

        // create review
        factory(\MotionArray\Models\Review::class)->create(['user_site_id' => $userSite->id]);

        return $ownerComment;
    }

    public function test_do_not_send_create_notification_for_owner_comment()
    {
        $ownerComment = $this->create_owner_comment();

        $commentCreateNotificationResult = $this->reviewRepo
            ->sendUserCommentNotification($ownerComment->previewUpload, $ownerComment);

        $this->assertNotTrue(
            $commentCreateNotificationResult,
            'Should not send create notification for project owner.'
        );
    }

    public function test_do_not_send_update_notification_for_owner_comment()
    {
        $ownerComment = $this->create_owner_comment();

        $commentUpdateNotificationResult = $this->reviewRepo->sendCommentUpdateNotification($ownerComment);

        $this->assertNotTrue(
            $commentUpdateNotificationResult,
            'Should not send update notification for project owner.'
        );
    }

    public function test_do_not_send_delete_notification_for_owner_comment()
    {
        $ownerComment = $this->create_owner_comment();

        $commentDeleteNotificationResult = $this->reviewRepo->sendCommentDeleteNotification($ownerComment);

        $this->assertNotTrue(
            $commentDeleteNotificationResult,
            'Should not send delete notification for project owner.'
        );
    }

    public function test_send_create_notification_for_user_comment()
    {
        $userComment = $this->create_user_comment();

        Mail::fake();

        $commentCreateNotificationResult = $this->reviewRepo
            ->sendUserCommentNotification($userComment->previewUpload, $userComment);

        $this->assertTrue(
            $commentCreateNotificationResult,
            'Should send create notification if author is not owner.'
        );
    }

    public function test_send_update_notification_for_user_comment()
    {
        $userComment = $this->create_user_comment();

        Mail::fake();

        $commentUpdateNotificationResult = $this->reviewRepo->sendCommentUpdateNotification($userComment);

        $this->assertTrue(
            $commentUpdateNotificationResult,
            'Should send update notification if author is not owner.'
        );
    }

    public function test_send_delete_notification_for_user_comment()
    {
        $userComment = $this->create_user_comment();

        Mail::fake();

        $commentDeleteNotificationResult = $this->reviewRepo->sendCommentDeleteNotification($userComment);

        $this->assertTrue(
            $commentDeleteNotificationResult,
            'Should send Delete notification if author is not owner.'
        );
    }
}
