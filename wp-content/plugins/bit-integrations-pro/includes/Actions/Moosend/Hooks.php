<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\Moosend\MoosendProHelper;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_moosend_map_custom_fields', [MoosendProHelper::class, 'mapCustomFields'], 10, 3);
