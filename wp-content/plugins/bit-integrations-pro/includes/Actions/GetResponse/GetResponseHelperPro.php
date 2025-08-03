<?php

/**
 * GetResponse    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\GetResponse;

/**
 * Provide functionality for Record insert, upsert
 */
class GetResponseHelperPro
{
    public static function autoResponderDay($requestParams, $dayOfCycle)
    {
        $requestParams['dayOfCycle'] = $dayOfCycle;

        return $requestParams;
    }
}
