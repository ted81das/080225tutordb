<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\UserFeedback\UserFeedbackController;

Hooks::add('userfeedback_survey_response', [UserFeedbackController::class, 'handleSurveyResponse'], 10, 3);
