<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SureCart\SureCartController;

Hooks::add('surecart/purchase_created', [SureCartController::class, 'surecart_purchase_product'], 10, 1);
Hooks::add('surecart/purchase_revoked', [SureCartController::class, 'surecart_purchase_revoked'], 10, 1);
Hooks::add('surecart/purchase_invoked', [SureCartController::class, 'surecart_purchase_unrevoked'], 10, 1);
