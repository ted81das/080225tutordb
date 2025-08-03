<?php

namespace BitApps\BTCBI_PRO\Triggers\Webhook;

use WP_Error;
use WP_REST_Request;
use BitCode\FI\Flow\Flow;
use BitApps\BTCBI_PRO\Core\Util\Helper;

class WebhookController
{
    protected $webhookIntegrationsList = [
        'Webhook',
        'KaliForms',
        'Amelia',
        'WPFunnels',
        'Typebot',
        'JetForm',
        'FluentSupport',
        'BitAssist',
    ];

    public static function info()
    {
        return [
            'name'      => 'Webhook',
            'title'     => __('Get callback data through an URL', 'bit-integrations-pro'),
            'type'      => 'webhook',
            'is_active' => true,
            'isPro'     => true
        ];
    }

    public function getNewHook()
    {
        $hook_id = wp_generate_uuid4();

        if (!$hook_id) {
            wp_send_json_error(__('Failed to generate new hook', 'bit-integrations-pro'));
        }
        add_option('btcbi_webhook_' . $hook_id, [], '', 'no');
        wp_send_json_success(['hook_id' => $hook_id]);
    }

    public function getTestData($data)
    {
        $missing_field = null;

        if (!property_exists($data, 'hook_id') || (property_exists($data, 'hook_id') && !wp_is_uuid($data->hook_id))) {
            $missing_field = \is_null($missing_field) ? 'Webhook ID' : $missing_field . ', Webhook ID';
        }
        if (!\is_null($missing_field)) {
            wp_send_json_error(\sprintf(__('%s can\'t be empty or need to be valid', 'bit-integrations-pro'), $missing_field));
        }

        $testData = get_option('btcbi_webhook_' . $data->hook_id);
        if ($testData === false) {
            update_option('btcbi_webhook_' . $data->hook_id, []);
        }
        if (!$testData || empty($testData)) {
            wp_send_json_error(new WP_Error('webhook_test', __('Webhook data is empty', 'bit-integrations-pro')));
        }
        wp_send_json_success(['webhook' => $testData]);
    }

    public function removeTestData($data)
    {
        $missing_field = null;

        if (!Helper::isUserLoggedIn()) {
            wp_send_json_error(__('Logged in user required!', 'bit-integrations-pro'));
        }
        if (!property_exists($data, 'hook_id') || (property_exists($data, 'hook_id') && !wp_is_uuid($data->hook_id))) {
            $missing_field = \is_null($missing_field) ? 'Webhook ID' : $missing_field . ', Webhook ID';
        }
        if (!\is_null($missing_field)) {
            wp_send_json_error(\sprintf(__('%s can\'t be empty or need to be valid', 'bit-integrations-pro'), $missing_field));
        }

        if (property_exists($data, 'reset') && $data->reset) {
            $testData = update_option('btcbi_webhook_' . $data->hook_id, []);
        } else {
            $testData = delete_option('btcbi_webhook_' . $data->hook_id);
        }
        if (!$testData) {
            wp_send_json_error(new WP_Error('webhook_test', __('Failed to remove test data', 'bit-integrations-pro')));
        }
        wp_send_json_success(__('Webhook test data removed successfully', 'bit-integrations-pro'));
    }

    public static function makeNonNestedRecursive(array &$out, $key, array $in)
    {
        foreach ($in as $k => $v) {
            if (\is_array($v)) {
                self::makeNonNestedRecursive($out, $key . $k . '_', $v);
            } else {
                $out[$key . $k] = $v;
            }
        }
    }

    public static function makeNonNested(array $in)
    {
        $out = [];
        self::makeNonNestedRecursive($out, '', $in);

        return $out;
    }

    public static function testDataFormat($data)
    {
        if (!\is_array($data)) {
            return $data;
        }

        $out = [];
        foreach ($data as $k => $v) {
            $rootAraVal = [];
            $flatAra = [];
            if (\is_array($v)) {
                $rootAraVal[$k] = json_encode($v);
                $flatAra = self::makeNonNested([$k => $v]);
                $rootAraVal = array_merge($flatAra, $rootAraVal);
            } else {
                $rootAraVal[$k] = $v;
            }
            $out = array_merge($out, $rootAraVal);
        }

        return $out;
    }

    public function handle(WP_REST_Request $request)
    {
        $headers = (array) $request->get_headers();
        $queryParams = $request->get_query_params();
        $fileParams = $request->get_file_params() ?? [];
        $bodyContent = Helper::isJson($request->get_body())
            ? json_decode($request->get_body(), true)
            : $request->get_body_params();

        $decodedBody = self::decodeJsonParams($bodyContent);
        $data = array_merge($headers, $bodyContent, $queryParams, $fileParams);

        $params = $request->get_params();
        $hookId = $params['hook_id'] ?? null;
        unset($data['hook_id']);

        $optionKey = 'btcbi_webhook_' . $hookId;
        $finalData = self::overrideDataWithBody($data, $decodedBody);

        if (get_option($optionKey) !== false) {
            update_option($optionKey, $data);
        }

        $flows = Flow::exists($this->webhookIntegrationsList, $hookId);

        if (empty($flows)) {
            return;
        }

        Flow::execute('Webhook', $hookId, $finalData, $flows);

        return rest_ensure_response(['status' => 'success']);
    }

    private static function decodeJsonParams($Params)
    {
        return array_map(function ($param) {
            return Helper::isJson($param) ? json_decode($param, true, 512, JSON_PRETTY_PRINT) : $param;
        }, $Params);
    }

    private static function overrideDataWithBody($data, $body)
    {
        foreach ($data as $key => $value) {
            if (isset($body[$key]) && !empty($body[$key])) {
                $data[$key] = $body[$key];
            }
        }

        return $data;
    }
}
