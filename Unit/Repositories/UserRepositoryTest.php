<?php

namespace Tests\Unit\Repositories;

use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshAndSeedDatabase;

    /**
     * @var UserRepository
     */
    private $userRepo;

    public function setUp()
    {
        parent::setUp();

        $this->userRepo = \App::make(UserRepository::class);
    }

    public function test_new_seller_has_no_relation_for_profile()
    {
        $seller = factory(User::class)->create();
        $seller->roles()->attach(Roles::SELLER_ID);
        $this->assertNull($seller->sellerProfile);
    }

    public function test_new_seller_has_default_submission_limit()
    {
        $seller = factory(User::class)->create();
        $seller->roles()->attach(Roles::SELLER_ID);
        $this->assertEquals($seller->getSubmissionLimit(), config('submissions.limit'));
    }

    public function test_updating_new_seller_submission_limit_works()
    {
        $seller = factory(User::class)->create();
        $seller->roles()->attach(Roles::SELLER_ID);
        $this->userRepo->update($seller->id, ['submission_limit' => 11]);
        $seller->refresh();
        $this->assertEquals($seller->sellerProfile->submission_limit, 11);

        $this->userRepo->update($seller->id, ['submission_limit' => 9]);
        $seller->refresh();
        $this->assertEquals($seller->sellerProfile->submission_limit, 9);

        $this->userRepo->update($seller->id, ['submission_limit' => 0]);
        $seller->refresh();
        $this->assertEquals($seller->sellerProfile->submission_limit, 0);
    }

    public function test_other_user_types_dont_have_seller_profile()
    {
        $nonseller = factory(User::class)->create();
        $nonseller->roles()->attach(Roles::CUSTOMER_ID);
        $this->assertNull($nonseller->sellerProfile);
        $this->userRepo->update($nonseller->id, ['submission_limit' => 11]);
        $this->assertNull($nonseller->sellerProfile);
    }
}
