<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\Bento\BentoProHelper;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_bento_get_user_fields', [BentoProHelper::class, 'getUserFields'], 10, 2);
Hooks::filter('btcbi_bento_get_event_fields', [BentoProHelper::class, 'getEventFields'], 10, 1);
Hooks::filter('btcbi_bento_get_all_tags', [BentoProHelper::class, 'getAllTags'], 10, 2);
Hooks::filter('btcbi_bento_store_event', [BentoProHelper::class, 'storeEvent'], 10, 3);
Hooks::add('btcbi_bento_update_user_data', [BentoProHelper::class, 'updateUserData'], 10, 5);
