<?php

namespace BitApps\BTCBI_PRO\Triggers\Breakdance;

use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class BreakdanceController
{
    public static $bAllForm = [];

    private $instance;

    public static function info()
    {
        return [
            'name'                => 'Breakdance',
            'title'               => __('Breakdance is the platform web creators choose to build professional WordPress websites, grow their skills, and build their business. Start for free today!', 'bit-integrations-pro'),
            'type'                => 'custom_form_submission',
            'is_active'           => static::pluginActive(),
            'documentation_url'   => 'https://bitapps.pro/docs/bit-integrations/trigger/breakdance-integrations',
            'tutorial_url'        => 'https://youtube.com/playlist?list=PL7c6CDwwm-AKSASMJiVsaECw_seJI-hd0&si=pDTYfgiolrtoO93P',
            'triggered_entity_id' => 'BreakdanceHook',
            'tasks'               => [
                'action' => 'breakdance/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'breakdance/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'breakdance/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Breakdance'));
        }

        wp_send_json_success([
            ['form_name' => __('Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'BreakdanceHook', 'skipPrimaryKey' => false],
        ]);
    }

    public static function pluginActive($option = null)
    {
        if (is_plugin_active('breakdance/plugin.php')) {
            return $option === 'get_name' ? 'breakdance/plugin.php' : true;
        }

        return false;
    }

    public static function addAction()
    {
        if (class_exists(__NAMESPACE__ . '\BreakdanceAction')) {
            \Breakdance\Forms\Actions\registerAction(new BreakdanceAction());
        }
    }

    public function getTestData()
    {
        return TriggerController::getTestData('breakdance');
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, 'breakdance');
    }
}
