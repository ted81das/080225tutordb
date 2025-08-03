<?php

namespace BitApps\BTCBI_PRO\Triggers\KaliForms;

use BitApps\BTCBI_PRO\Triggers\Webhook\WebhookController;

final class KaliFormsController extends WebhookController
{
    public static function info()
    {
        return [
            'name'      => 'Kali Forms',
            'title'     => __('Get callback data through an URL', 'bit-integrations-pro'),
            'type'      => 'webhook',
            'is_active' => true,
            'isPro'     => true
        ];
    }
}
