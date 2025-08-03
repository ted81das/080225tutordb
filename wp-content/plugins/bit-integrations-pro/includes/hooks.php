<?php

// If try to direct access  plugin folder it will Exit

use BitApps\BTCBI_PRO\Core\Util\Activation;
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

if (!defined('ABSPATH')) {
    exit;
}

// Hooks::add('wp_insert_site', [Activation::class, 'handle_new_site'], 10, 1);
Hooks::add('wp_initialize_site', [Activation::class, 'handle_new_site'], 200, 1);

Hooks::filter('bit_integrations_triggers', [TriggerController::class, 'triggerList']);
Hooks::filter('bit_integrations_trigger_fields', [TriggerController::class, 'getTriggerField'], 10, 2);
