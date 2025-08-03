<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\SendPulse\SendPulseHelperPro;

Hooks::add('btcbi_sendPulse_refresh_fields', [SendPulseHelperPro::class, 'refreshFields'], 10, 3);
