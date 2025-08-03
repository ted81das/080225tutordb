<?php

namespace BitApps\BTCBI_PRO\Triggers\WpSimplePay;

use BitApps\BTCBI_PRO\Triggers\TriggerController;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Flow\Flow;

final class WpSimplePayController
{
    public static function info()
    {
        return [
            'name'              => 'WP Simple Pay',
            'title'             => __('WP Simple Pay is a WordPress Payment form plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => WpSimplePayHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'wp_simple_pay/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'wp_simple_pay/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'wp_simple_pay/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!WpSimplePayHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WP Simple Pay'));
        }

        wp_send_json_success([
            ['form_name' => __('Payment For Form Completed', 'bit-integrations-pro'), 'triggered_entity_id' => 'simpay_webhook_payment_intent_succeeded', 'skipPrimaryKey' => false],
            ['form_name' => __('Subscription For Form Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'simpay_webhook_subscription_created', 'skipPrimaryKey' => false],
            ['form_name' => __('Subscription For Form Renewed', 'bit-integrations-pro'), 'triggered_entity_id' => 'simpay_webhook_invoice_payment_succeeded', 'skipPrimaryKey' => false],
        ]);
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handlePaymentForFormCompleted($type, $object)
    {
        if (empty($object->metadata->simpay_form_id)) {
            return;
        }

        $formData = WpSimplePayHelper::formatPaymentFormData($object);

        return static::flowExecute('simpay_webhook_payment_intent_succeeded', $formData);
    }

    public static function handleSubscriptionForFormCreated($type, $object)
    {
        if (empty($object->metadata->simpay_form_id) || empty($object->latest_invoice)) {
            return;
        }

        $formData = WpSimplePayHelper::formatSubscriptionFormData($object);

        return static::flowExecute('simpay_webhook_subscription_created', $formData);
    }

    public static function handleSubscriptionForFormRenewed($type, $object)
    {
        if (empty($object->metadata->simpay_form_id) || empty($object->latest_invoice)) {
            return;
        }

        $formData = WpSimplePayHelper::formatSubscriptionFormData($object);

        return static::flowExecute('simpay_webhook_invoice_payment_succeeded', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData), 'form_id.value', $formData['form_id']['value']);

        $flows = Flow::exists('WpSimplePay', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) || !Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                continue;
            }

            Flow::execute('WpSimplePay', $flow->triggered_entity_id, array_column($formData, 'value', 'name'), [$flow]);
        }

        return ['type' => 'success'];
    }
}
