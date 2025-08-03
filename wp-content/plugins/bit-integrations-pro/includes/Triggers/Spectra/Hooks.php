<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Spectra\SpectraController;

Hooks::add('uagb_form_success', [SpectraController::class, 'spectraHandler'], 10, PHP_INT_MAX);
