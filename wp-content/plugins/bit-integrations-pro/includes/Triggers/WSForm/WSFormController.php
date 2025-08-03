<?php

namespace BitApps\BTCBI_PRO\Triggers\WSForm;

use BitCode\FI\Flow\Flow;

final class WSFormController
{
    public static function info()
    {
        $plugin_path = 'ws-form-pro/ws-form.php';

        return [
            'name'           => 'WSForm',
            'title'          => __('WSForm - WS Form LITE is a powerful contact form builder plugin for WordPress', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'ws-form-pro/ws-form.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('ws-form-pro/ws-form.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'wsform/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'wsform/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'note'  => '<h4>' . __('Setup Action Hook', 'bit-integrations-pro') . '</h4><ul><li>' . __('Goto', 'bit-integrations-pro') . ' <b>' . __('Actions', 'bit-integrations-pro') . ' </b>' . __('and create an action', 'bit-integrations-pro') . '</li><li>' . __('Select Action', 'bit-integrations-pro') . ' <b>' . __('Run WordPress Hook', 'bit-integrations-pro') . '</b></li><li>' . __('Select Type', 'bit-integrations-pro') . ' <b>' . __('Action', 'bit-integrations-pro') . '</b></li><li>' . __('Add Hook Tag', 'bit-integrations-pro') . ' <b>ws_form_action_for_bi</b></li></ul><h4>' . __('File Upload', 'bit-integrations-pro') . '</h4><ul><li>' . __('Goto', 'bit-integrations-pro') . ' <b>' . __('Field Settings', 'bit-integrations-pro') . '</b></li><li>' . __('Under File Handler select Save To', 'bit-integrations-pro') . ' <b>' . __('WS Form (Public)', 'bit-integrations-pro') . '</b></li></ul>',
            'isPro' => true
        ];
    }

    public static function handle_ws_form_submit($form, $submit)
    {
        $form_id = $submit->form_id;

        $flows = Flow::exists('WSForm', $form_id);
        if (!$flows) {
            return;
        }

        $data = [];
        if (isset($submit->meta)) {
            foreach ($submit->meta as $key => $field_value) {
                if (empty($field_value) || (\is_array($field_value) && !\array_key_exists('id', $field_value))) {
                    continue;
                }
                $value = wsf_submit_get_value($submit, $key);

                if (($field_value['type'] == 'file' || $field_value['type'] == 'signature') && !empty($value)) {
                    $upDir = wp_upload_dir();
                    $files = $value;
                    $value = [];

                    if (\is_array($files)) {
                        foreach ($files as $k => $file) {
                            if (\array_key_exists('hash', $file)) {
                                continue;
                            }
                            $value[$k] = $upDir['basedir'] . '/' . $file['path'];
                        }
                    }
                } elseif ($field_value['type'] == 'radio') {
                    $value = \is_array($value) ? $value[0] : $value;
                }
                $data[$key] = $value;
            }
        }

        Flow::execute('WSForm', $form_id, $data, $flows);
    }

    public function getAll()
    {
        if (!is_plugin_active('ws-form-pro/ws-form.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WS Form Pro'));
        }

        $forms = wsf_form_get_all(true, 'label');

        $all_forms = [];
        if ($forms) {
            foreach ($forms as $form) {
                $all_forms[] = (object) [
                    'id'    => $form['id'],
                    'title' => $form['label'],
                ];
            }
        }
        wp_send_json_success($all_forms);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('ws-form-pro/ws-form.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WS Form Pro'));
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
        $form = wsf_form_get_form_object($form_id, false);
        $fieldDetails = wsf_form_get_fields($form);

        if (empty($fieldDetails)) {
            return $fieldDetails;
        }
        $fields = [];
        foreach ($fieldDetails as $field) {
            if ($field->type !== 'submit') {
                $type = $field->type;
                if ($type === 'signature') {
                    $type = 'file';
                }

                $fields[] = [
                    'name'  => 'field_' . $field->id,
                    'type'  => $type,
                    'label' => $field->label,
                ];
            }
        }

        return $fields;
    }
}
