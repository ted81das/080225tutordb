<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\LMFWC\LMFWCProHelper;

Hooks::add('btcbi_lmfwc_update_licence', [LMFWCProHelper::class, 'updateLicense'], 10, 5);
Hooks::add('btcbi_lmfwc_activate_licence', [LMFWCProHelper::class, 'activateLicense'], 10, 4);
Hooks::add('btcbi_lmfwc_deactivate_licence', [LMFWCProHelper::class, 'deactivateLicense'], 10, 5);
Hooks::add('btcbi_lmfwc_reactivate_licence', [LMFWCProHelper::class, 'reactivateLicense'], 10, 5);
Hooks::add('btcbi_lmfwc_delete_licence', [LMFWCProHelper::class, 'deleteLicense'], 10, 4);
Hooks::add('btcbi_lmfwc_create_generator', [LMFWCProHelper::class, 'createGenerator'], 10, 4);
Hooks::add('btcbi_lmfwc_update_generator', [LMFWCProHelper::class, 'UpdateGenerator'], 10, 5);
