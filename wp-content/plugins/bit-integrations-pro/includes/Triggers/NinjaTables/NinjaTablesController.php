<?php

namespace BitApps\BTCBI_PRO\Triggers\NinjaTables;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class NinjaTablesController
{
    public static function info()
    {
        return [
            'name'              => 'Ninja Tables',
            'title'             => __('Best Data Table Plugin for WordPress.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => NinjaTablesHelper::isPluginInstalled(),
            'documentation_url' => 'https://bit-integrations.com/wp-docs/trigger/ninja-tables-integrations/',
            'tutorial_url'      => '#',
            'note'              => '<p>' . __("The 'Row Deleted' trigger will execute for each deleted row.", 'bit-integrations') . '</b>',
            'tasks'             => [
                'action' => 'ninja_tables/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'ninja_tables/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'ninja_tables/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!NinjaTablesHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Ninja Tables'));
        }

        wp_send_json_success([
            ['form_name' => __('New Row Added', 'bit-integrations-pro'), 'triggered_entity_id' => 'ninja_table_after_add_item', 'skipPrimaryKey' => true],
            ['form_name' => __('Row Updated', 'bit-integrations-pro'), 'triggered_entity_id' => 'ninja_table_after_update_item', 'skipPrimaryKey' => true],
            ['form_name' => __('Row Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'ninja_table_before_items_deleted', 'skipPrimaryKey' => true],
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

    public static function handleNewRowAdded($insertId, $tableId, $attributes)
    {
        return static::flowExecute('ninja_table_after_add_item', $insertId, $tableId);
    }

    public static function handleRowUpdated($insertId, $tableId, $attributes)
    {
        return static::flowExecute('ninja_table_after_update_item', $insertId, $tableId);
    }

    public static function handleRowDeleted($insertIds, $tableId)
    {
        if (empty($insertIds) || empty($tableId)) {
            return;
        }

        foreach ($insertIds as $insertId) {
            static::flowExecute('ninja_table_before_items_deleted', $insertId, $tableId);
        }

        return ['type' => 'success'];
    }

    private static function flowExecute($triggered_entity_id, $insertId, $tableId)
    {
        if (empty($insertId) || empty($tableId)) {
            return;
        }

        $formData = NinjaTablesHelper::formatRowData($insertId, $tableId);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('NinjaTables', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('NinjaTables', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
