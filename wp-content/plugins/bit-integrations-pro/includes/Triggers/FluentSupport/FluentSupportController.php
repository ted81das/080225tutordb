<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentSupport;

use BitApps\BTCBI_PRO\Triggers\Webhook\WebhookController;

final class FluentSupportController extends WebhookController
{
    public static function info()
    {
        return [
            'name'      => 'Fluent Support',
            'title'     => __('Get callback data through an URL', 'bit-integrations-pro'),
            'type'      => 'webhook',
            'is_active' => true,
            'isPro'     => true
        ];
    }
}
