<?php

namespace BitApps\BTCBI_PRO\Triggers\WpAllImport;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Post;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class WpAllImportController
{
    public static function info()
    {
        return [
            'name'              => 'WP All Import',
            'title'             => __('WP All Import plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'wp_all_import/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'wp_all_import/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'wp_all_import/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WP All Import'));
        }

        wp_send_json_success([
            ['form_name' => __('Import Completed', 'bit-integrations-pro'), 'triggered_entity_id' => 'pmxi_after_xml_import', 'skipPrimaryKey' => true],
            ['form_name' => __('Import Failed', 'bit-integrations-pro'), 'triggered_entity_id' => 'pmxi_after_xml_import_failed', 'skipPrimaryKey' => true],
            ['form_name' => __('Post Type Imported', 'bit-integrations-pro'), 'triggered_entity_id' => 'pmxi_saved_post', 'skipPrimaryKey' => true],
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

    public static function handleImportCompleted($import_id, $import_obj)
    {
        if (empty($import_id) || (isset($import_obj->failed) && $import_obj->failed)) {
            return false;
        }

        $formData = static::formatImportObject($import_obj);

        return static::flowExecute('pmxi_after_xml_import', $formData);
    }

    public static function handleImportFailed($import_id, $import_obj)
    {
        if (empty($import_id) || (isset($import_obj->failed) && $import_obj->failed == 0)) {
            return false;
        }

        $formData = static::formatImportObject($import_obj);

        return static::flowExecute('pmxi_after_xml_import_failed', $formData);
    }

    public static function handlePostTypeImported($post_id, $xml_node, $is_update)
    {
        if (empty($post_id)) {
            return false;
        }

        $formData = Helper::prepareFetchFormatFields(Post::get($post_id));

        return static::flowExecute('pmxi_saved_post', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('WpAllImport', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        Flow::execute('WpAllImport', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }

    private static function formatImportObject($import_obj)
    {
        $data = (array) json_decode(wp_json_encode($import_obj));
        unset($data['options']);

        return Helper::prepareFetchFormatFields($data);
    }

    private static function isPluginInstalled()
    {
        return class_exists('PMXI_Plugin');
    }
}
