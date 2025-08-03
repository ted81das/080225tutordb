<?php

namespace BitApps\BTCBI_PRO\Actions\WhatsApp;

use BitApps\BTCBI_PRO\Core\Util\Common;
use BitCode\FI\Core\Util\HttpHelper;
use CURLFile;

class WhatsAppHelperPro
{
    public static function sendTextMessages($body, $fieldValues, $numberId, $token, $phoneNumber)
    {
        if (empty($body) || empty($fieldValues) || empty($numberId) || empty($token) || empty($phoneNumber)) {
            return (object) ['error' => 'Required params are empty!'];
        }

        $msg = Common::replaceFieldWithValue($body, $fieldValues);
        $messagesBody = str_replace(['<p>', '</p>'], ' ', $msg);

        $apiEndPoint = "https://graph.facebook.com/v20.0/{$numberId}/messages";
        $sanitizingSpace = rtrim($messagesBody, '&nbsp; ');
        $planMessage = strip_tags($sanitizingSpace);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => "{$phoneNumber}",
            'type'              => 'text',
            'text'              => ['body' => $planMessage]
        ];

        return HttpHelper::post($apiEndPoint, $data, static::setHeaders($token));
    }

    public static function sendMediaMessages($integrationDetails, $fieldValues, $numberId, $token, $phoneNumber)
    {
        if (empty($integrationDetails) || empty($integrationDetails->mediaType) || empty($integrationDetails->upload_field) || empty($fieldValues) || empty($numberId) || empty($token) || empty($phoneNumber)) {
            return (object) ['error' => 'Required params are empty!'];
        }

        $mediaType = $integrationDetails->mediaType;
        $media = static::upload_file_to_whatsapp($numberId, $token, $fieldValues[$integrationDetails->upload_field], $mediaType);

        if (!isset($media->id) || isset($media->error) || is_wp_error($media)) {
            return $media;
        }

        $dataFinal = isset($integrationDetails->media_field_map)
            ? static::generateReqDataFromFieldMap($fieldValues, $integrationDetails->media_field_map)
            : [];

        $apiEndPoint = "https://graph.facebook.com/v20.0/{$numberId}/messages";
        $type = explode('/', $mediaType);
        $type = \in_array($type[0], ['text', 'application']) ? 'document' : $type[0];

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => "{$phoneNumber}",
            'type'              => $type,
            $type               => array_merge($dataFinal, ['id' => $media->id])
        ];

        return HttpHelper::post($apiEndPoint, $data, static::setHeaders($token));
    }

    public static function sendContactMessages($integrationDetails, $fieldValues, $numberId, $token, $phoneNumber)
    {
        if (empty($integrationDetails) || empty($integrationDetails->contact_field_map) || empty($fieldValues) || empty($numberId) || empty($token) || empty($phoneNumber)) {
            return (object) ['error' => 'Required params are empty!'];
        }

        $dataFinal = static::generateContactFieldMap($fieldValues, $integrationDetails->contact_field_map);
        $apiEndPoint = "https://graph.facebook.com/v20.0/{$numberId}/messages";
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => "{$phoneNumber}",
            'type'              => 'contacts',
            'contacts'          => [$dataFinal]
        ];

        return HttpHelper::post($apiEndPoint, $data, static::setHeaders($token));
    }

    private static function upload_file_to_whatsapp($numberId, $token, $file, $file_type = 'image/jpeg')
    {
        $apiEndPoint = "https://graph.facebook.com/v20.0/{$numberId}/media";
        $file_path = static::extractTheFile($file);

        $data = [
            'file'              => new CURLFile(Common::filePath($file_path), $file_type, basename($file_path)),
            'type'              => 'image/png',
            'messaging_product' => 'whatsapp'
        ];

        return HttpHelper::post($apiEndPoint, $data, static::setHeaders($token, 'multipart/form-data'));
    }

    private static function extractTheFile($files)
    {
        if (\is_array($files)) {
            foreach ($files as $file) {
                if (\is_array($file)) {
                    return static::extractTheFile($file);
                }

                return $file;
            }
        } else {
            return $files;
        }
    }

    private static function generateReqDataFromFieldMap($data, $fieldMap)
    {
        $dataFinal = [];

        foreach ($fieldMap as $key => $value) {
            $triggerValue = $value->formField;
            $actionValue = $value->whatsAppFormField;
            if ($triggerValue === 'custom') {
                $dataFinal[$actionValue] = Common::replaceFieldWithValue($value->customValue, $data);
            } elseif (!\is_null($data[$triggerValue])) {
                $dataFinal[$actionValue] = $data[$triggerValue];
            }
        }

        return $dataFinal;
    }

    private static function generateContactFieldMap($data, $fieldMap)
    {
        $dataFinal = [];
        $fieldValues = static::generateReqDataFromFieldMap($data, $fieldMap);
        $dataFinal['name'] = array_filter([
            'prefix'      => $fieldValues['prefix'] ?? '',
            'first_name'  => $fieldValues['first_name'] ?? '',
            'middle_name' => $fieldValues['middle_name'] ?? '',
            'last_name'   => $fieldValues['last_name'] ?? '',
            'suffix'      => $fieldValues['suffix'] ?? ''
        ]);

        $dataFinal['name']['formatted_name'] = trim(implode(' ', $dataFinal['name']));
        $addressTypes = ['HOME', 'WORK'];

        foreach ($addressTypes as $type) {
            $address = [
                'street'       => $fieldValues["{$type}_street"] ?? '',
                'city'         => $fieldValues["{$type}_city"] ?? '',
                'state'        => $fieldValues["{$type}_state"] ?? '',
                'zip'          => $fieldValues["{$type}_zip"] ?? '',
                'country'      => $fieldValues["{$type}_country"] ?? '',
                'country_code' => $fieldValues["{$type}_country_code"] ?? ''
            ];

            if (array_filter($address)) {
                $address['type'] = $type;
                $dataFinal['addresses'][] = array_filter($address);
            }
        }

        foreach ($fieldValues as $key => $value) {
            if (!empty($value)) {
                if (strpos($key, '_email') !== false) {
                    $type = explode('_', $key)[0];

                    $dataFinal['emails'][] = [
                        'email' => $value,
                        'type'  => $type
                    ];
                } elseif (strpos($key, '_phone') !== false) {
                    $type = explode('_', $key)[0];

                    $dataFinal['phones'][] = [
                        'phone' => $value,
                        'type'  => $type
                    ];
                } elseif (\in_array($key, ['company', 'department', 'title'])) {
                    $dataFinal['org'][$key] = $value;
                } elseif ($key == 'birthday') {
                    $dataFinal[$key] = $value;
                }
            }
        }

        return $dataFinal;
    }

    private static function setHeaders($token, $contentType = 'application/json')
    {
        return
            [
                'Authorization' => "Bearer {$token}",
                'Content-Type'  => $contentType,
            ];
    }
}
