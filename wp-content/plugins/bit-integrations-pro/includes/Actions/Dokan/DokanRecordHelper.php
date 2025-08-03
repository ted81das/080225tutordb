<?php

namespace BitApps\BTCBI_PRO\Actions\Dokan;

class DokanRecordHelper
{
    public static function vendorCreateActions($module, $actions)
    {
        if (empty($actions)) {
            return false;
        }

        $actionsData = [];

        if (isset($actions['notifyVendor'])) {
            $actionsData['notify_vendor'] = true;
        } else {
            $actionsData['notify_vendor'] = false;
        }

        if (isset($actions['enableSelling'])) {
            $actionsData['enabled'] = true;
        } else {
            $actionsData['enabled'] = false;
        }

        if (isset($actions['publishProduct'])) {
            $actionsData['trusted'] = true;
        } else {
            $actionsData['trusted'] = false;
        }

        if (isset($actions['featureVendor'])) {
            $actionsData['featured'] = true;
        } else {
            $actionsData['featured'] = false;
        }

        return $actionsData;
    }
}
