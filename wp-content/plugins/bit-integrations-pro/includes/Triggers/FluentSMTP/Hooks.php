<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\FluentSMTP\FluentSMTPController;

Hooks::add('fluentmail_email_sending_failed_no_fallback', [FluentSMTPController::class, 'handleErrorInEmailDelivery'], 10, 3);
Hooks::add('fluentmail_email_sending_failed', [FluentSMTPController::class, 'handleErrorInEmailDelivery'], 10, 3);
Hooks::add('wp_mail_succeeded', [FluentSMTPController::class, 'handleEmailSucceeded'], 10, 1);
