<?php

namespace BitApps\BTCBI_PRO\Triggers\JetForm;

use BitApps\BTCBI_PRO\Triggers\Webhook\WebhookController;

final class JetFormController extends WebhookController
{
    public static function info()
    {
        return [
            'name'      => 'JetForm Builder',
            'title'     => __('Get callback data through an URL', 'bit-integrations-pro'),
            'type'      => 'webhook',
            'is_active' => true,
            'isPro'     => true
        ];
    }
}
