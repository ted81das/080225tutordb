<?php

namespace BitApps\BTCBI_PRO\Core\Hooks;

use FilesystemIterator;
use BitApps\BTCBI_PRO\Admin\AdminAjax;
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitCode\FI\Core\Util\StoreInCache;
use BitApps\BTCBI_PRO\Core\Util\Request;

class HookService
{
    private const CUSTOM_TRIGGERS = [
        'CustomTrigger',
        'ActionHook',
        'Spectra',
        'EssentialBlocks',
        'Coblocks',
        'GutenaForms',
        'SiteOriginWidgets',
        'Bricksforge',
        'Kadence',
        'Divi',
        'PopupMaker',
        'Brizy',
        'Bricks',
        'DiviFormBuilder',
        'PieForms',
        'Asgaros',
        'AvadaForms',
        'FluentCommunity',
        'Buddypress',
        'NinjaTables',
        'AdvancedAds',
        'SenseiLMS',
        'AdvancedCoupons',
        'AppointmentHourBooking',
        'BetterMessages',
        'ConvertPro',
        'FunnelKitAutomations',
        'CalculatedFieldsForm',
        'AmeliaBooking',
        'FluentSMTP',
        'BbPress',
        'Newsletter',
        'WishlistMember',
        'GravityKit',
        'AdvancedCustomFields',
        'PrestoPlayer',
        'SimplyScheduleAppointments',
        'LearnPress',
        'PeepSo',
        'SureMail',
        'MasteriyoLMS',
        'FluentBoards',
        'JetpackCRM',
        'Paymattic',
        'MemberPressCourse',
        'ProfileGrid',
        'ModernEventsCalendar',
        'SureFeedback',
        'WpPolls',
        'WpAllImport',
        'WPLoyalty',
        'WPLMS',
        'MyCred',
        'WpSimplePay',
        'WCMemberships',
        'NexForms',
        'QuillForms',
        'ProfileBuilder',
        'UserFeedback',
        'NewUserApprove',
    ];

    public function __construct()
    {
        $this->loadTriggersHooks();
        $this->loadTriggersRoutes();
        $this->loadAppHooks();
        $this->loadActionsHooks();
        $this->loadAdminAjax();
        Hooks::add('rest_api_init', [$this, 'loadApi']);
    }

    /**
     * Helps to register admin side ajax
     *
     * @return null
     */
    public function loadAdminAjax()
    {
        (new AdminAjax())->register();
    }

    /**
     * Helps to register integration ajax
     *
     * @return void
     */
    public function loadActionsHooks()
    {
        $this->_includeActionTaskHooks('Actions');
    }

    /**
     * Loads API routes
     *
     * @return null
     */
    public function loadApi()
    {
        if (is_readable(BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'api.php')) {
            include BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'api.php';
        }
    }

    /**
     * Helps to register App hooks
     *
     * @return null
     */
    protected function loadAppHooks()
    {
        if (Request::Check('ajax') && is_readable(BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'ajax.php')) {
            include BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'ajax.php';
        }
        if (is_readable(BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR . 'hooks.php')) {
            include BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR . 'hooks.php';
        }
    }

    /**
     * Helps to register Triggers ajax
     *
     * @return null
     */
    protected function loadTriggersHooks()
    {
        if (!\defined('BTCBI_VERSION') || version_compare(BTCBI_VERSION, '2.0.0', '<')) {
            return;
        }

        $activeTrigger = [];
        if (class_exists(\BitCode\FI\Core\Util\StoreInCache::class) && method_exists(\BitCode\FI\Core\Util\StoreInCache::class, 'getActiveFlowEntities')) {
            $activeTrigger = StoreInCache::getActiveFlowEntities() ?? [];
        }

        $activeTrigger = array_unique(array_merge($activeTrigger, static::CUSTOM_TRIGGERS));

        if (empty($activeTrigger)) {
            return;
        }

        foreach ($activeTrigger as $key => $triggerName) {
            $this->_includeTriggerTaskHooks($triggerName);
        }
    }

    /**
     * Includes Routes and Hooks
     *
     * @param string $task_name Triggers|Actions
     *
     * @return void
     */
    private function _includeTriggerTaskHooks($task_name)
    {
        $task_dir = BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR;
        $task_path = $task_dir . 'Triggers' . DIRECTORY_SEPARATOR . $task_name . DIRECTORY_SEPARATOR;
        if (is_readable($task_path . 'Hooks.php')) {
            include $task_path . 'Hooks.php';
        }
    }

    private function loadTriggersRoutes()
    {
        $task_dir = BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR . 'Triggers';
        $dirs = new FilesystemIterator($task_dir);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $task_name = basename($dirInfo);
                $task_path = $task_dir . DIRECTORY_SEPARATOR . $task_name . DIRECTORY_SEPARATOR;
                if (is_readable($task_path . 'Routes.php') && Request::Check('ajax') && Request::Check('admin')) {
                    include $task_path . 'Routes.php';
                }
            }
        }
    }

    private function _includeActionTaskHooks($task_name)
    {
        $task_dir = BTCBI_PRO_PLUGIN_BASEDIR . 'includes' . DIRECTORY_SEPARATOR . $task_name;
        $dirs = new FilesystemIterator($task_dir);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $task_name = basename($dirInfo);
                $task_path = $task_dir . DIRECTORY_SEPARATOR . $task_name . DIRECTORY_SEPARATOR;
                if (is_readable($task_path . 'Routes.php') && Request::Check('ajax') && Request::Check('admin')) {
                    include $task_path . 'Routes.php';
                }
                if (is_readable($task_path . 'Hooks.php')) {
                    include $task_path . 'Hooks.php';
                }
            }
        }
    }
}
