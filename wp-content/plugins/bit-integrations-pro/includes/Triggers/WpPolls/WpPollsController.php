<?php

namespace BitApps\BTCBI_PRO\Triggers\WpPolls;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class WpPollsController
{
    public static function info()
    {
        return [
            'name'              => 'WP-Polls',
            'title'             => __('WP-Polls is a WordPress polls plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => WpPollsHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'wp_polls/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'wp_polls/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'wp_polls/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!WpPollsHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WP-Polls'));
        }

        wp_send_json_success([
            ['form_name' => __('Poll Submitted', 'bit-integrations-pro'), 'triggered_entity_id' => 'wp_polls_vote_poll_success', 'skipPrimaryKey' => true],
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

    public static function handlePollSubmitted()
    {
        $poll = WpPollsHelper::getPollIds();

        if (empty($poll) || !\is_array($poll)) {
            return;
        }

        $formData = WpPollsHelper::formatPollData($poll['selected_answers_ids'], $poll['poll_id']);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_wp_polls_vote_poll_success_test', array_values($formData));

        $flows = Flow::exists('WpPolls', 'wp_polls_vote_poll_success');
        if (!$flows) {
            return;
        }

        Flow::execute('WpPolls', 'wp_polls_vote_poll_success', array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
