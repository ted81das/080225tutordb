<?php

namespace BitApps\BTCBI_PRO\Triggers\Hustle;

use BitCode\FI\Flow\Flow;
use Hustle_Model;
use Hustle_Module_Collection;

final class HustleController
{
    public static function info()
    {
        $plugin_path = 'wordpress-popup/popover.php';

        return [
            'name'           => 'Hustle',
            'title'          => __('Hustle', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'wordpress-popup/popover.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('hustle/opt-in.php') || is_plugin_active('wordpress-popup/popover.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'hustle/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'hustle/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true,
            'note'  => '<p>' . __('Only the <strong>Email Opt-in</strong> type pop-ups and slide-ins will be listed in the select box', 'bit-integrations-pro') . '</p>'
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active('hustle/opt-in.php') && !is_plugin_active('wordpress-popup/popover.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Hustle'));
        }

        $moduleList = [];
        $modules = Hustle_Module_Collection::instance()->get_all();

        if (!empty($modules)) {
            foreach ($modules as $module) {
                if ($module->module_type === 'social_sharing' || $module->module_mode === 'informational') {
                    continue;
                }

                $moduleList[] = (object) [
                    'id'    => $module->id,
                    'title' => $module->module_name,
                    'note'  => \sprintf(__('Runs after user submits email opt-in form of %s (%s)', 'bit-integrations-pro'), $module->module_name, $module->module_type)
                ];
            }
        }

        wp_send_json_success($moduleList);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('hustle/opt-in.php') && !is_plugin_active('wordpress-popup/popover.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Hustle'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bit-integrations-pro'));
        }

        $fields = self::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Fields fetching failed!', 'bit-integrations-pro'), 400);
        }

        $responseData['fields'] = $fields;

        wp_send_json_success($responseData);
    }

    public static function fields($formId)
    {
        $module = Hustle_Model::get_module($formId);

        if (is_wp_error($module)) {
            return false;
        }

        $formFields = $module->get_form_fields();

        if (empty($formFields)) {
            return false;
        }

        $fields = [];

        foreach ($formFields as $item) {
            if ($item['type'] !== 'submit') {
                $fields[] = [
                    'name'  => $item['name'],
                    'type'  => $item['type'],
                    'label' => $item['label'],
                ];
            }
        }

        return $fields;
    }

    public static function handleHustleSubmit($entry, $moduleId, $fieldDataArray)
    {
        if (empty($moduleId) || empty($fieldDataArray)) {
            return;
        }

        $flows = Flow::exists('Hustle', $moduleId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = self::prepareFlowData($fieldDataArray);

        if (!empty($data)) {
            Flow::execute('Hustle', $moduleId, $data, $flows);
        }
    }

    public static function prepareFlowData($fieldDataArray)
    {
        if (empty($fieldDataArray)) {
            return false;
        }

        foreach ($fieldDataArray as $item) {
            $data[$item['name']] = \is_array($item['value']) ? implode(',', $item['value']) : $item['value'];
        }

        return $data;
    }
}
