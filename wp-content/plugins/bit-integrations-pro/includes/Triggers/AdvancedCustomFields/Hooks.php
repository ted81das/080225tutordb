<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\AdvancedCustomFields\AdvancedCustomFieldsController;

Hooks::add('acf/save_post', [AdvancedCustomFieldsController::class, 'handleFieldUpdatedOnOptionsPage'], 10, 1);
Hooks::add('updated_post_meta', [AdvancedCustomFieldsController::class, 'handleFieldUpdatedOnPost'], 99, 4);
Hooks::add('updated_user_meta', [AdvancedCustomFieldsController::class, 'handleFieldUpdatedOnUserProfile'], 10, 4);
Hooks::add('added_user_meta', [AdvancedCustomFieldsController::class, 'handleFieldUpdatedOnUserProfile'], 10, 4);
