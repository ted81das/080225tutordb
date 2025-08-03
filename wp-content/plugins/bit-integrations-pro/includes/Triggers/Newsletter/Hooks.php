<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Newsletter\NewsletterController;

Hooks::add('newsletter_user_post_subscribe', [NewsletterController::class, 'handleSubscriptionFormSubmittedWithSpecificList'], 20, 1);
