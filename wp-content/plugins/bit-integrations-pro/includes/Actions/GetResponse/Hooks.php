<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\GetResponse\GetResponseHelperPro;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_getresponse_autoresponder_day', [GetResponseHelperPro::class, 'autoResponderDay'], 10, 2);
