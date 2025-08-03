<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\BetterMessages\BetterMessagesController;

Hooks::add('better_messages_message_sent', [BetterMessagesController::class, 'handleNewMessageReceived'], 10, 1);
