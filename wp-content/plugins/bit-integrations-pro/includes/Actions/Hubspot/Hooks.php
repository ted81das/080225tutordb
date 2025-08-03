<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\Hubspot\HubspotHelperPro;

Hooks::filter('btcbi_hubspot_update_entity', [HubspotHelperPro::class, 'updateEntity'], 10, 4);
