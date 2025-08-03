<?php

namespace BitApps\BTCBI_PRO\Triggers\WPJobManager;

use BitCode\FI\Flow\Flow;

final class WPJobManagerController
{
    private static $pluginPath = 'wp-job-manager/wp-job-manager.php';

    public static function info()
    {
        return [
            'name'           => 'WP Job Manager',
            'title'          => __('WP Job Manager', 'bit-integrations-pro'),
            'slug'           => self::$pluginPath,
            'pro'            => self::$pluginPath,
            'type'           => 'form',
            'is_active'      => is_plugin_active(self::$pluginPath),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . self::$pluginPath . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . self::$pluginPath),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . self::$pluginPath), 'install-plugin_' . self::$pluginPath),
            'list'           => [
                'action' => 'wpjobmanager/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'wpjobmanager/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active(self::$pluginPath)) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WP Job Manager'));
        }

        $tasks = [
            ['id' => 'wp_job_manager-1', 'title' => __('Job published', 'bit-integrations-pro'), 'note' => __('Runs after a user publishes a new job of a particular type', 'bit-integrations-pro')],
            ['id' => 'wp_job_manager-2', 'title' => __('A job is filled', 'bit-integrations-pro'), 'note' => __('Runs after a user marks a job as filled', 'bit-integrations-pro')],
            ['id' => 'wp_job_manager-3', 'title' => __('Job - mark not filled', 'bit-integrations-pro'), 'note' => __('Runs after a user marks a job as not filled', 'bit-integrations-pro')],
            [
                'id'   => 'wp_job_manager-4', 'title' => __('A user marks a specific type of job as filled', 'bit-integrations-pro'),
                'note' => __('Runs after user marks a specific type (e.g., job types - full time, part time, etc.) of job as filled', 'bit-integrations-pro')
            ],
            ['id' => 'wp_job_manager-5', 'title' => __('A user marks a specific type of job as not filled', 'bit-integrations-pro'), 'note' => ''],
            ['id' => 'wp_job_manager-6', 'title' => __('A user updates a job', 'bit-integrations-pro'), 'note' => __('Runs after a user updates a job from the job dashboard', 'bit-integrations-pro')],
            ['id' => 'wp_job_manager-7', 'title' => __('A user receives an application to a specific type of job', 'bit-integrations-pro'), 'note' => ''],
            ['id' => 'wp_job_manager-8', 'title' => __('An application is received for a specific job type', 'bit-integrations-pro'), 'note' => ''],
            ['id' => 'wp_job_manager-9', 'title' => __('A user\'s application is set to a specific status', 'bit-integrations-pro'), 'note' => ''],
            ['id' => 'wp_job_manager-10', 'title' => __('A user\'s application of a specific job type is set to a specific status.', 'bit-integrations-pro'), 'note' => ''],
            ['id' => 'wp_job_manager-11', 'title' => __('Applies with resume', 'bit-integrations-pro'), 'note' => __('An user applies for a job (submits an application) with a resume', 'bit-integrations-pro')],
        ];

        wp_send_json_success($tasks);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active(self::$pluginPath)) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'WP Job Manager'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        if ($data->id === 'wp_job_manager-1' || $data->id === 'wp_job_manager-4' || $data->id === 'wp_job_manager-5' || $data->id === 'wp_job_manager-8') {
            $jobTypes = WPJobManagerHelper::getAllJobTypes();

            if (!empty($jobTypes)) {
                $responseData['jobTypes'] = $jobTypes;
            }
        } elseif ($data->id === 'wp_job_manager-2' || $data->id === 'wp_job_manager-3' || $data->id === 'wp_job_manager-6' || $data->id === 'wp_job_manager-7' || $data->id === 'wp_job_manager-11') {
            $jobList = WPJobManagerHelper::getAllJobs();

            if (!empty($jobList)) {
                $responseData['jobList'] = $jobList;
            }
        } elseif ($data->id === 'wp_job_manager-9') {
            $statusList = WPJobManagerHelper::getApplicationStatuses();

            if (!empty($statusList)) {
                $responseData['statusList'] = $statusList;
            }
        } elseif ($data->id === 'wp_job_manager-10') {
            $jobTypes = WPJobManagerHelper::getAllJobTypes();

            if (!empty($jobTypes)) {
                $responseData['jobTypes'] = $jobTypes;
            }

            $statusList = WPJobManagerHelper::getApplicationStatuses();

            if (!empty($statusList)) {
                $responseData['statusList'] = $statusList;
            }
        }

        $responseData['fields'] = self::fields($data);

        wp_send_json_success($responseData);
    }

    public static function fields($data)
    {
        return WPJobManagerHelper::getFields($data);
    }

    public static function handleWPJobManagerJobPublished($newStatus, $oldStatus, $post)
    {
        if (empty($newStatus) || empty($oldStatus) || empty($post)) {
            return false;
        }

        if (isset($post->post_type)) {
            if ($post->post_type !== 'job_listing' || $oldStatus === 'publish' || $newStatus !== 'publish') {
                return false;
            }
        }

        $terms = get_the_terms($post->ID, 'job_listing_type');

        if (!empty($terms)) {
            $termId = $terms[0]->term_id;
        } else {
            $termId = 'empty_terms';
        }

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-1');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJobType', $termId);

        if (empty($flows) || !$flows) {
            return;
        }

        $jobPublishedData = WPJobManagerHelper::formatJobPublishedData($post, $terms);

        Flow::execute('WPJobManager', 'wp_job_manager-1', $jobPublishedData, $flows);
    }

    public static function handleWPJobManagerJobFilled($action, $jobId)
    {
        if (empty($jobId)) {
            return false;
        }

        if ($action !== 'mark_filled') {
            return;
        }

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-2');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJob', $jobId);

        if (empty($flows) || !$flows) {
            return;
        }

        $jobFilledData = WPJobManagerHelper::formatJobFilledData($jobId);

        if (!empty($jobFilledData)) {
            Flow::execute('WPJobManager', 'wp_job_manager-2', $jobFilledData, $flows);
        }
    }

    public static function handleWPJobManagerJobNotFilled($action, $jobId)
    {
        if (empty($jobId)) {
            return false;
        }

        if ($action !== 'mark_not_filled') {
            return;
        }

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-3');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJob', $jobId);

        if (empty($flows) || !$flows) {
            return;
        }

        $jobNotFilledData = WPJobManagerHelper::formatJobFilledData($jobId);

        if (!empty($jobNotFilledData)) {
            Flow::execute('WPJobManager', 'wp_job_manager-3', $jobNotFilledData, $flows);
        }
    }

    public static function handleSpecificTypeJobFilled($action, $jobId)
    {
        if (empty($jobId)) {
            return false;
        }

        if ($action !== 'mark_filled') {
            return;
        }

        $jobTypes = wpjm_get_the_job_types($jobId);

        if (empty($jobTypes)) {
            return;
        }

        $typeId = $jobTypes[0]->term_id;

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-4');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJobType', $typeId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = WPJobManagerHelper::formatJobFilledData($jobId);

        if (!empty($data)) {
            Flow::execute('WPJobManager', 'wp_job_manager-4', $data, $flows);
        }
    }

    public static function handleSpecificTypeJobNotFilled($action, $jobId)
    {
        if (empty($jobId)) {
            return false;
        }

        if ($action !== 'mark_not_filled') {
            return;
        }

        $jobTypes = wpjm_get_the_job_types($jobId);

        if (empty($jobTypes)) {
            return;
        }

        $typeId = $jobTypes[0]->term_id;

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-5');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJobType', $typeId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = WPJobManagerHelper::formatJobFilledData($jobId);

        if (!empty($data)) {
            Flow::execute('WPJobManager', 'wp_job_manager-5', $data, $flows);
        }
    }

    public static function handleJobUpdate($jobId, $saveMessage, $values)
    {
        if (empty($jobId)) {
            return false;
        }

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-6');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJob', $jobId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = WPJobManagerHelper::formatJobFilledData($jobId);

        if (!empty($data)) {
            Flow::execute('WPJobManager', 'wp_job_manager-6', $data, $flows);
        }
    }

    public static function handleApplicationSubmit($applicationId, $jobId)
    {
        if (!is_plugin_active('wp-job-manager-applications/wp-job-manager-applications.php')) {
            return;
        }

        if (empty($applicationId) || empty($jobId)) {
            return;
        }

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-7');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJob', $jobId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = WPJobManagerHelper::formatApplicationData($applicationId, $jobId);

        if (!empty($data)) {
            Flow::execute('WPJobManager', 'wp_job_manager-7', $data, $flows);
        }
    }

    public static function handleApplicationSubmitSpecificType($applicationId, $jobId)
    {
        if (!is_plugin_active('wp-job-manager-applications/wp-job-manager-applications.php')) {
            return;
        }

        if (empty($applicationId) || empty($jobId)) {
            return;
        }

        $jobTypes = wpjm_get_the_job_types($jobId);

        if (empty($jobTypes)) {
            return;
        }

        $typeId = $jobTypes[0]->term_id;

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-8');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJobType', $typeId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = WPJobManagerHelper::formatApplicationData($applicationId, $jobId);

        if (!empty($data)) {
            Flow::execute('WPJobManager', 'wp_job_manager-8', $data, $flows);
        }
    }

    public static function handleApplicationStatusChange($postId, $postAfter, $postBefore)
    {
        if (!is_plugin_active('wp-job-manager-applications/wp-job-manager-applications.php')) {
            return;
        }

        if (empty($postId) || empty($postAfter) || empty($postBefore)) {
            return;
        }

        if ($postAfter->post_type !== 'job_application' || $postAfter->post_status === $postBefore->post_status) {
            return;
        }

        $newStatus = $postAfter->post_status;
        $oldStatus = $postBefore->post_status;

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-9');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedStatus', $newStatus);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = WPJobManagerHelper::formatApplicationStatusData($postId, $newStatus, $oldStatus);

        if (!empty($data)) {
            Flow::execute('WPJobManager', 'wp_job_manager-9', $data, $flows);
        }
    }

    public static function handleJobTypeApplicationStatusChange($postId, $postAfter, $postBefore)
    {
        if (!is_plugin_active('wp-job-manager-applications/wp-job-manager-applications.php')) {
            return;
        }

        if (empty($postId) || empty($postAfter) || empty($postBefore)) {
            return;
        }

        if ($postAfter->post_type !== 'job_application' || $postAfter->post_status === $postBefore->post_status) {
            return;
        }

        $newStatus = $postAfter->post_status;
        $oldStatus = $postBefore->post_status;
        $jobId = $postAfter->post_parent;
        $jobTypes = wpjm_get_the_job_types($jobId);

        if (empty($jobTypes)) {
            return;
        }

        $typeId = $jobTypes[0]->term_id;

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-10');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJobType', $typeId);
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedStatus', $newStatus);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = WPJobManagerHelper::formatApplicationStatusData($postId, $newStatus, $oldStatus);

        if (!empty($data)) {
            Flow::execute('WPJobManager', 'wp_job_manager-10', $data, $flows);
        }
    }

    public static function handleApplyWithResume($userId, $jobId, $resumeId, $applicationMessage)
    {
        if (!is_plugin_active('wp-job-manager-resumes/wp-job-manager-resumes.php')) {
            return;
        }

        if (empty($userId) || empty($jobId) || empty($resumeId)) {
            return;
        }

        $flows = Flow::exists('WPJobManager', 'wp_job_manager-11');
        $flows = WPJobManagerHelper::flowFilter($flows, 'selectedJob', $jobId);

        if (empty($flows) || !$flows) {
            return;
        }

        $data = WPJobManagerHelper::handleResumeData($resumeId, $applicationMessage);

        if (!empty($data)) {
            Flow::execute('WPJobManager', 'wp_job_manager-11', $data, $flows);
        }
    }
}
