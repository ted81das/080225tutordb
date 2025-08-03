<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\FunnelKitAutomations\FunnelKitAutomationsController;

Hooks::add('bwfan_contact_added_to_lists', [FunnelKitAutomationsController::class, 'handleContactAddedToList'], 10, 2);
Hooks::add('bwfan_contact_removed_from_lists', [FunnelKitAutomationsController::class, 'handleContactRemovedFromList'], 10, 2);
Hooks::add('bwfan_tags_added_to_contact', [FunnelKitAutomationsController::class, 'handleTagAddedToContact'], 10, 2);
Hooks::add('bwfan_tags_removed_from_contact', [FunnelKitAutomationsController::class, 'handleTagRemovedFromContact'], 10, 2);
