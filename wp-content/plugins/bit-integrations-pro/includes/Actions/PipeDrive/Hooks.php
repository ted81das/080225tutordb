<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\PipeDrive\PipeDriveHelperPro;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::add('btcbi_pipedrive_store_related_list', [PipeDriveHelperPro::class, 'addRelatedList'], 10, 6);
