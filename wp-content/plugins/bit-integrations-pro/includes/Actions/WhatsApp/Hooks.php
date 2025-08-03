<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\WhatsApp\WhatsAppHelperPro;

Hooks::filter('btcbi_whatsapp_send_text_messages', [WhatsAppHelperPro::class, 'sendTextMessages'], 10, 5);
Hooks::filter('btcbi_whatsapp_send_media_messages', [WhatsAppHelperPro::class, 'sendMediaMessages'], 10, 5);
Hooks::filter('btcbi_whatsapp_send_contact_messages', [WhatsAppHelperPro::class, 'sendContactMessages'], 10, 5);
