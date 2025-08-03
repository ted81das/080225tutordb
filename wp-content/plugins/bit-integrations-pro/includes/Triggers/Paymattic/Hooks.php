<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Paymattic\PaymatticController;

Hooks::add('wppayform/after_form_submission_complete', [PaymatticController::class, 'handlePaymentFormCompleted'], 20, 2);
Hooks::add('wppayform/after_payment_status_change', [PaymatticController::class, 'handlePaymentStatusChanged'], 20, 2);
Hooks::add('wppayform/form_payment_success', [PaymatticController::class, 'handlePaymentSuccess'], 20, 4);
Hooks::add('wppayform/form_payment_failed', [PaymatticController::class, 'handlePaymentFailed'], 20, 4);
Hooks::add('wppayform/after_create_note_by_user', [PaymatticController::class, 'handleNoteCreatedByUser'], 20, 1);
