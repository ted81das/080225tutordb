<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\FreshSales\FreshSalesRecordApiHelper;

Hooks::filter('btcbi_freshsales_upsert_record', [FreshSalesRecordApiHelper::class, 'upsertRecord'], 10, 5);
