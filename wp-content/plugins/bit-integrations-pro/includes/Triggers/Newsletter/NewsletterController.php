<?php

namespace BitApps\BTCBI_PRO\Triggers\Newsletter;

use BitApps\BTCBI_PRO\Triggers\TriggerController;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Flow\Flow;

final class NewsletterController
{
    public static function info()
    {
        return [
            'name'              => 'Newsletter',
            'title'             => __('Newsletter is a powerful yet simple email creation tool that helps you get in touch with your subscribers and engage them with your own content.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/newsletter-integrations/',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'newsletter/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'newsletter/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'newsletter/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Newsletter'));
        }

        wp_send_json_success([
            ['form_name' => __('Subscription Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'newsletter_user_post_subscribe', 'skipPrimaryKey' => true]
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

    public static function handleSubscriptionFormSubmittedWithSpecificList($user)
    {
        if (!property_exists($user, 'id')) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields((array) $user);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_newsletter_user_post_subscribe_test', array_values($formData));

        $flows = Flow::exists('Newsletter', 'newsletter_user_post_subscribe');

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('Newsletter', 'newsletter_user_post_subscribe', $data, $flows);

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return (bool) (\defined('NEWSLETTER_VERSION'));
    }
}
