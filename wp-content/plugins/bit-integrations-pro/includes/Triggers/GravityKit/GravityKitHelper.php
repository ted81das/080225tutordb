<?php

namespace BitApps\BTCBI_PRO\Triggers\GravityKit;

use GFCommon;
use GFFormsModel;
use BitCode\FI\Core\Util\Helper;

class GravityKitHelper
{
    public static function formatFormEntryData($entry_id)
    {
        $form_id = static::getFormId($entry_id);

        if (!$form_id) {
            return [];
        }

        $form = GFFormsModel::get_form_meta($form_id);
        $fields = static::getFormFields($form);

        if (empty($fields)) {
            return [];
        }

        $data = [
            'form_id'  => (int) $form_id,
            'entry_id' => (int) $entry_id,
        ];

        $formdata = static::getFormData($entry_id, $fields);
        foreach ($fields as $meta_key => $label) {
            $data[$label] = $formdata[$meta_key]->meta_value ?? '';
        }

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return (bool) (class_exists('GravityView_Plugin') && class_exists('GFFormsModel'));
    }

    private static function getFormId($entry_id)
    {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT form_id FROM {$wpdb->prefix}gf_entry WHERE id = %d",
            $entry_id
        ));
    }

    private static function getFormData($entry_id, $fields)
    {
        global $wpdb;

        $meta_keys = array_keys($fields);
        $placeholders = implode(',', array_fill(0, \count($meta_keys), '%s'));

        $query = $wpdb->prepare(
            "SELECT meta_key, meta_value FROM {$wpdb->prefix}gf_entry_meta WHERE entry_id = %d AND meta_key IN ({$placeholders})",
            array_merge([$entry_id], $meta_keys)
        );

        return $wpdb->get_results($query, OBJECT_K);
    }

    private static function getFormFields($form)
    {
        if (empty($form['fields']) || !\is_array($form['fields'])) {
            return [];
        }

        $fields = [];
        foreach ($form['fields'] as $field) {
            if (empty($field)) {
                continue;
            }

            if (!empty($field['inputs']) && \is_array($field['inputs'])) {
                foreach ($field['inputs'] as $input) {
                    $fields[$input['id']] = GFCommon::get_label($field, $input['id']);
                }
            } elseif (empty($field['displayOnly'])) {
                $fields[$field['id']] = GFCommon::get_label($field);
            }
        }

        return $fields;
    }
}
