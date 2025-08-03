<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\MailPoet\MailPoetProHelper;

Hooks::filter('btcbi_mailpoet_update_subscriber', [MailPoetProHelper::class, 'updateRecord'], 10, 2);
