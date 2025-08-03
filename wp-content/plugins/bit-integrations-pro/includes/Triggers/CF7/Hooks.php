<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\CF7\CF7HelperPro;

Hooks::filter('btcbi_cf7_get_advance_custom_html_fields', [CF7HelperPro::class, 'getAdvanceCustomHtmlFields'], 10, 1);
