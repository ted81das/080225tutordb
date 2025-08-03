<?php

namespace BitApps\BTCBI_PRO\Triggers\BitAssist;

use BitApps\BTCBI_PRO\Triggers\Webhook\WebhookController;

final class BitAssistController extends WebhookController
{
    public static function info()
    {
        return [
            'name'      => 'Bit Assist',
            'title'     => __('Get callback data through an URL', 'bit-integrations-pro'),
            'type'      => 'webhook',
            'is_active' => true,
            'isPro'     => true
        ];
    }
}
