<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\ZohoBigin\ZohoBiginHelperPro;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_zbigin_get_tags', [ZohoBiginHelperPro::class, 'getTagList'], 10, 5);
Hooks::filter('btcbi_zbigin_add_tags_to_records', [ZohoBiginHelperPro::class, 'addTagsToRecords'], 10, 5);
