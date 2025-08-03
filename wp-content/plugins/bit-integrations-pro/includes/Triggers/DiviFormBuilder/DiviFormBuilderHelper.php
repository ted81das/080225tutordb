<?php

namespace BitApps\BTCBI_PRO\Triggers\DiviFormBuilder;

use BitApps\BTCBI_PRO\Core\Util\Helper;

class DiviFormBuilderHelper
{
    public static function isPrimaryKeysMatch($postArray, $flowDetails, $formIdField)
    {
        $recordData = self::prepareDataForFlow($postArray, $formIdField);

        foreach ($flowDetails->primaryKey as $primaryKey) {
            $valueFromPath = Helper::extractValueFromPath($recordData, $primaryKey->key, 'DiviFormBuilder');

            if (\is_array($valueFromPath)) {
                $valueFromPath = $valueFromPath['value'];
            }

            if ($primaryKey->value != $valueFromPath) {
                return false;
            }
        }

        return true;
    }

    public static function prepareDataForFlow($postArray, $formIdField)
    {
        $fieldNames = $postArray['field_name'];
        $data = [];

        foreach ($fieldNames as $name) {
            $valueKey = str_replace('de_fb_', '', $name);
            $value = $postArray[$valueKey] ?? '';

            if (\is_array($value)) {
                $value = implode(',', $postArray[$valueKey]);
            } else {
                $explodeValue = explode(',', $value ?? '');
                $attachmentUrls = [];

                if (is_numeric($explodeValue[0])) {
                    foreach ($explodeValue as $item) {
                        $url = wp_get_attachment_url($item);
                        if ($url) {
                            $attachmentUrls[] = $url;
                        }
                    }

                    if (\count($explodeValue) === \count($attachmentUrls)) {
                        $value = $attachmentUrls;
                    }
                }
            }

            $data[$name] = $value;
        }

        return array_merge($data, $formIdField);
    }

    public static function setFields($formId, $postArray)
    {
        $allFields = [
            ['name' => 'id', 'type' => 'text', 'label' => __('Form Id', 'bit-integrations-pro'), 'value' => $formId]
        ];

        $fieldTitles = $postArray['field_title'];
        $fieldName = $postArray['field_name'];

        foreach ($fieldTitles as $key => $title) {
            $valueKey = str_replace('de_fb_', '', $fieldName[$key]);
            $value = $postArray[$valueKey] ?? '';

            if (\is_array($value)) {
                $value = implode(',', $postArray[$valueKey]);
            } else {
                $explodeValue = explode(',', $value ?? '');
                $attachmentUrls = [];

                if (is_numeric($explodeValue[0])) {
                    foreach ($explodeValue as $item) {
                        $url = wp_get_attachment_url($item);
                        if ($url) {
                            $attachmentUrls[] = $url;
                        }
                    }

                    if (\count($explodeValue) === \count($attachmentUrls)) {
                        $value = $attachmentUrls;
                    }
                }
            }

            $allFields[] = [
                'name'  => $fieldName[$key],
                'type'  => 'text',
                'label' => !empty($title) ? $title : $fieldName[$key],
                'value' => $value
            ];
        }

        return $allFields;
    }

    public static function isPluginInstalled()
    {
        return is_plugin_active('divi-form-builder/divi-form-builder.php');
    }
}
