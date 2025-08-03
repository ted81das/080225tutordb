<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\JetpackCRM\JetpackCRMController;

Hooks::add('zbs_new_company', [JetpackCRMController::class, 'handleCompanyCreated'], 10, 1);
Hooks::add('zbs_delete_company', [JetpackCRMController::class, 'handleCompanyDeleted'], 10, 1);
Hooks::add('zbs_new_customer', [JetpackCRMController::class, 'handleContactCreated'], 10, 1);
Hooks::add('zbs_delete_customer', [JetpackCRMController::class, 'handleContactDeleted'], 10, 1);
Hooks::add('zbs_delete_event', [JetpackCRMController::class, 'handleEventDeleted'], 10, 1);
Hooks::add('zbs_delete_invoice', [JetpackCRMController::class, 'handleInvoiceDeleted'], 10, 1);
Hooks::add('jpcrm_quote_accepted', [JetpackCRMController::class, 'handleQuoteAccepted'], 10, 1);
Hooks::add('zbs_new_quote', [JetpackCRMController::class, 'handleQuoteCreated'], 10, 1);
Hooks::add('zbs_delete_quote', [JetpackCRMController::class, 'handleQuoteDeleted'], 10, 1);
Hooks::add('zbs_delete_transaction', [JetpackCRMController::class, 'handleTransactionDeleted'], 10, 1);
