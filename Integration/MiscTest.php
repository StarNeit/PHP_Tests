<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

/**
 * Putting orphaned tests here until they have their own file
 */
class MiscTest extends TestCase
{
    private function check_share_links($response)
    {
        $url = config('app.url');
        $encoded_url = urlencode($url);        $response->assertSee("Share this:");
        $response->assertSee("Twitter");
        $response->assertSee("https://twitter.com/intent/tweet?source={$encoded_url}&text={$encoded_url}&via=motionarray");
        $response->assertSee("Facebook");
        $response->assertSee("https://www.facebook.com/sharer/sharer.php?u={$encoded_url}&t=");
        $response->assertSee("Google+");
        $response->assertSee("https://plus.google.com/share?url={$encoded_url}");
    }
    public function test_blog_sharing_links_use_proper_host()
    {
        $response = $this->get('/blog/7-best-cinematography-tutorials-for-beginners');
        $this->check_share_links($response);
    }
    public function test_tutorial_sharing_links_use_proper_host()
    {
        $response = $this->get('/tutorials/plugins/premiere-pro/how-to-use-motion-arrays-stretch-plugin');
        $this->check_share_links($response);
    }
    public function test_make_sure_motionarray_dot_com_is_not_hardcoded()
    {
        config(['app.url' => 'localhost']);
        $response = $this->get('/');
        $response->assertDontSee("http://motionarray.com");
        $response->assertDontSee("https://motionarray.com");

        $response = $this->get('/tutorials');
        $response->assertDontSee("http://motionarray.com");
        $response->assertDontSee("https://motionarray.com");
    }
}
