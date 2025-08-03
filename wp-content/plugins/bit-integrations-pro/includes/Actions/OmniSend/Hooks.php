<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\OmniSend\OmniSendHelperPro;

Hooks::filter('btcbi_omnisend_custom_properties', [OmniSendHelperPro::class, 'setCustomProperties'], 10, 3);
