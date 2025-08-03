<?php

/**
 * FluentCRM    Record Api
 */

namespace BitApps\BTCBI_PRO\Actions\FluentCRM;

/**
 * Provide functionality for Record insert, upsert
 */
class FluentCRMProHelper
{
    public static function assignCompany($fieldData, $actions)
    {
        if (empty($actions['company_id'])) {
            return $fieldData;
        }

        $fieldData['company_id'] = $actions['company_id'];

        return $fieldData;
    }
}
