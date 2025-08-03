<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SureFeedback\SureFeedbackController;

Hooks::add('ph_website_pre_rest_update_thread_attribute', [SureFeedbackController::class, 'handleCommentMarkedAsResolved'], 10, 3);
Hooks::add('rest_insert_comment', [SureFeedbackController::class, 'handleNewCommentOnSite'], 10, 3);
