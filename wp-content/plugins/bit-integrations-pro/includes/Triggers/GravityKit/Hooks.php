<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\GravityKit\GravityKitController;

Hooks::add('gravityview/approve_entries/approved', [GravityKitController::class, 'handleFormEntryApproved'], 10, 1);
Hooks::add('gravityview/approve_entries/disapproved', [GravityKitController::class, 'handleFormEntryRejected'], 10, 1);
