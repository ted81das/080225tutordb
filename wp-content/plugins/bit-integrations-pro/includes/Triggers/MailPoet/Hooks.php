<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\MailPoet\MailPoetController;

Hooks::add('mailpoet_subscription_before_subscribe', [MailPoetController::class, 'handle_mailpoet_submit'], 10, 3);
