<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\Dokan\DokanRecordHelper;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_dokan_vendor_crud_actions', [DokanRecordHelper::class, 'vendorCreateActions'], 10, 2);
