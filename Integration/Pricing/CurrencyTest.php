<?php

namespace Tests\Integration\Pricing;

use MotionArray\Models\Plan;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    public function test_us_dollars_page_is_working_as_expected()
    {
        $response = $this->get('/pricing');

        preg_match('/PLANS_DATA.site_plans = (.*?);/', $response->getContent(), $plans);
        if ($plans) {
            $plans = json_decode($plans[1]);
            foreach ($plans as $plan) {
                $actualPlan = Plan::find($plan->id);
                $this->assertEquals($plan->price, $actualPlan->price);
            }
        }
    }

    public function test_gbp_page_is_working_as_expected()
    {
        $response = $this->get('/pricing?currency=GBP');

        preg_match('/PLANS_DATA.site_plans = (.*?);/', $response->getContent(), $plans);
        if ($plans) {
            $plans = json_decode($plans[1]);
            foreach ($plans as $plan) {
                if ($plan->price > 0) {
                    $actualPlan = Plan::find($plan->id);
                    $this->assertNotEquals($plan->price, $actualPlan->price);
                }
            }
        }
    }
}
