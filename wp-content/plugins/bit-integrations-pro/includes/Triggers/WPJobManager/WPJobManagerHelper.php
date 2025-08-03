<?php

namespace BitApps\BTCBI_PRO\Triggers\WPJobManager;

class WPJobManagerHelper
{
    private static $jobPublishedField = [
        [
            'name'  => 'ID',
            'type'  => 'text',
            'label' => 'Job ID',
        ],
        [
            'name'  => 'post_title',
            'type'  => 'text',
            'label' => 'Job Title (Position Name)',
        ],
        [
            'name'  => 'post_content',
            'type'  => 'text',
            'label' => 'Job Content (Description)',
        ],
        [
            'name'  => 'post_status',
            'type'  => 'text',
            'label' => 'Job Status',
        ],
        [
            'name'  => 'post_date',
            'type'  => 'text',
            'label' => 'Job Created At',
        ],
        [
            'name'  => 'post_date_gmt',
            'type'  => 'text',
            'label' => 'Job Created At (GMT)',
        ],
        [
            'name'  => 'application_email_url',
            'type'  => 'text',
            'label' => 'Application email/URL',
        ],
        [
            'name'  => 'company_website',
            'type'  => 'text',
            'label' => 'Company Website',
        ],
        [
            'name'  => 'company_twitter',
            'type'  => 'text',
            'label' => 'Company Twitter',
        ],
        [
            'name'  => 'location',
            'type'  => 'text',
            'label' => 'Location',
        ],
        [
            'name'  => 'company_name',
            'type'  => 'text',
            'label' => 'Company Name',
        ],
        [
            'name'  => 'company_tagline',
            'type'  => 'text',
            'label' => 'Company Tagline',
        ],
        [
            'name'  => 'company_video',
            'type'  => 'text',
            'label' => 'Company Video',
        ],
        [
            'name'  => 'remote_position',
            'type'  => 'text',
            'label' => 'Remote Position',
        ],
        [
            'name'  => 'job_listing_type',
            'type'  => 'text',
            'label' => 'Job Type',
        ],
        [
            'name'  => 'is_job_featured',
            'type'  => 'text',
            'label' => 'Is Job Featured',
        ],
        [
            'name'  => 'job_expiry_date',
            'type'  => 'text',
            'label' => 'Job Expiry Date',
        ],
        [
            'name'  => 'post_name',
            'type'  => 'text',
            'label' => 'Job Name',
        ],
        [
            'name'  => 'to_ping',
            'type'  => 'text',
            'label' => 'To Ping',
        ],
        [
            'name'  => 'pinged',
            'type'  => 'text',
            'label' => 'Pinged',
        ],
        [
            'name'  => 'guid',
            'type'  => 'text',
            'label' => 'Job guid',
        ],
    ];

    private static $jobApplicationFields = [
        [
            'name'  => 'candidate_name',
            'type'  => 'text',
            'label' => 'Candidate name',
        ],
        [
            'name'  => 'candidate_email',
            'type'  => 'text',
            'label' => 'Candidate email',
        ],
        [
            'name'  => 'candidate_message',
            'type'  => 'text',
            'label' => 'Candidate message',
        ],
        [
            'name'  => 'candidate_cv',
            'type'  => 'text',
            'label' => 'CV (attachment)',
        ],
    ];

    private static $jobApplicationStatusFields = [
        [
            'name'  => 'new_status',
            'type'  => 'text',
            'label' => 'New status',
        ],
        [
            'name'  => 'old_status',
            'type'  => 'text',
            'label' => 'Old status',
        ],
    ];

    private static $resumeFields = [
        [
            'name'  => 'resume_id',
            'type'  => 'text',
            'label' => 'Resume ID',
        ],
        [
            'name'  => 'candidate_name',
            'type'  => 'text',
            'label' => 'Candidate name',
        ],
        [
            'name'  => 'candidate_email',
            'type'  => 'text',
            'label' => 'Candidate email',
        ],
        [
            'name'  => 'professional_title',
            'type'  => 'text',
            'label' => 'Professional title',
        ],
        [
            'name'  => 'location',
            'type'  => 'text',
            'label' => 'Location',
        ],
        [
            'name'  => 'photo',
            'type'  => 'text',
            'label' => 'Photo',
        ],
        [
            'name'  => 'video',
            'type'  => 'text',
            'label' => 'Video',
        ],
        [
            'name'  => 'urls',
            'type'  => 'text',
            'label' => 'URL(s)',
        ],
        [
            'name'  => 'education',
            'type'  => 'text',
            'label' => 'Education',
        ],
        [
            'name'  => 'experience',
            'type'  => 'text',
            'label' => 'Experience',
        ],
        [
            'name'  => 'application_message',
            'type'  => 'text',
            'label' => 'Application Message',
        ],
        [
            'name'  => 'guid',
            'type'  => 'text',
            'label' => 'Resume guid',
        ],
    ];

    private static $userFieldsKey = [
        ['field_key' => 'id', 'field_label' => 'ID', 'wp_user_key' => 'ID'],
        ['field_key' => 'login', 'field_label' => 'Login', 'wp_user_key' => 'user_login'],
        ['field_key' => 'display_name', 'field_label' => 'Display Name', 'wp_user_key' => 'display_name'],
        ['field_key' => 'firstname', 'field_label' => 'First Name', 'wp_user_key' => 'user_firstname'],
        ['field_key' => 'lastname', 'field_label' => 'Last Name', 'wp_user_key' => 'user_lastname'],
        ['field_key' => 'email', 'field_label' => 'Email', 'wp_user_key' => 'user_email'],
        ['field_key' => 'role', 'field_label' => 'Role', 'wp_user_key' => 'roles'],
    ];

    public static function getAllJobTypes()
    {
        $types = get_job_listing_types();

        $typeList[] = (object) [
            'value' => 'any',
            'label' => __('Any Job Type', 'bit-integrations-pro')
        ];

        if (!empty($types)) {
            foreach ($types as $item) {
                $typeList[] = (object) ['value' => (string) $item->term_id, 'label' => $item->name];
            }
        }

        return $typeList;
    }

    public static function getAllJobs()
    {
        $jobList[] = (object) [
            'value' => 'any',
            'label' => __('Any Job', 'bit-integrations-pro')
        ];

        $args = [
            'post_type'      => 'job_listing',
            'posts_per_page' => 9999,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ];

        $jobs = get_posts($args);

        if (!is_wp_error($jobs)) {
            if (!empty($jobs)) {
                foreach ($jobs as $job) {
                    $jobList[] = (object) ['value' => (string) $job->ID, 'label' => esc_html($job->post_title)];
                }
            }
        }

        return $jobList;
    }

    public static function getApplicationStatuses()
    {
        if (!is_plugin_active('wp-job-manager-applications/wp-job-manager-applications.php')) {
            return [];
        }

        $statusList[] = (object) [
            'value' => 'any',
            'label' => 'Any Status',
        ];

        $statuses = get_job_application_statuses();

        if (!empty($statuses) && !is_wp_error($statuses)) {
            foreach ($statuses as $key => $status) {
                $statusList[] = (object) ['value' => $key, 'label' => esc_html($status)];
            }
        }

        return $statusList;
    }

    public static function getAllUsers()
    {
        $getUsers = get_users(['fields' => ['ID', 'display_name']]);

        if (empty($getUsers)) {
            return false;
        }

        $users[] = (object) [
            'value' => 'any',
            'label' => __('Any User', 'bit-integrations-pro')
        ];

        foreach ($getUsers as $item) {
            $users[] = (object) ['value' => (string) $item->ID, 'label' => $item->display_name];
        }

        return $users;
    }

    public static function getFields($data)
    {
        if (\is_string($data)) {
            $id = $data;
        } else {
            $id = $data->id;
        }

        if (empty($id)) {
            return;
        }

        $fields = [];

        if ($id === 'wp_job_manager-1' || $id === 'wp_job_manager-2' || $id === 'wp_job_manager-3' || $id === 'wp_job_manager-4' || $id === 'wp_job_manager-5' || $id === 'wp_job_manager-6') {
            $jobOwnerFields = self::createUserFieldsByTypes('job_owner');
            $fields = array_merge(self::$jobPublishedField, $jobOwnerFields);
        } elseif ($id === 'wp_job_manager-7' || $id === 'wp_job_manager-8') {
            $jobOwnerFields = self::createUserFieldsByTypes('job_owner');
            $fields = array_merge(self::$jobPublishedField, $jobOwnerFields, self::$jobApplicationFields);
        } elseif ($id === 'wp_job_manager-9' || $id === 'wp_job_manager-10') {
            $jobOwnerFields = self::createUserFieldsByTypes('job_owner');
            $fields = array_merge(self::$jobApplicationStatusFields, self::$jobPublishedField, $jobOwnerFields, self::$jobApplicationFields);
        } elseif ($id === 'wp_job_manager-11') {
            $fields = self::$resumeFields;
        }

        return $fields;
    }

    public static function formatJobPublishedData($post, $terms)
    {
        if (empty($post)) {
            return false;
        }

        $data = self::formatJobData($post);
        $data['job_listing_type'] = !empty($terms) ? $terms[0]->name : '';

        return $data;
    }

    public static function formatJobFilledData($jobId)
    {
        if (empty($jobId)) {
            return false;
        }

        $post = get_post($jobId);

        $data = self::formatJobData($post);
        $jobTypes = wpjm_get_the_job_types($post);
        $data['job_listing_type'] = !empty($jobTypes) ? $jobTypes[0]->name : '';

        return $data;
    }

    public static function formatApplicationData($applicationId, $jobId)
    {
        if (empty($applicationId) || empty($jobId)) {
            return false;
        }

        $job = get_post($jobId);
        $jobData = self::formatJobData($job);
        $jobTypes = wpjm_get_the_job_types($job);
        $jobData['job_listing_type'] = !empty($jobTypes) ? $jobTypes[0]->name : '';

        $application = get_post($applicationId);

        $applicationData['candidate_name'] = $application->post_title;
        $applicationData['candidate_email'] = get_post_meta($applicationId, '_candidate_email', true);
        $applicationData['candidate_message'] = $application->post_content;
        $attachment = get_post_meta($applicationId, '_attachment', true);

        if (!empty($attachment)) {
            if (\is_array($attachment)) {
                $applicationData['candidate_cv'] = $attachment[0];
            } else {
                $applicationData['candidate_cv'] = $attachment;
            }
        } else {
            $applicationData['candidate_cv'] = '';
        }

        return array_merge($jobData, $applicationData);
    }

    public static function formatApplicationStatusData($applicationId, $newStatus, $oldStatus)
    {
        if (empty($applicationId) || empty($newStatus) || empty($oldStatus)) {
            return false;
        }

        $application = get_post($applicationId);
        $jobId = $application->post_parent;
        $job = get_post($jobId);
        $jobData = self::formatJobData($job);
        $jobTypes = wpjm_get_the_job_types($job);
        $jobData['job_listing_type'] = !empty($jobTypes) ? $jobTypes[0]->name : '';
        $applicationData['candidate_name'] = $application->post_title;
        $applicationData['candidate_email'] = get_post_meta($applicationId, '_candidate_email', true);
        $applicationData['candidate_message'] = $application->post_content;
        $attachment = get_post_meta($applicationId, '_attachment', true);

        if (!empty($attachment)) {
            if (\is_array($attachment)) {
                $applicationData['candidate_cv'] = $attachment[0];
            } else {
                $applicationData['candidate_cv'] = $attachment;
            }
        } else {
            $applicationData['candidate_cv'] = '';
        }

        $statusData['new_status'] = $newStatus;
        $statusData['old_status'] = $oldStatus;

        return array_merge($jobData, $applicationData, $statusData);
    }

    public static function formatJobData($post)
    {
        $postData = (array) $post;

        $postMeta = get_post_meta($post->ID);

        $postMetaFormatted['application_email_url'] = !empty($postMeta['_application']) ? $postMeta['_application'][0] : '';
        $postMetaFormatted['company_website'] = !empty($postMeta['_company_website']) ? $postMeta['_company_website'][0] : '';
        $postMetaFormatted['company_twitter'] = !empty($postMeta['_company_twitter']) ? $postMeta['_company_twitter'][0] : '';
        $postMetaFormatted['location'] = !empty($postMeta['_job_location']) ? $postMeta['_job_location'][0] : '';
        $postMetaFormatted['company_name'] = !empty($postMeta['_company_name']) ? $postMeta['_company_name'][0] : '';
        $postMetaFormatted['company_tagline'] = !empty($postMeta['_company_tagline']) ? $postMeta['_company_tagline'][0] : '';
        $postMetaFormatted['company_video'] = !empty($postMeta['_company_video']) ? $postMeta['_company_video'][0] : '';
        $postMetaFormatted['remote_position'] = (!empty($postMeta['_remote_position']) && $postMeta['_remote_position'][0]) ? 'Yes' : 'No';
        $postMetaFormatted['is_job_featured'] = (!empty($postMeta['_featured']) && $postMeta['_featured'][0]) ? 'Yes' : 'No';
        $postMetaFormatted['job_expiry_date'] = !empty($postMeta['_job_expires']) ? $postMeta['_job_expires'][0] : '';

        return array_merge($postData, $postMetaFormatted, self::userDataByType($post->post_author, 'job_owner'));
    }

    public static function handleResumeData($resumeId, $applicationMessage)
    {
        if (empty($resumeId)) {
            return false;
        }

        $resume = get_post($resumeId);
        $resumeData = [];
        $resumeData['resume_id'] = $resume->ID;
        $resumeData['guid'] = $resume->guid;
        $resumeData['candidate_name'] = $resume->post_title;
        $resumeData['candidate_email'] = get_post_meta($resumeId, '_candidate_email', true);
        $resumeData['professional_title'] = get_post_meta($resumeId, '_candidate_title', true);
        $resumeData['location'] = get_post_meta($resumeId, '_candidate_location', true);
        $resumeData['photo'] = get_post_meta($resumeId, '_candidate_photo', true);
        $resumeData['video'] = get_post_meta($resumeId, '_candidate_video', true);
        $resumeData['urls'] = $resumeData['education'] = $resumeData['experience'] = '';
        $urls = get_post_meta($resumeId, '_links', true);

        if (!empty($urls)) {
            $resumeData['urls'] = json_encode(maybe_unserialize($urls));
        }

        $education = get_post_meta($resumeId, '_candidate_education', true);

        if (!empty($education)) {
            $resumeData['education'] = json_encode(maybe_unserialize($education));
        }

        $experience = get_post_meta($resumeId, '_candidate_experience', true);

        if (!empty($experience)) {
            $resumeData['experience'] = json_encode(maybe_unserialize($experience));
        }

        $resumeData['application_message'] = $applicationMessage;

        return $resumeData;
    }

    public static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];

        if (\is_array($flows) || \is_object($flows)) {
            foreach ($flows as $flow) {
                if (\is_string($flow->flow_details)) {
                    $flow->flow_details = json_decode($flow->flow_details);
                }

                if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || $flow->flow_details->{$key} == $value || $flow->flow_details->{$key} === '' || $value === 'empty_terms') {
                    $filteredFlows[] = $flow;
                }
            }
        }

        return $filteredFlows;
    }

    public static function createUserFieldsByTypes($type = null)
    {
        $userFields = [];

        foreach (self::$userFieldsKey as $item) {
            $userFields[] = [
                'name'  => (!empty($type) ? $type . '_' : '') . $item['field_key'],
                'type'  => 'text',
                'label' => (!empty($type) ? ucwords(str_replace('_', ' ', $type)) . ' ' : '') . $item['field_label'],
            ];
        }

        return $userFields;
    }

    public static function userDataByType($id, $type = null)
    {
        $userData = get_userdata(\intval($id));

        $formattedUserData = [];

        foreach (self::$userFieldsKey as $item) {
            $key = $item['wp_user_key'];

            if (\is_array($userData->{$key})) {
                $data = implode(',', $userData->{$key});
            } else {
                $data = $userData->{$key};
            }

            $formattedUserData[(!empty($type) ? $type . '_' : '') . $item['field_key']] = $data;
        }

        return $formattedUserData;
    }
}
