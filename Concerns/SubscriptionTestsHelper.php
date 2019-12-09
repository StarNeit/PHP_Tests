<?php

namespace Tests\Concerns;

use App;
use Carbon\Carbon;
use Config;
use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\User;
use MotionArray\Repositories\UserSubscriptionRepository;
use Stripe;
use Stripe_Token;

trait SubscriptionTestsHelper
{
    /**
     * @var UserSubscriptionRepository
     */
    protected $userSubscription;

    public function setUpSubscriptionTests()
    {
        $stripeSecret = Config::get('services.stripe.secret');

        Stripe::setApiKey($stripeSecret);

        // Get ReviewRepository
        $this->userSubscription = App::make(UserSubscriptionRepository::class);
    }

    private function createUserWithSubscription($token, $cycle)
    {
        if (null === $token) {
            $token = $this->getCardToken('4242424242424242');
        }
        $user = factory(User::class)->create();

        return $this->subscribe($user, $token, $cycle);
    }

    private function subscribe($user, $token, $cycle)
    {
        if (null === $cycle) {
            $cycle = Plans::CYCLE_MONTHLY;
        }
        $plan = $this->getPlanByCycle($cycle);

        return $this->userSubscription->create($user, $plan, $token->id);
    }

    private function getPlanByCycle($cycle): Plan
    {
        return Plan::active()->where('billing_id', '!=', 'free')
            ->where('cycle', '=', $cycle)
            ->inRandomOrder()
            ->first();
    }

    private function getCardToken($card)
    {
        return Stripe_Token::create([
            "card" => [
                "number" => $card,
                "exp_month" => 1,
                "exp_year" => (new Carbon('+2 years'))->format('Y'),
                "cvc" => "314",
            ]
        ]);
    }
}
