<?php

/**
 * Bento Record Api
 */

namespace BitCode\FI\Actions\Bento;

use BitCode\FI\Core\Util\Common;
use BitCode\FI\Core\Util\HttpHelper;
use BitCode\FI\Log\LogHandler;

/**
 * Provide functionality for Record insert, upsert
 */
class RecordApiHelper
{
    private $integrationDetails;

    private $integrationId;

    private $defaultHeader;

    private $siteUUID;

    private $publishableKey;

    private $secretKey;

    public function __construct(
        $integrationDetails,
        $integId,
        $publishableKey,
        $secretKey,
        $siteUUID
    ) {
        $this->integrationDetails = $integrationDetails;
        $this->integrationId = $integId;
        $this->siteUUID = $siteUUID;
        $this->publishableKey = $publishableKey;
        $this->secretKey = $secretKey;
        $this->defaultHeader = BentoHelper::setHeaders($publishableKey, $secretKey);
    }

    public function addPeople($finalData)
    {
        if (empty($finalData['email'])) {
            return ['success' => false, 'message' => __('Required field Email is empty', 'bit-integrations'), 'code' => 400];
        }

        $email = $finalData['email'];
        unset($finalData['email']);

        $apiEndpoint = BentoHelper::setEndpoint('subscribers', $this->siteUUID);
        $response = HttpHelper::post($apiEndpoint, wp_json_encode(['email' => $email]), $this->defaultHeader);

        if (!BentoHelper::checkResponseCode()) {
            return $response;
        }

        $integration = $this->integrationDetails;
        $utilities = [
            'tags'      => $integration->selected_tags ?? [],
            'EventTags' => $integration->selected_tags_via_event ?? [],
            'subscribe' => $integration->subscribe ?? false,
        ];

        $reqParams = BentoHelper::setReqParams($this->siteUUID, $this->publishableKey, $this->secretKey);

        do_action('btcbi_bento_update_user_data', false, $reqParams, $email, $finalData, $utilities);

        return $response;
    }

    public function addEvent($finalData)
    {
        if (empty($finalData['email']) || empty($finalData['type'])) {
            return ['success' => false, 'message' => __('Required field Email is empty', 'bit-integrations'), 'code' => 400];
        }

        $reqParams = BentoHelper::setReqParams($this->siteUUID, $this->publishableKey, $this->secretKey);

        $response = apply_filters('btcbi_bento_store_event', false, $reqParams, $finalData);

        return empty($response) ? (object) ['error' => wp_sprintf(__('%s plugin is not installed or activate', 'bit-integrations'), 'Bit Integration Pro')] : $response;
    }

    public function generateReqDataFromFieldMap($data, $fieldMap)
    {
        $dataFinal = [];
        foreach ($fieldMap as $value) {
            $triggerValue = ($value->formField === 'custom' && !empty($value->customValue)) ? Common::replaceFieldWithValue($value->customValue, $data) : $data[$value->formField];
            $actionValue = $value->bentoFormField === 'customFieldKey' && !empty($value->customFieldKey) ? $value->customFieldKey : $value->bentoFormField;

            $dataFinal[$actionValue] = $triggerValue;
        }

        return $dataFinal;
    }

    public function execute($fieldValues, $fieldMap, $action)
    {
        $finalData = $this->generateReqDataFromFieldMap($fieldValues, $fieldMap);

        switch ($action) {
            case 'add_people':
                $type = 'People';
                $typeName = 'Add People';
                $apiResponse = $this->addPeople($finalData);
                $logStatus = (!BentoHelper::checkResponseCode() || empty($apiResponse->data)) ? 'error' : 'success';

                break;
            case 'add_event':
                $type = 'Event';
                $typeName = 'Add Event';
                $apiResponse = $this->addEvent($finalData);
                $logStatus = (!BentoHelper::checkResponseCode() || empty($apiResponse->results)) ? 'error' : 'success';

                break;

            default:
                break;
        }

        LogHandler::save($this->integrationId, wp_json_encode(['type' => $type, 'type_name' => $typeName]), $logStatus, wp_json_encode($apiResponse));

        return $apiResponse;
    }
}
