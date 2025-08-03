<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitCode\FI\Core\Util\StoreInCache;
use BitApps\BTCBI_PRO\Triggers\ActionHook\ActionHookController;

$hooks = get_option('btcbi_action_hook_test_data');

if (!empty($hooks)) {
    foreach ($hooks as $key => $value) {
        Hooks::add($key, [ActionHookController::class, 'actionHookHandler'], 10, PHP_INT_MAX);
    }
}

if (class_exists(\BitCode\FI\Core\Util\StoreInCache::class) && method_exists(\BitCode\FI\Core\Util\StoreInCache::class, 'getActionHookFlows')) {
    $flows = StoreInCache::getActionHookFlows() ?? [];
} else {
    $flows = [];
}

foreach ($flows as $flow) {
    if (isset($flow->triggered_entity_id)) {
        Hooks::add($flow->triggered_entity_id, [ActionHookController::class, 'handle'], 10, PHP_INT_MAX);
    }
}
