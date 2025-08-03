<?php

/**
 * MailPoet    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\MailPoet;

/**
 * Provide functionality for Record insert, upsert
 */
class MailPoetProHelper
{
    public static function updateRecord($id, $subscriber)
    {
        try {
            $subscriber = \MailPoet\API\API::MP('v1')->updateSubscriber($id, $subscriber);

            return [
                'success' => true,
                'id'      => $subscriber['id'],
            ];
        } catch (\MailPoet\API\MP\v1\APIException $e) {
            return [
                'success' => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }
}
