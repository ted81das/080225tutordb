<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentBoards;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class FluentBoardsController
{
    public static function info()
    {
        return [
            'name'              => 'Fluent Boards',
            'title'             => __('FluentBoards is the Ultimate Scheduling Solution for WordPress. Harness the power of unlimited appointments, bookings, webinars, events, sales calls, etc., and save time with scheduling automation.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'fluent_boards/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'fluent_boards/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'fluent_boards/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'FluentBoards'));
        }

        wp_send_json_success([
            ['form_name' => __('New Board Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_boards/board_created', 'skipPrimaryKey' => true],
            ['form_name' => __('Board Member Added', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_boards/board_member_added', 'skipPrimaryKey' => false],
            ['form_name' => __('New Task Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'fluent_boards/task_created', 'skipPrimaryKey' => false],
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

    public static function handleNewBoardCreated($board)
    {
        if (empty($board)) {
            return;
        }
        $formData = Helper::prepareFetchFormatFields((array) json_decode(wp_json_encode($board)));

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_boards/board_created_test', array_values($formData));

        $flows = Flow::exists('FluentBoards', 'fluent_boards/board_created');
        if (!$flows) {
            return;
        }

        Flow::execute('FluentBoards', 'fluent_boards/board_created', array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }

    public static function handleBoardMemberAdded($board_id, $board_member)
    {
        if (empty($board_id) || empty($board_member)) {
            return;
        }

        $data = array_merge(['board_id' => $board_id], (array) json_decode(wp_json_encode($board_member)));
        $formData = Helper::prepareFetchFormatFields($data);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_boards/board_member_added_test', array_values($formData), 'board_id.value', $formData['board_id']['value']);

        return static::flowExecute('fluent_boards/board_member_added', $formData);
    }

    public static function handleNewTaskCreated($task)
    {
        if (empty($task)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields((array) json_decode(wp_json_encode($task)));

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_boards/task_created_test', array_values($formData), 'board_id.value', $formData['board_id']['value']);

        return static::flowExecute('fluent_boards/task_created', $formData);
    }

    public static function isPluginInstalled()
    {
        return \defined('FLUENT_BOARDS');
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        $flows = Flow::exists('FluentBoards', $triggered_entity_id);
        if (!$flows) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) || !Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                continue;
            }

            Flow::execute('FluentBoards', $flow->triggered_entity_id, array_column($formData, 'value', 'name'), [$flow]);
        }

        return ['type' => 'success'];
    }
}
