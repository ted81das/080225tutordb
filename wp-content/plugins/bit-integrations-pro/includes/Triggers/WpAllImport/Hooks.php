<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WpAllImport\WpAllImportController;

Hooks::add('pmxi_after_xml_import', [WpAllImportController::class, 'handleImportCompleted'], 10, 2);
Hooks::add('pmxi_after_xml_import', [WpAllImportController::class, 'handleImportFailed'], 10, 2);
Hooks::add('pmxi_saved_post', [WpAllImportController::class, 'handlePostTypeImported'], 10, 3);
