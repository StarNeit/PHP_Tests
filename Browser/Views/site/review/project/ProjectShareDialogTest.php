<?php

namespace Tests\Browser\Views\site\review\project;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use MotionArray\Models\User;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\Project;
use MotionArray\Models\PreviewUpload;
use MotionArray\Models\UserSite;
use MotionArray\Models\Review;
use MotionArray\Models\ProjectInvitation;

class ProjectShareDialogTest extends DuskTestCase
{
    public function create_user()
    {
        $user = factory(User::class)->create();
        $user->roles()->attach(Roles::CUSTOMER_ID);

        return $user;
    }

    public function create_review($user)
    {
        $userSite = factory(UserSite::class)->create(['user_id' => $user->id]);

        $review = factory(Review::class)->create(
            [
                'user_site_id' => $userSite->id,
                'email' => $user->email
            ]);

        return $review;
    }

    public function create_project($user, $projectName)
    {
        $project = factory(Project::class)->create(
            [
                'user_id' => $user->id,
                'name' => $projectName
            ]);

        $previewUpload = factory(PreviewUpload::class)->create(
            [
                'uploadable_id' => $project->id
            ]);

        $project->active_preview_id = $previewUpload->id;
        $project->save();

        return $project;
    }

    public function create_project_invitation($projectId)
    {
        $projectInvitation = factory(ProjectInvitation::class)->create(
            [
                'project_id' => $projectId
            ]);

        return $projectInvitation;
    }

    public function test_review_page()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $projectName = 'TEST 111';

        $user = $this->create_user();

        $review = $this->create_review($user);

        $project = $this->create_project($user, $projectName);

        $this->browse(function (Browser $browser) use ($user, $project, $review, $projectName) {
            $browser->visit($review->url . '/review/' . $project->permalink)
                ->assertSee($projectName);
        });
    }

    public function test_owner_should_see_same_clients_for_invitation_regardless_of_projects()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $firstProjectName = 'Project 1 for Invitation Test';
        $secondProjectName = 'Project 2 for Invitation Test';

        $user = $this->create_user();

        $review = $this->create_review($user);

        $firstProject = $this->create_project($user, $firstProjectName);
        $secondProject = $this->create_project($user, $secondProjectName);

        $projectInvitation = $this->create_project_invitation($firstProject->id);

        $this->browse(function (Browser $browser) use (
            $user,
            $firstProject,
            $secondProject,
            $review,
            $firstProjectName,
            $secondProjectName,
            $projectInvitation
        ) {
            $browser->loginAs($user)
                ->visit($review->url . '/review/' . $firstProject->permalink)
                ->assertSee($firstProjectName);

            $browser->click('#showNotificationModal')
                ->waitFor('.recipients-list .recipient')
                ->assertSee($projectInvitation->email);

            $browser->loginAs($user)
                ->visit($review->url . '/review/' . $secondProject->permalink)
                ->assertSee($secondProjectName);

            $browser->click('#showNotificationModal')
                ->waitFor('.recipients-list .recipient')
                ->assertSee($projectInvitation->email);
        });

    }
}
