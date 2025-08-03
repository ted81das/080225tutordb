<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SureMail\SureMailController;

Hooks::add('wp_mail_failed', [SureMailController::class, 'handleMailFailedToSend'], 10, 1);
Hooks::add('wp_mail_succeeded', [SureMailController::class, 'handleMailSent'], 10, 1);
