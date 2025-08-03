<?php

namespace BitApps\BTCBI_PRO\Triggers\Paymattic;

use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use WPPayForm\App\Models\Submission;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class PaymatticController
{
    public static function info()
    {
        return [
            'name'              => 'Paymattic',
            'title'             => __('Paymattic is a WordPress Payment form plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'paymattic/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'paymattic/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'paymattic/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Paymattic'));
        }

        wp_send_json_success([
            ['form_name' => __('Payment Form Submission', 'bit-integrations-pro'), 'triggered_entity_id' => 'paymattic_after_form_submission_complete', 'skipPrimaryKey' => false],
            ['form_name' => __('Payment Status Changed', 'bit-integrations-pro'), 'triggered_entity_id' => 'paymattic_after_payment_status_change', 'skipPrimaryKey' => true],
            ['form_name' => __('Payment Success', 'bit-integrations-pro'), 'triggered_entity_id' => 'paymattic_form_payment_success', 'skipPrimaryKey' => true],
            ['form_name' => __('Payment Failed', 'bit-integrations-pro'), 'triggered_entity_id' => 'paymattic_form_payment_failed', 'skipPrimaryKey' => true],
            ['form_name' => __('Note Created By User', 'bit-integrations-pro'), 'triggered_entity_id' => 'paymattic_after_create_note_by_user', 'skipPrimaryKey' => true],
        ]);
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handlePaymentFormCompleted($submission, $form_id)
    {
        if (! isset($form_id)) {
            return;
        }

        $formData = Helper::prepareFetchFormatFields((array) json_decode(wp_json_encode($submission)));
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_paymattic_after_form_submission_complete_test', array_values($formData), 'form_id.value', $formData['form_id']['value']);

        $flows = Flow::exists('Paymattic', 'paymattic_after_form_submission_complete');
        if (!$flows) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) || !Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                continue;
            }

            Flow::execute('Paymattic', 'paymattic_after_form_submission_complete', array_column($formData, 'value', 'name'), [$flow]);
        }

        return ['type' => 'success'];
    }

    public static function handlePaymentStatusChanged($submissionId, $newStatus)
    {
        if (!self::isPluginInstalled()) {
            return;
        }

        return self::execute(
            'paymattic_after_payment_status_change',
            array_merge(
                (array) self::getSubmissionById($submissionId),
                ['new_status' => $newStatus]
            )
        );
    }

    public static function handlePaymentSuccess($submission, $transaction, $formId, $updateData)
    {
        if (!self::isPluginInstalled()) {
            return;
        }

        return self::execute(
            'paymattic_form_payment_success',
            [
                'form_id'     => $formId,
                'submission'  => self::jsonDecode($submission),
                'transaction' => self::jsonDecode($transaction),
                'update'      => $updateData
            ]
        );
    }

    public static function handlePaymentFailed($submission, $formId, $transaction, $updateData)
    {
        if (!self::isPluginInstalled()) {
            return;
        }

        return self::execute(
            'paymattic_form_payment_failed',
            [
                'form_id'     => $formId,
                'submission'  => self::jsonDecode($submission),
                'transaction' => self::jsonDecode($transaction),
                'update'      => $updateData
            ]
        );
    }

    public static function handleNoteCreatedByUser($note)
    {
        if (!self::isPluginInstalled() || empty($note['submission_id'])) {
            return;
        }

        return self::execute(
            'paymattic_after_create_note_by_user',
            array_merge(
                $note,
                ['submission' => self::getSubmissionById($note['submission_id'])]
            )
        );
    }

    private static function execute($triggeredEntityId, $data)
    {
        $data = Helper::prepareFetchFormatFields($data);

        if (empty($data) || !\is_array($data)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggeredEntityId}_test", array_values($data));

        $flows = Flow::exists('Paymattic', $triggeredEntityId);

        if (!$flows) {
            return;
        }

        Flow::execute('Paymattic', $triggeredEntityId, array_column($data, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }

    private static function getSubmissionById($id)
    {
        $submission = (new Submission())->getSubmission($id);

        return self::jsonDecode($submission);
    }

    private static function jsonDecode($data)
    {
        return json_decode(wp_json_encode($data));
    }

    private static function isPluginInstalled()
    {
        return \defined('WPPAYFORM_VERSION');
    }
}
