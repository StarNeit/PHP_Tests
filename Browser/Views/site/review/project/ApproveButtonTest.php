<?php

namespace Tests\Browser\Views\site\review\project;

use MotionArray\Models\StaticData\Roles;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ApproveButtonTest extends DuskTestCase
{
    public function create_user()
    {
        $user = factory(\MotionArray\Models\User::class)->create();
        $user->roles()->attach(Roles::CUSTOMER_ID);

        return $user;
    }

    public function create_review($user)
    {
        $userSite = factory(\MotionArray\Models\UserSite::class)->create(['user_id' => $user->id]);

        $review = factory(\MotionArray\Models\Review::class)->create(
            [
                'user_site_id' => $userSite->id,
                'email' => $user->email
            ]);

        return $review;
    }

    public function create_project($user, $projectName)
    {
        $project = factory(\MotionArray\Models\Project::class)->create(
            [
                'user_id' => $user->id,
                'name' => $projectName
            ]);

        $previewUpload = factory(\MotionArray\Models\PreviewUpload::class)->create(
            [
                'uploadable_id' => $project->id
            ]);

        $project->active_preview_id = $previewUpload->id;
        $project->save();

        return $project;
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

    public function test_owner_should_not_see_approve_button()
    {
        $this->markTestSkipped('FIXME: Fails.');
        $projectName = 'TEST TEST';

        $user = $this->create_user();

        $review = $this->create_review($user);

        $project = $this->create_project($user, $projectName);

        $this->browse(function (Browser $browser) use ($user, $project, $review, $projectName) {
            $browser->loginAs($user)
                ->visit($review->url . '/review/' . $project->permalink)
                ->assertSee($projectName);

            $browser->waitFor('.product-header__content', 50)
                ->assertDontSee('Approve');
        });
    }
}
