<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\NinjaTables\NinjaTablesController;

Hooks::add('ninja_table_after_add_item', [NinjaTablesController::class, 'handleNewRowAdded'], 10, 3);
Hooks::add('ninja_table_after_update_item', [NinjaTablesController::class, 'handleRowUpdated'], 10, 3);
Hooks::add('ninja_table_before_items_deleted', [NinjaTablesController::class, 'handleRowDeleted'], 10, 2);
