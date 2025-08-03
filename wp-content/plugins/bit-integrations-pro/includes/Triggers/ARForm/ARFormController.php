<?php

namespace BitApps\BTCBI_PRO\Triggers\ARForm;

use BitCode\FI\Flow\Flow;

final class ARFormController
{
    public static function info()
    {
        $plugin_path = 'arforms-form-builder/arforms-form-builder.php';

        return [
            'name'           => 'ARForm',
            'title'          => __('ARForms - More than just a WordPress Form Builder', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'arforms/arforms.php',
            'type'           => 'form',
            'is_active'      => self::isARFormActive(),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'arform/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'arform/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function isARFormActive()
    {
        return is_plugin_active('arforms-form-builder/arforms-form-builder.php') || is_plugin_active('arforms/arforms.php');
    }

    public function getAll()
    {
        if (!self::isARFormActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'ARForm'));
        }

        $forms = self::getAllARForms();

        $all_forms = [];
        if ($forms) {
            foreach ($forms as $form) {
                $all_forms[] = (object) [
                    'id'    => $form->id,
                    'title' => $form->name,
                ];
            }
        }
        wp_send_json_success($all_forms);
    }

    public function get_a_form($data)
    {
        if (!self::isARFormActive()) {
            wp_send_json_error(__('ARForms is not installed or activated', 'bit-integrations-pro'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = self::fields($data->id);
        if (empty($fields)) {
            wp_send_json_error(__('Form doesn\'t exists any field', 'bit-integrations-pro'));
        }

        $responseData['fields'] = $fields;
        wp_send_json_success($responseData);
    }

    public static function fields($form_id)
    {
        $fields = [];

        if (! empty($GLOBALS['arfliteversion']) && version_compare($GLOBALS['arfliteversion'], '1.5.8', '<=') && !is_plugin_active('arforms/arforms.php')) {
            $fields = static::getFieldsFor100($form_id);
        } else {
            $fields = static::getFieldsFor159($form_id);
        }

        foreach ($fields as $field) {
            $fields[] = [
                'name'  => $field->id,
                'type'  => $field->type,
                'label' => $field->name,
            ];
        }

        return $fields;
    }

    public static function handleArFormSubmit($params, $errors, $form, $item_meta_values)
    {
        $form_id = $form->id;

        if (!empty($form_id) && $flows = Flow::exists('ARForm', $form_id)) {
            Flow::execute('ARForm', $form_id, $item_meta_values, $flows);
        }
    }

    public static function getAllARForms()
    {
        if (! empty($GLOBALS['arfliteversion']) && version_compare($GLOBALS['arfliteversion'], '1.5.8', '<=') && !is_plugin_active('arforms/arforms.php')) {
            return static::getFormFor100();
        }

        return static::getFormFor159();
    }

    private static function getFormFor159()
    {
        global $wpdb;

        $forms = $wpdb->get_results($wpdb->prepare("SELECT id,name FROM {$wpdb->prefix}arf_forms WHERE is_template = 0 AND status = 'published'"));

        if (is_wp_error($forms)) {
            return [];
        }

        return $forms;
    }

    private static function getFormFor100()
    {
        global $wpdb;

        $forms = $wpdb->get_results($wpdb->prepare("SELECT id,name FROM {$wpdb->prefix}arflite_forms WHERE is_template = 0 AND status = 'published'"));

        if (is_wp_error($forms)) {
            return [];
        }

        return $forms;
    }

    private static function getFieldsFor100($form_id)
    {
        global $wpdb;

        $fields = $wpdb->get_results($wpdb->prepare("SELECT id,field_key,name,type,required FROM {$wpdb->prefix}arf_fields WHERE form_id = %d", $form_id));

        if (is_wp_error($fields)) {
            return [];
        }

        return $fields;
    }

    private static function getFieldsFor159($form_id)
    {
        global $wpdb;

        $fields = $wpdb->get_results($wpdb->prepare("SELECT id,field_key,name,type,required FROM {$wpdb->prefix}arflite_fields WHERE form_id = %d", $form_id));

        if (is_wp_error($fields)) {
            return [];
        }

        return $fields;
    }
}
