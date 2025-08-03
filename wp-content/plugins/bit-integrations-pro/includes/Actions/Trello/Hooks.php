<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\Trello\TrelloHelperPro;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_trello_get_all_custom_fields', [TrelloHelperPro::class, 'getAllCustomFields'], 10, 4);
Hooks::add('btcbi_trello_store_custom_fields', [TrelloHelperPro::class, 'storeCustomFields'], 10, 5);
