<?php

namespace BitApps\BTCBI_PRO\Triggers;

use FilesystemIterator;
use WP_Error;

final class TriggerController
{
    public static function triggerList($triggers = [])
    {
        $dirs = new FilesystemIterator(__DIR__);

        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $trigger = basename($dirInfo);

                if (file_exists(__DIR__ . '/' . $trigger . '/' . $trigger . 'Controller.php')) {
                    $trigger_controller = __NAMESPACE__ . "\\{$trigger}\\{$trigger}Controller";

                    if (method_exists($trigger_controller, 'info')) {
                        $triggers[$trigger] = $trigger_controller::info();
                    }
                }
            }
        }

        return $triggers;
    }

    public static function getTriggerField($triggerName, $data)
    {
        $trigger = basename($triggerName);

        if (file_exists(__DIR__ . '/' . $trigger . '/' . $trigger . 'Controller.php')) {
            $trigger_controller = __NAMESPACE__ . "\\{$trigger}\\{$trigger}Controller";

            if (method_exists($trigger_controller, 'get_a_form')) {
                $trigger = new $trigger_controller();

                return $trigger::fields($data->id);
            }
        }

        return [];
    }

    public static function getTestData($triggerName)
    {
        $testData = get_option("btcbi_{$triggerName}_test");

        if ($testData === false) {
            update_option("btcbi_{$triggerName}_test", []);
        }
        if (!$testData || empty($testData)) {
            wp_send_json_error(new WP_Error("{$triggerName}_test", \sprintf(__('%s data is empty', 'bit-integrations-pro'), $triggerName)));
        }

        wp_send_json_success($testData);
    }

    public static function removeTestData($data, $triggerName)
    {
        if (\is_object($data) && property_exists($data, 'reset') && $data->reset) {
            $testData = update_option("btcbi_{$triggerName}_test", []);
        } else {
            $testData = delete_option("btcbi_{$triggerName}_test");
        }

        if (!$testData) {
            wp_send_json_error(new WP_Error("{$triggerName}_test", __('Failed to remove test data', 'bit-integrations-pro')));
        }

        wp_send_json_success(\sprintf(__('%s test data removed successfully', 'bit-integrations-pro'), $triggerName));
    }
}
