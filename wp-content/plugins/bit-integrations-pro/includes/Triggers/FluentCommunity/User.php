<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCommunity;

use BitCode\FI\Core\Util\Helper;

final class User
{
    public static function handleUserLeveledUp($xprofile, $new_level, $old_level)
    {
        if (empty($xprofile) || empty($new_level) || empty($old_level)) {
            return;
        }

        $formData = FluentCommunityHelper::formatUserLaveledUpData($xprofile, $new_level, $old_level);

        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_fluent_community/user_level_upgraded_test', array_values($formData));

        return FluentCommunityController::flowExecute('fluent_community/user_level_upgraded', $formData);
    }
}
