<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\Klaviyo\KlaviyoHelperPro;

Hooks::filter('btcbi_klaviyo_custom_properties', [KlaviyoHelperPro::class, 'setCustomProperties'], 10, 3);
Hooks::filter('btcbi_klaviyo_update_profile', [KlaviyoHelperPro::class, 'updateProfile'], 10, 4);
