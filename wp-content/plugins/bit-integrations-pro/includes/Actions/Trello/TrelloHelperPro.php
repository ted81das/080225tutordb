<?php

namespace BitApps\BTCBI_PRO\Actions\Trello;

use BitCode\FI\Log\LogHandler;
use BitCode\FI\Core\Util\Common;
use BitCode\FI\Core\Util\HttpHelper;

class TrelloHelperPro
{
    public static function getAllCustomFields($data, $boardId, $clientId, $accessToken)
    {
        $apiEndpoint = "https://api.trello.com/1/boards/{$boardId}/customFields?key={$clientId}&token={$accessToken}";
        $response = HttpHelper::get($apiEndpoint, null);

        if (is_wp_error($response) || !empty($response->response->error)) {
            wp_send_json_error(
                $response->response->error->message,
                400
            );
        }

        $allFields = [];
        foreach ($response as $field) {
            $allFields[] = (object) [
                'key'      => $field->id,
                'label'    => $field->name,
                'type'     => $field->type,
                'options'  => empty($field->options) ? [] : $field->options,
                'required' => false
            ];
        }

        uksort($allFields, 'strnatcasecmp');

        return $allFields;
    }

    public static function storeCustomFields($cardId, $customFieldMap, $fieldValues, $integrationID, $integrationDetails)
    {
        $insertRecordEndpoint = "https://api.trello.com/1/cards/{$cardId}/customFields?idList={$integrationDetails->listId}&key={$integrationDetails->clientId}&token={$integrationDetails->accessToken}";
        $finalData = static::generateReqDataFromFieldMap($fieldValues, $customFieldMap);
        $apiResponse = HttpHelper::put($insertRecordEndpoint, $finalData);

        if (HttpHelper::$responseCode != 200 || (\is_object($apiResponse) && property_exists($apiResponse, 'errors'))) {
            LogHandler::save($integrationID, wp_json_encode(['type' => 'Card', 'type_name' => 'add-Card-custom-fields']), 'error', wp_json_encode($apiResponse));
        } else {
            LogHandler::save($integrationID, wp_json_encode(['type' => 'Card', 'type_name' => 'add-Card-custom-fields']), 'success', __('Custom Fields added successfully', 'bit-integrations-pro'));
        }
    }

    private static function generateReqDataFromFieldMap($data, $fieldMap)
    {
        $dataFinal = [];

        foreach ($fieldMap as $value) {
            $triggerValue = static::getTriggerValue($value, $data);
            $actionValue = $value->trelloFormField;
            $key = $value->type ?? null;
            $options = $value->options ?? null;

            if (\in_array($key, ['checkbox', 'number', 'text', 'date'])) {
                if ($key === 'checkbox') {
                    $key = 'checked';
                    $triggerValue = (!$triggerValue || $triggerValue == 'false') ? 'false' : 'true';
                }

                $dataFinal[] = static::formatCustomField($actionValue, $key, $triggerValue);
            } elseif ($key === 'list' && \is_array($options)) {
                $matchedOption = static::findMatchingOption($options, $triggerValue);

                if ($matchedOption) {
                    $optionKey = key((array) $matchedOption->value);
                    $dataFinal[] = static::formatListField($actionValue, $matchedOption, $optionKey, $triggerValue);
                }
            }
        }

        return ['customFieldItems' => $dataFinal];
    }

    private static function getTriggerValue($value, $data)
    {
        return $value->formField === 'custom'
            ? Common::replaceFieldWithValue($value->customValue, $data)
            : ($data[$value->formField] ?? null);
    }

    private static function findMatchingOption(array $options, $triggerValue)
    {
        $option = array_filter($options, function ($option) use ($triggerValue) {
            if (isset($option->value)) {
                return $option->value->{key((array) $option->value)} == $triggerValue;
            }
        });

        return array_pop($option);
    }

    private static function formatCustomField($actionValue, $key, $triggerValue)
    {
        return (object) [
            'idCustomField' => $actionValue,
            'value'         => (object) [$key => sanitize_text_field($triggerValue)],
        ];
    }

    private static function formatListField($actionValue, $option, $key, $triggerValue)
    {
        return (object) [
            'idCustomField' => $actionValue,
            'idValue'       => $option->id ?? '',
            'value'         => (object) [$key => sanitize_text_field($triggerValue)],
        ];
    }
}
