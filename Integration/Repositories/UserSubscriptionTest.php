<?php

namespace Tests\Integration\Repositories;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\User;
use Tests\Concerns\SubscriptionTestsHelper;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class UserSubscriptionTest extends TestCase
{
    use SubscriptionTestsHelper;
    use RefreshAndSeedDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->setUpSubscriptionTests();
    }
    /**
     * Test Monthly Subscription Creation.
     */
    public function test_create_monthly_subscription()
    {
        $token = $this->getCardToken('4242424242424242');
        $user = $this->createUserWithSubscription($token, Plans::CYCLE_MONTHLY);
        $user->fresh();
        $this->assertTrue($user->isPayingMember(), 'Should create a paying member');
        $this->assertTrue($user->plan->isMonthly(), 'Should create a user on a monthly plan');
    }
    /**
     * Test Yearly Subscription Creation.
     */
    public function test_create_yearly_subscription()
    {
        $token = $this->getCardToken('4242424242424242');
        $user = $this->createUserWithSubscription($token, Plans::CYCLE_YEARLY);
        $user->fresh();
        $this->assertTrue($user->isPayingMember(), 'Should create a paying member');
        $this->assertTrue($user->plan->isYearly(), 'Should create a user on a yearly plan');
    }
    /**
     * Test Upgrade to Yearly from Monthly.
     */
    public function test_upgrade_monthly_to_yearly()
    {
        $token = $this->getCardToken('4242424242424242');
        $user = $this->createUserWithSubscription($token, Plans::CYCLE_MONTHLY);
        $user = $user->fresh();
        $this->assertTrue($user->isPayingMember(), 'Should create a paying member');
        $this->assertTrue($user->plan->isMonthly(), 'Should create a user on a monthly plan');
        $yearlyPlan = $this->getPlanByCycle(Plans::CYCLE_YEARLY);
        $this->userSubscription->upgrade($user, $yearlyPlan);
        $user = $user->fresh();
        $this->assertTrue($user->isPayingMember(), 'Should create a paying member');
        $this->assertTrue($user->plan->isYearly(), 'Should create a user on a yearly plan');
    }
    /**
     * Test the customer_id is saved on the DB before attempting to charge.
     */
    public function test_customer_save_on_failing_payment()
    {
        $badToken = $this->getCardToken('4000000000000341');
        $user = factory(User::class)->create();
        try {
            $this->subscribe($user, $badToken, Plans::CYCLE_MONTHLY);
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\Stripe_CardError::class, $exception);
        }
        $user = $user->fresh();
        $this->assertNull($user->stripe_id, 'Should save stripe_id');
    }
    /**
     * This test creates and outstanding invoice using a CC with no funds
     * after updating card, if the account is upgraded, the old invoice should be removed.
     * This prevents the user to be charged twice.
     */
    public function test_remove_outstanding_invoice()
    {
        $token = $this->getCardToken('4242424242424242');
        $user = $this->createUserWithSubscription($token, Plans::CYCLE_MONTHLY);
        /**
         * 1. Create outstanding Invoice
         * We do this by changing to a card with no funds and creating a new invoice.
         */
        $badToken = $this->getCardToken('4000000000000341');
        $this->userSubscription->updateCard($user, $badToken->id);
        \Stripe_InvoiceItem::create(['customer' => $user->stripe_id, 'amount' => 1000, 'currency' => 'usd', 'description' => 'Extra invoice']);
        try {
            \Stripe_Invoice::create(['customer' => $user->stripe_id])->pay();
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\Stripe_CardError::class, $exception);
        }
        /**
         * 2. Upgrade
         * The outstanding invoice should be removed on this step.
         */
        $token = $this->getCardToken('4242424242424242');
        $this->userSubscription->updateCard($user, $token->id);
        $yearlyPlan = $this->getPlanByCycle(Plans::CYCLE_YEARLY);
        $this->userSubscription->upgrade($user, $yearlyPlan);
        $user = $user->fresh();
        /**
         * 3. Test invoice was removed.
         */
        $invoices = $user->subscription()->invoices(true);
        $outstandingInvoices = array_filter($invoices, function ($invoice) {
            return !$invoice->paid && 'void' != $invoice->status;
        });
        $this->assertCount(0, $outstandingInvoices, 'Should not have any outstanding invoices');
    }
}
