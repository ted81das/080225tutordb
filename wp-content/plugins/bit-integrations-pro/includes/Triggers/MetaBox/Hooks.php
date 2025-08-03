<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\MetaBox\MetaBoxController;

// * METABOX SUBMITTED ACTION HOOK*//
Hooks::add('rwmb_frontend_after_save_post', [MetaBoxController::class, 'handle_metabox_submit'], 10, 1);
// * METABOX SUBMITTED ACTION HOOK*//
