<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Includes a few examples on how to write proper unit tests
 */
class ExampleTest extends TestCase
{
    // NOTE: automated tests reset the database. Use appropriate settings if necessary.
    // use DatabaseMigrations; // run migrate before every test (does not run seeder)
    // use RefreshDatabase; // run migrate once per call to phpunit
    // use RefreshAndSeedDatabase; // run migrate and db:seed once per call to phpunit

    function setUp()
    {
        parent::setUp();
        // setup shared environment of tests in this class here.
    }

    public function testBadExample()
    {
        // Bad test, because it uses conditional.
        $one = 1;
        $two = 2;
        if ($one != $two)
            $this->assertFalse(false);
        else
            $this->assertTrue(false); //Shouldn't happen
    }

    public function testArrayMinimum()
    {
        // Good test example, has a descriptive function name.
        $array = [7, 5, 2, 3, 9];
        $this->assertEquals(min($array), 2, 'minimum of 7,5,2,3,9 was not 2');
    }
    // // Use these tests as stub to implement later.
    // public function testNotImplemented()
    // {
    //     $this->markTestIncomplete('This test has not been implemented yet.');
    // }

}
