<?php

namespace BitApps\BTCBI_PRO\Actions\Bento;

use BitCode\FI\Core\Util\HttpHelper;

class BentoProHelper
{
    private const BASE_URL = 'https://app.bentonow.com/api/v1/';

    public static function getUserFields($default, $fieldsRequestParams)
    {
        static::checkValidation($fieldsRequestParams);

        $response = static::BentoAPI('fetch/fields', $fieldsRequestParams);
        if (!$response || empty($response->data)) {
            return $default;
        }

        $formatted = array_map(function ($item) {
            return (object) [
                'key'      => $item->attributes->key,
                'label'    => $item->attributes->name,
                'required' => false
            ];
        }, $response->data);

        return array_merge($formatted, $default);
    }

    public static function getEventFields($default)
    {
        return [
            (object) ['key' => 'type', 'label' => 'Title', 'required' => true],
            (object) ['key' => 'email', 'label' => 'Email Address', 'required' => true]
        ];
    }

    public static function getAllTags($default, $fieldsRequestParams)
    {
        static::checkValidation($fieldsRequestParams);

        $response = static::BentoAPI('fetch/tags', $fieldsRequestParams);
        if (!$response || empty($response->data)) {
            return $default;
        }

        foreach ($response->data as $item) {
            $tag = $item->attributes->name ?? null;

            if (!empty($tag)) {
                $default[] = (object) ['value' => $tag, 'label' => $tag];
            }
        }

        return $default;
    }

    public static function updateUserData($default, $fieldsRequestParams, $email, $finalData, $utilities)
    {
        static::checkValidation($fieldsRequestParams, $email);

        $tags = is_array($utilities['tags']) ? $utilities['tags'] : explode(',', $utilities['tags'] ?? '');
        $EventTags = is_array($utilities['EventTags']) ? $utilities['EventTags'] : explode(',', $utilities['EventTags'] ?? '');

        $commands = [
            (object) [
                'command' => $utilities['subscribe'] ? 'subscribe' : 'unsubscribe',
                'email'   => $email,
            ]
        ];

        foreach ($finalData as $key => $value) {
            $commands[] = (object) [
                'command' => 'add_field',
                'email'   => $email,
                'query'   => [
                    'key'   => $key,
                    'value' => $value
                ]
            ];
        }
        foreach ($tags as $tag) {
            $commands[] = (object) [
                'command' => 'add_tag',
                'email'   => $email,
                'query'   => $tag
            ];
        }
        foreach ($EventTags as $tag) {
            $commands[] = (object) [
                'command' => 'add_tag_via_event',
                'email'   => $email,
                'query'   => $tag
            ];
        }

        static::BentoAPI('fetch/commands', $fieldsRequestParams, wp_json_encode(['command' => $commands]), 'post');
    }

    public static function storeEvent($default, $fieldsRequestParams, $finalData)
    {
        static::checkValidation($fieldsRequestParams);

        $email = $finalData['email'];
        $type = $finalData['type'];
        unset($finalData['email'],$finalData['type']);

        $event = [
            'events' => [
                (object) [
                    'type'   => '$' . $type,
                    'email'  => $email,
                    'fields' => (object) $finalData
                ]
            ]
        ];

        return static::BentoAPI('batch/events', $fieldsRequestParams, wp_json_encode($event), 'post');
    }

    private static function setHeaders($publishableKey, $secretKey)
    {
        return [
            'Authorization' => 'Basic ' . base64_encode("{$publishableKey}:{$secretKey}"),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json'
        ];
    }

    private static function checkValidation($fieldsRequestParams, $customParam = '**')
    {
        if (empty($fieldsRequestParams->publishable_key) || empty($fieldsRequestParams->secret_key || empty($fieldsRequestParams->site_uuid)) || empty($customParam)) {
            wp_send_json_error(__('Requested parameter is empty', 'bit-integrations'), 400);
        }
    }

    private static function BentoAPI($type, $params, $data = null, $method = 'get')
    {
        $endpoint = static::BASE_URL . "{$type}?site_uuid={$params->site_uuid}";
        $headers = static::setHeaders($params->publishable_key, $params->secret_key);

        $response = HttpHelper::$method($endpoint, $data, $headers);

        return (substr(HttpHelper::$responseCode, 0, 2) == 20) ? $response : null;
    }
}
