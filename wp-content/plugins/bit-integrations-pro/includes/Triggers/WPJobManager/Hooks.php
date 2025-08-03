<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WPJobManager\WPJobManagerController;

Hooks::add('transition_post_status', [WPJobManagerController::class, 'handleWPJobManagerJobPublished'], 10, 3);
Hooks::add('job_manager_my_job_do_action', [WPJobManagerController::class, 'handleWPJobManagerJobFilled'], 10, 2);
Hooks::add('job_manager_my_job_do_action', [WPJobManagerController::class, 'handleWPJobManagerJobNotFilled'], 10, 2);
Hooks::add('job_manager_my_job_do_action', [WPJobManagerController::class, 'handleSpecificTypeJobFilled'], 10, 2);
Hooks::add('job_manager_my_job_do_action', [WPJobManagerController::class, 'handleSpecificTypeJobNotFilled'], 10, 2);
Hooks::add('job_manager_user_edit_job_listing', [WPJobManagerController::class, 'handleJobUpdate'], 10, 3);
Hooks::add('job_manager_applications_new_job_application', [WPJobManagerController::class, 'handleApplicationSubmit'], 10, 2);
Hooks::add('job_manager_applications_new_job_application', [WPJobManagerController::class, 'handleApplicationSubmitSpecificType'], 10, 2);
Hooks::add('post_updated', [WPJobManagerController::class, 'handleApplicationStatusChange'], 10, 3);
Hooks::add('post_updated', [WPJobManagerController::class, 'handleJobTypeApplicationStatusChange'], 10, 3);
Hooks::add('resume_manager_apply_with_resume', [WPJobManagerController::class, 'handleApplyWithResume'], 10, 4);
