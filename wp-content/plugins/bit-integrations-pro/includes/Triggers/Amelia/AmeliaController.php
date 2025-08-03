<?php

namespace BitApps\BTCBI_PRO\Triggers\Amelia;

use BitApps\BTCBI_PRO\Triggers\Webhook\WebhookController;

final class AmeliaController extends WebhookController
{
    public static function info()
    {
        return [
            'name'      => 'Amelia Webhook',
            'title'     => __('Get callback data through an URL', 'bit-integrations-pro'),
            'type'      => 'webhook',
            'is_active' => true,
            'isPro'     => true
        ];
    }
}
