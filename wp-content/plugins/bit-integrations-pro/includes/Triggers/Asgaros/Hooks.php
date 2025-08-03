<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Asgaros\AsgarosController;

Hooks::add('asgarosforum_after_add_topic_submit', [AsgarosController::class, 'handleUserCreatesNewTopicInForum'], 5, 6);
Hooks::add('asgarosforum_after_add_post_submit', [AsgarosController::class, 'handleUserRepliesToTopicInForum'], 5, 6);
