<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\BbPress\BbPressController;

Hooks::add('bbp_new_reply', [BbPressController::class, 'handleReplyToTopic'], 10, 3);
Hooks::add('bbp_new_topic', [BbPressController::class, 'handleTopicCreated'], 10, 4);
