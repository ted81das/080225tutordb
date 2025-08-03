<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\FluentSupport\FluentSupportHelperPro;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::add('btcbi_fluent_support_upload_ticket_attachments', [FluentSupportHelperPro::class, 'uploadTicketAttachments'], 10, 5);
