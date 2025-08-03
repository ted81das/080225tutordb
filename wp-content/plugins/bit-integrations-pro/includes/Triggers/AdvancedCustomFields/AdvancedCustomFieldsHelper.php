<?php

namespace BitApps\BTCBI_PRO\Triggers\AdvancedCustomFields;

use BitCode\FI\Core\Util\Post;
use BitCode\FI\Core\Util\Helper;

class AdvancedCustomFieldsHelper
{
    public static function formatOptionPageACFData($post_id)
    {
        $field_key = 'all';
        $get_field = get_fields($post_id);
        $acf_value = empty($get_field[$field_key]) ? $get_field : $get_field[$field_key];

        if (!\is_array($acf_value)) {
            return Helper::prepareFetchFormatFields(['field_key' => $field_key, $field_key => $acf_value]);
        }

        if (!empty($acf_value[0]) && \is_array($acf_value[0])) {
            $data = [
                'field_key' => $field_key,
                $field_key  => wp_json_encode($acf_value)
            ];
        } else {
            $data = array_map(function ($value) {
                return \is_array($value) ? wp_json_encode($value) : $value;
            }, $acf_value);

            $data['field_key'] = key($acf_value);
        }

        return Helper::prepareFetchFormatFields($data);
    }

    public static function formatPostACFData($post_id, $meta_key, $meta_value)
    {
        $data = [
            'wp_post'      => $post_id,
            'wp_post_type' => get_post_type($post_id),
            'post'         => Post::get($post_id),
            'field_key'    => $meta_key,
            $meta_key      => $meta_value,
        ];

        if (!\function_exists('get_fields')) {
            return Helper::prepareFetchFormatFields($data);
        }

        $fields = get_fields($post_id);

        if (empty($fields) || !\is_array($fields) || !\array_key_exists($meta_key, $fields)) {
            return;
        }

        $data['post_fields'] = $fields;

        return Helper::prepareFetchFormatFields($data);
    }

    public static function isPluginInstalled()
    {
        return class_exists('ACF');
    }
}
