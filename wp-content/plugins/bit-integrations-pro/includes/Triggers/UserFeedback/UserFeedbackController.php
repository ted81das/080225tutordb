<?php

namespace BitApps\BTCBI_PRO\Triggers\UserFeedback;

use UserFeedback_Survey;
use BitCode\FI\Flow\Flow;
use BitCode\FI\Core\Util\Helper;
use BitApps\BTCBI_PRO\Triggers\TriggerController;

final class UserFeedbackController
{
    public static function info()
    {
        return [
            'name'              => 'UserFeedback',
            'title'             => __('Conversational Forms Builder for WordPress', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => static::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'userfeedback/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'userfeedback/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'userfeedback/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!static::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'UserFeedback'));
        }

        wp_send_json_success([
            ['form_name' => __('Survey Response', 'bit-integrations-pro'), 'triggered_entity_id' => 'userfeedback_survey_response', 'skipPrimaryKey' => false],
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

    public static function handleSurveyResponse($survey_id, $response_id, $request)
    {
        if (empty($survey_id) || empty($response_id) || empty($request['answers']) || !class_exists('UserFeedback_Survey')) {
            return;
        }

        $survey = UserFeedback_Survey::get_by('id', $survey_id);
        if (empty($survey) || empty($survey->questions)) {
            return;
        }

        $questionsById = [];
        foreach ($survey->questions as $question) {
            $questionsById[$question->id] = $question;
        }

        $formData = [
            'survey_id'   => $survey_id,
            'response_id' => $response_id,
        ];

        foreach ($request['answers'] as $answer) {
            $questionId = $answer['question_id'] ?? null;
            $value = $answer['value'] ?? null;

            if (!$questionId || !isset($questionsById[$questionId])) {
                continue;
            }

            $question = $questionsById[$questionId];
            $formData[$question->title] = $value;
        }

        $formData = Helper::prepareFetchFormatFields($formData);
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData('btcbi_userfeedback_survey_response_test', array_values($formData), 'survey_id.value', $survey_id);

        return static::flowExecute('userfeedback_survey_response', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        $flows = Flow::exists('UserFeedback', $triggered_entity_id);
        if (empty($flows)) {
            return;
        }

        foreach ($flows as $flow) {
            $flowDetails = Helper::parseFlowDetails($flow->flow_details);

            if (!isset($flowDetails->primaryKey) || !Helper::isPrimaryKeysMatch($formData, $flowDetails->primaryKey)) {
                continue;
            }

            Flow::execute('UserFeedback', $flowDetails->triggered_entity_id, array_column($formData, 'value', 'name'), [$flow]);
        }

        return ['type' => 'success'];
    }

    private static function isPluginInstalled()
    {
        return class_exists('UserFeedback_Base');
    }
}
