<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Mailster\MailsterController;

Hooks::add('mailster_add_subscriber', [MailsterController::class, 'handleMailsterSubmit'], 10, 1);
