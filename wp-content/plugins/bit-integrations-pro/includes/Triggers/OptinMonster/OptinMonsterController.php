<?php

namespace BitApps\BTCBI_PRO\Triggers\OptinMonster;

use BitApps\BTCBI_PRO\Triggers\Webhook\WebhookController;

final class OptinMonsterController extends WebhookController
{
    public static function info()
    {
        return [
            'name'      => 'OptinMonster',
            'title'     => __('Get callback data through an URL', 'bit-integrations-pro'),
            'type'      => 'webhook',
            'is_active' => true,
            'isPro'     => true
        ];
    }
}
