<?php

namespace Tests\Integration\Http\Controllers\Webhook;

use MotionArray\Models\StaticData\SubscriptionPaymentStatuses;
use MotionArray\Models\StaticData\SubscriptionStatuses;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\SubscriptionPayment;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class PaymentOnHoldTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function test_payment_on_hold_and_resumed()
    {
        /** @var SubscriptionPayment $subscriptionPayment */
        $subscriptionPayment = factory(SubscriptionPayment::class)->create();
        $this->assertTrue($subscriptionPayment->subscription->user->isSubscriptionActive(), 'User has valid subscription!');

        $json = $this->getPaymentOnHoldJson($subscriptionPayment);
        $this->assertEquals(0, $this->getUserEventCount($subscriptionPayment->subscription->user, UserEventLogTypes::SUBSCRIPTION_SUSPENDED_BY_PAYMENT_ON_HOLD_ID));
        $request = $this->json('POST', '/paypal/webhook', $json);
        $request->assertStatus(200);

        $subscriptionPayment = $subscriptionPayment->refresh();
        $subscription = $subscriptionPayment->subscription->refresh();
        $this->assertEquals(SubscriptionStatuses::STATUS_PENDING_ID, $subscription->subscription_status_id);
        $this->assertEquals(SubscriptionPaymentStatuses::STATUS_PENDING_ID, $subscriptionPayment->subscription_payment_status_id);
        $this->assertEquals(1, $this->getUserEventCount($subscriptionPayment->subscription->user, UserEventLogTypes::SUBSCRIPTION_SUSPENDED_BY_PAYMENT_ON_HOLD_ID));
        $user = $subscription->user()->first();
        $this->assertFalse($user->isSubscriptionActive(), 'User\'s subscription is not active because it has been suspended.');

        $json = $this->getPaymentCompletedJson($subscriptionPayment);
        $request = $this->json('POST', '/paypal/webhook', $json);
        $request->assertStatus(200);
        $subscriptionPayment = $subscriptionPayment->refresh();
        $subscription = $subscriptionPayment->subscription->refresh();
        $this->assertEquals(SubscriptionStatuses::STATUS_ACTIVE_ID, $subscription->subscription_status_id);
        $this->assertEquals(SubscriptionPaymentStatuses::STATUS_SUCCESS_ID, $subscriptionPayment->subscription_payment_status_id);
        $this->assertEquals(1, $this->getUserEventCount($subscriptionPayment->subscription->user, UserEventLogTypes::SUBSCRIPTION_RESUMED_AFTER_PAYMENT_HOLD_ID));
        $user = $subscription->user()->first();
        $this->assertTrue($user->isSubscriptionActive(), 'User\'s subscription resumed!');
    }

    private function getUserEventCount(User $user, int $eventLogTypeId)
    {
        return UserEventLog::where('user_id', $user->id)
            ->where('user_event_log_type_id', $eventLogTypeId)
            ->count();
    }

    private function getPaymentOnHoldJson(SubscriptionPayment $subscriptionPayment): array
    {
        return json_decode('{"mc_gross":"29.00","invoice":"4IlWG8vKKm4kjw5t","protection_eligibility":"Ineligible","item_number1":"","payer_id":"FBUARYHATRF8C","tax":"0.00","payment_date":"03:54:37 Sep 19, 2019 PDT","payment_status":"Pending","charset":"windows-1252","mc_shipping":"0.00","mc_handling":"0.00","first_name":"Sellerultrajohn","mc_fee":"1.58","notify_version":"3.9","custom":"","payer_status":"unverified","business":"motionarray@gmail.com","num_cart_items":"1","verify_sign":"AzkkkAS265i38GyCmEyNhYb7bbx5APiPDmf09.VJilPDi9duVIea75L4","payer_email":"sellerultrajohn@yahoo.com","tax1":"0.00","txn_id":"'.$subscriptionPayment->gateway_payment_id.'","payment_type":"instant","last_name":"doe","item_name1":"Monthly","receiver_email":"motionarray@gmail.com","payment_fee":"1.58","shipping_discount":"0.00","quantity1":"1","insurance_amount":"0.00","receiver_id":"DBV6RU9PPNM6U","pending_reason":"paymentreview","txn_type":"cart","discount":"0.00","mc_gross_1":"29.00","mc_currency":"USD","residence_country":"GB","shipping_method":"Default","transaction_subject":"Monthly","payment_gross":"29.00","ipn_track_id":"a38061d1b7a1"}', true);
    }

    private function getPaymentCompletedJson(SubscriptionPayment $subscriptionPayment): array
    {
        return json_decode('{"mc_gross":"29.00","invoice":"4IlWG8vKKm4kjw5t","protection_eligibility":"Ineligible","item_number1":"","payer_id":"FBUARYHATRF8C","tax":"0.00","payment_date":"03:54:37 Sep 19, 2019 PDT","payment_status":"Completed","charset":"windows-1252","mc_shipping":"0.00","mc_handling":"0.00","first_name":"Sellerultrajohn","mc_fee":"1.58","notify_version":"3.9","custom":"","payer_status":"unverified","business":"motionarray@gmail.com","num_cart_items":"1","mc_handling1":"0.00","verify_sign":"AsAAQne2GLTCS3PhjEQ1EE.PW.fTA5VED1XaG26HLStFdCaYtsWi-YyT","payer_email":"sellerultrajohn@yahoo.com","mc_shipping1":"0.00","tax1":"0.00","txn_id":"'.$subscriptionPayment->gateway_payment_id.'","payment_type":"instant","last_name":"doe","item_name1":"Monthly","receiver_email":"motionarray@gmail.com","payment_fee":"1.58","shipping_discount":"0.00","quantity1":"1","insurance_amount":"0.00","receiver_id":"DBV6RU9PPNM6U","txn_type":"cart","discount":"0.00","mc_gross_1":"29.00","mc_currency":"USD","residence_country":"GB","shipping_method":"Default","transaction_subject":"Monthly","payment_gross":"29.00","ipn_track_id":"3aadbe8c1ba53\n"}', true);
    }
}
