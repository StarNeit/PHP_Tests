<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use MotionArray\Models\StaticData\PaymentGateways;
use MotionArray\Models\StaticData\SubscriptionStatuses;
use MotionArray\Models\Subscription;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SkippedPaymentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payment_skipped()
    {
        $formParams = json_decode('{"payment_cycle":"Monthly","txn_type":"recurring_payment_skipped","last_name":"......","next_payment_date":"03:00:00 Aug 20, 2019 PDT","residence_country":"LS","initial_payment_amount":"0.00","currency_code":"USD","time_created":"11:40:55 Aug 15, 2019 PDT","verify_sign":"A77V7WdPtuVw5wNNq8T-EcSdi1rtAbjKgtPCFsZeP.InpTtjWw.rx0.j","period_type":" Regular","payer_status":"unverified","tax":"0.00","payer_email":"ppppppp@gmail.com","first_name":"......","receiver_email":"motionarray@gmail.com","payer_id":"4ZTS8MSJFLWVW","product_type":"1","shipping":"0.00","amount_per_cycle":"29.00","profile_status":"Active","charset":"windows-1252","notify_version":"3.9","amount":"29.00","outstanding_balance":"0.00","recurring_payment_id":"I-BVU88L004V5V","product_name":"Monthly","ipn_track_id":"32be32b8de1a1"}', true);

        factory(Subscription::class)->create([
            'payment_gateway_id' => PaymentGateways::PAYPAL_ID,
            'payment_gateway_customer_id' => '4ZTS8MSJFLWVW',
            'payment_gateway_subscription_id' => 'I-BVU88L004V5V',
            'payment_gateway_email' => 'ppppppp@gmail.com',
        ]);

        $request = $this->json('POST', '/paypal/webhook', $formParams);
        $request->assertStatus(200);
        $subscription = Subscription::where('payment_gateway_subscription_id', 'I-BVU88L004V5V')
            ->first();
        $this->assertEquals(SubscriptionStatuses::STATUS_SUSPENDED_ID, $subscription->subscription_status_id);
    }

    public function test_payment_profile_canceled_when_it_is_active()
    {
        $formParams = json_decode('{"payment_cycle":"Monthly","txn_type":"recurring_payment_profile_cancel","last_name":"......","next_payment_date":"03:00:00 Aug 20, 2019 PDT","residence_country":"LS","initial_payment_amount":"0.00","currency_code":"USD","time_created":"11:40:55 Aug 15, 2019 PDT","verify_sign":"A77V7WdPtuVw5wNNq8T-EcSdi1rtAbjKgtPCFsZeP.InpTtjWw.rx0.j","period_type":" Regular","payer_status":"unverified","tax":"0.00","payer_email":"ppppppp@gmail.com","first_name":"......","receiver_email":"motionarray@gmail.com","payer_id":"4ZTS8MSJFLWVW","product_type":"1","shipping":"0.00","amount_per_cycle":"29.00","profile_status":"Active","charset":"windows-1252","notify_version":"3.9","amount":"29.00","outstanding_balance":"0.00","recurring_payment_id":"I-BVU88L004V5V","product_name":"Monthly","ipn_track_id":"32be32b8de1a1"}', true);

        factory(Subscription::class)->create([
            'payment_gateway_id' => PaymentGateways::PAYPAL_ID,
            'payment_gateway_customer_id' => '4ZTS8MSJFLWVW',
            'payment_gateway_subscription_id' => 'I-BVU88L004V5V',
            'payment_gateway_email' => 'ppppppp@gmail.com',
            'subscription_status_id' => SubscriptionStatuses::STATUS_ACTIVE_ID,
        ]);

        $request = $this->json('POST', '/paypal/webhook', $formParams);
        $request->assertStatus(200);
        /** @var Subscription $subscription */
        $subscription = Subscription::where('payment_gateway_subscription_id', 'I-BVU88L004V5V')
            ->first();
        $this->assertEquals(SubscriptionStatuses::STATUS_ACTIVE_ID, $subscription->subscription_status_id);
        $this->assertEquals(1, $subscription->user->billingActions()->count());
    }

    public function test_payment_profile_canceled_when_it_is_suspended()
    {
        $formParams = json_decode('{"payment_cycle":"Monthly","txn_type":"recurring_payment_profile_cancel","last_name":"......","next_payment_date":"03:00:00 Aug 20, 2019 PDT","residence_country":"LS","initial_payment_amount":"0.00","currency_code":"USD","time_created":"11:40:55 Aug 15, 2019 PDT","verify_sign":"A77V7WdPtuVw5wNNq8T-EcSdi1rtAbjKgtPCFsZeP.InpTtjWw.rx0.j","period_type":" Regular","payer_status":"unverified","tax":"0.00","payer_email":"ppppppp@gmail.com","first_name":"......","receiver_email":"motionarray@gmail.com","payer_id":"4ZTS8MSJFLWVW","product_type":"1","shipping":"0.00","amount_per_cycle":"29.00","profile_status":"Active","charset":"windows-1252","notify_version":"3.9","amount":"29.00","outstanding_balance":"0.00","recurring_payment_id":"I-BVU88L004V5V","product_name":"Monthly","ipn_track_id":"32be32b8de1a1"}', true);

        factory(Subscription::class)->create([
            'payment_gateway_id' => PaymentGateways::PAYPAL_ID,
            'payment_gateway_customer_id' => '4ZTS8MSJFLWVW',
            'payment_gateway_subscription_id' => 'I-BVU88L004V5V',
            'payment_gateway_email' => 'ppppppp@gmail.com',
            'subscription_status_id' => SubscriptionStatuses::STATUS_SUSPENDED_ID,
        ]);

        $request = $this->json('POST', '/paypal/webhook', $formParams);
        $request->assertStatus(200);
        /** @var Subscription $subscription */
        $subscription = Subscription::where('payment_gateway_subscription_id', 'I-BVU88L004V5V')
            ->first();
        $this->assertEquals(SubscriptionStatuses::STATUS_CANCELED_ID, $subscription->subscription_status_id);
        $this->assertEquals(0, $subscription->user->billingActions()->count());
    }
}
