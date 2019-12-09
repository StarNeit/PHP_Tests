<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectCommentAuthor extends TestCase
{
    private $projectCommentAuthor;

    public function setUp()
    {
        parent::setUp();

        $this->projectCommentAuthor = \App::make('MotionArray\Repositories\ProjectCommentAuthorRepository');
    }

    public function test_author_should_be_created_even_though_there_is_thumbnail()
    {
        $authorData = [
            'email' => 'test1234@test.com',
            'name' => 'Test Test',
            'thumbnail' => 'TEST'
        ];

        $author = $this->projectCommentAuthor->getOrCreate($authorData);

        $this->assertEquals($author->email, $authorData['email']);
    }
}
