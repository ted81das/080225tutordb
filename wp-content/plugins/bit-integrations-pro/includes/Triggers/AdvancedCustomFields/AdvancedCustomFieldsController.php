<?php

namespace BitApps\BTCBI_PRO\Triggers\AdvancedCustomFields;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class AdvancedCustomFieldsController
{
    public static function info()
    {
        return [
            'name'              => 'Advanced Custom Fields (ACF)',
            'title'             => __('Advanced Custom Fields (ACF) helps you easily customize WordPress with powerful, professional and intuitive fields.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => AdvancedCustomFieldsHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'acf/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'acf/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'acf/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!AdvancedCustomFieldsHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Advanced Custom Fields'));
        }

        wp_send_json_success([
            ['form_name' => __('Field Updated On Options Page', 'bit-integrations-pro'), 'triggered_entity_id' => 'acf/save_post', 'skipPrimaryKey' => true],
            ['form_name' => __('Field Updated On Post', 'bit-integrations-pro'), 'triggered_entity_id' => 'updated_post_meta', 'skipPrimaryKey' => true],
            ['form_name' => __('Field Updated On User Profile', 'bit-integrations-pro'), 'triggered_entity_id' => 'updated_user_meta', 'skipPrimaryKey' => true],
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

    public static function handleFieldUpdatedOnOptionsPage($post_id)
    {
        if ('options' !== $post_id || !\function_exists('get_fields')) {
            return;
        }

        $formData = AdvancedCustomFieldsHelper::formatOptionPageACFData($post_id);

        return static::flowExecute('acf/save_post', $formData);
    }

    public static function handleFieldUpdatedOnPost($meta_id, $post_id, $meta_key, $meta_value)
    {
        if ('_edit_lock' === $meta_key || empty($post_id) || !\is_int($post_id) || empty($_POST['acf'])) {
            return;
        }

        $formData = AdvancedCustomFieldsHelper::formatPostACFData($post_id, $meta_key, $meta_value);

        return static::flowExecute('updated_post_meta', $formData);
    }

    public static function handleFieldUpdatedOnUserProfile($meta_id, $user_id, $meta_key, $meta_value)
    {
        if (empty($meta_id) || empty($user_id) || empty($meta_key) || empty($meta_value) || $meta_key === 'last_update') {
            return;
        }

        $formData = Helper::prepareFetchFormatFields([
            'field_key' => $meta_key,
            $meta_key   => $meta_value,
            'user'      => User::get($user_id)
        ]);

        return static::flowExecute('updated_user_meta', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('AdvancedCustomFields', $triggered_entity_id);

        if (!$flows) {
            return;
        }

        $data = array_column($formData, 'value', 'name');
        Flow::execute('AdvancedCustomFields', $triggered_entity_id, $data, $flows);

        return ['type' => 'success'];
    }
}
