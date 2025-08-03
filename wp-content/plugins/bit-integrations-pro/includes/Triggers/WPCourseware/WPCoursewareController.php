<?php

namespace BitApps\BTCBI_PRO\Triggers\WPCourseware;

use BitCode\FI\Flow\Flow;

final class WPCoursewareController
{
    public static function info()
    {
        $plugin_path = 'wp-courseware/wp-courseware.php';

        return [
            'name'           => 'WP Courseware',
            'title'          => __('The first and most widely-trusted course creation plugin for WordPress, WP Courseware makes course creation simple and fast with an intuitive, drag-and-drop course builder and all the features you need to create world-class courses', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'wp-courseware/wp-courseware.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('wp-courseware/wp-courseware.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'wpcourseware/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'wpcourseware/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function userEnrolledCourse($userId, $courses)
    {
        $user = get_user_by('id', $userId);
        $flows = Flow::exists('WPCourseware', 'userEnrolledCourse');

        if (!$flows || !$user || !\function_exists('WPCW_courses_getCourseDetails')) {
            return;
        }

        foreach ($courses as $courseId) {
            $course = WPCW_courses_getCourseDetails($courseId);

            if (!$course) {
                continue;
            }

            $data = [
                'enroll_user_id'    => $userId,
                'enroll_user_name'  => $user->display_name,
                'enroll_user_email' => $user->user_email,
                'course_id'         => $courseId,
                'course_title'      => $course->course_title,
            ];

            Flow::execute('WPCourseware', 'userEnrolledCourse', $data, $flows);
        }
    }

    public static function courseCompleted($userId, $unitId, $course)
    {
        $flows = Flow::exists('WPCourseware', 'courseCompleted');
        $flows = self::flowFilter($flows, 'selectedCourse', $course->course_id);
        if (!$flows) {
            return;
        }

        $user = get_user_by('id', $userId);
        if (!$user) {
            return;
        }

        $data = [
            'enroll_user_id'    => $userId,
            'enroll_user_name'  => $user->display_name,
            'enroll_user_email' => $user->user_email,
            'course_id'         => $course->course_id,
            'course_title'      => $course->course_title,
        ];

        Flow::execute('WPCourseware', 'courseCompleted', $data, $flows);
    }

    public static function moduleCompleted($userId, $unitId, $module)
    {
        $flows = Flow::exists('WPCourseware', 'moduleCompleted');
        $flows = self::flowFilter($flows, 'selectedModule', $module->module_id);
        if (!$flows) {
            return;
        }

        $user = get_user_by('id', $userId);
        if (!$user) {
            return;
        }

        $data = [
            'enroll_user_id'    => $userId,
            'enroll_user_name'  => $user->display_name,
            'enroll_user_email' => $user->user_email,
            'module_id'         => $module->module_id,
            'module_title'      => $module->module_title,
            'course_title'      => $module->course_title,
        ];

        Flow::execute('WPCourseware', 'moduleCompleted', $data, $flows);
    }

    public static function unitCompleted($userId, $unitId, $unitData)
    {
        $flows = Flow::exists('WPCourseware', 'unitCompleted');
        $flows = self::flowFilter($flows, 'selectedUnit', $unitId);
        if (!$flows) {
            return;
        }

        $unit = get_post($unitId);
        $user = get_user_by('id', $userId);
        if (!$unit || !$user) {
            return;
        }

        $data = [
            'enroll_user_id'    => $userId,
            'enroll_user_name'  => $user->display_name,
            'enroll_user_email' => $user->user_email,
            'unit_id'           => $unitId,
            'unit_title'        => $unit->post_title,
            'module_title'      => $unitData->module_title,
            'course_title'      => $unitData->course_title,
        ];

        Flow::execute('WPCourseware', 'unitCompleted', $data, $flows);
    }

    public function getAll()
    {
        if (!is_plugin_active('wp-courseware/wp-courseware.php')) {
            wp_send_json_error(__('WP Courseware is not installed or activated', 'bit-integrations-pro'));
        }

        $wpcw_actions = [];
        foreach (self::actions() as $action) {
            $wpcw_actions[] = (object) [
                'id'    => $action['id'],
                'title' => $action['title'],
            ];
        }
        wp_send_json_success($wpcw_actions);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('wp-courseware/wp-courseware.php')) {
            wp_send_json_error(__('WP Courseware is not installed or activated', 'bit-integrations-pro'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = self::fields($data->id);
        if (empty($fields)) {
            wp_send_json_error(__('Form doesn\'t exists any field', 'bit-integrations-pro'));
        }

        if ($data->id == 'userEnrolledCourse' || $data->id == 'courseCompleted') {
            $responseData['courses'] = $this->getWPCWCourses();
        } elseif ($data->id === 'moduleCompleted') {
            $responseData['modules'] = $this->getWPCWModules();
        } elseif ($data->id === 'unitCompleted') {
            $responseData['units'] = $this->getWPCWUnits();
        }

        $responseData['fields'] = $fields;
        wp_send_json_success($responseData);
    }

    public static function fields($selectedAction)
    {
        $fieldDetails = [];
        if ($selectedAction == 'userEnrolledCourse' || $selectedAction == 'courseCompleted') {
            $fieldDetails = self::courseFields();
        } elseif ($selectedAction === 'moduleCompleted') {
            $fieldDetails = self::moduleCompletedFields();
        } elseif ($selectedAction === 'unitCompleted') {
            $fieldDetails = self::unitCompletedFields();
        }

        $fields = [];
        foreach ($fieldDetails as $field) {
            $fields[] = [
                'name'  => $field['key'],
                'label' => $field['label'],
                'type'  => isset($field['type']) ? $field['type'] : 'text',
            ];
        }

        return $fields;
    }

    protected static function actions()
    {
        return [
            [
                'id'    => 'userEnrolledCourse',
                'title' => __('User Enrolled in Course', 'bit-integrations-pro'),
            ], [
                'id'    => 'courseCompleted',
                'title' => __('User Completed Course', 'bit-integrations-pro'),
            ], [
                'id'    => 'moduleCompleted',
                'title' => __('User Completed Module', 'bit-integrations-pro'),
            ], [
                'id'    => 'unitCompleted',
                'title' => __('User Completed Unit', 'bit-integrations-pro'),
            ]
        ];
    }

    protected static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];
        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
            }
            if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || $flow->flow_details->{$key} == $value || $flow->flow_details->{$key} === '') {
                $filteredFlows[] = $flow;
            }
        }

        return $filteredFlows;
    }

    protected static function courseFields()
    {
        return [[
            'key'   => 'enroll_user_id',
            'label' => __('Enroll User ID', 'bit-integrations-pro')
        ], [
            'key'   => 'enroll_user_name',
            'label' => __('Enroll User Name', 'bit-integrations-pro')
        ], [
            'key'   => 'enroll_user_email',
            'label' => __('Enroll User Email', 'bit-integrations-pro'),
            'type'  => 'email',
        ], [
            'key'   => 'course_id',
            'label' => __('Course ID', 'bit-integrations-pro')
        ], [
            'key'   => 'course_title',
            'label' => __('Course Title', 'bit-integrations-pro')
        ]];
    }

    protected static function moduleCompletedFields()
    {
        return [[
            'key'   => 'enroll_user_id',
            'label' => __('Enroll User ID', 'bit-integrations-pro'),
        ], [
            'key'   => 'enroll_user_name',
            'label' => __('Enroll User Name', 'bit-integrations-pro'),
        ], [
            'key'   => 'enroll_user_email',
            'label' => __('Enroll User Email', 'bit-integrations-pro'),
            'type'  => 'email',
        ], [
            'key'   => 'module_id',
            'label' => __('Module ID', 'bit-integrations-pro'),
        ], [
            'key'   => 'module_title',
            'label' => __('Module Title', 'bit-integrations-pro'),
        ]];
    }

    protected static function unitCompletedFields()
    {
        return [[
            'key'   => 'enroll_user_id',
            'label' => __('Enroll User ID', 'bit-integrations-pro'),
        ], [
            'key'   => 'enroll_user_name',
            'label' => __('Enroll User Name', 'bit-integrations-pro'),
        ], [
            'key'   => 'enroll_user_email',
            'label' => __('Enroll User Email', 'bit-integrations-pro'),
            'type'  => 'email',
        ], [
            'key'   => 'unit_id',
            'label' => __('Unit ID', 'bit-integrations-pro'),
        ], [
            'key'   => 'unit_title',
            'label' => __('Unit Title', 'bit-integrations-pro'),
        ], [
            'key'   => 'module_title',
            'label' => __('Module Title', 'bit-integrations-pro'),
        ], [
            'key'   => 'course_title',
            'label' => __('Course Title', 'bit-integrations-pro'),
        ]];
    }

    protected function getWPCWCourses()
    {
        $wpcwCourses = \function_exists('wpcw_get_courses') ? wpcw_get_courses() : [];
        $courses = [(object) [
            'label' => __('Any Course', 'bit-integrations-pro'),
            'value' => 'any',
        ]];
        foreach ($wpcwCourses as $course) {
            $courses[] = (object) [
                'label' => $course->course_title,
                'value' => $course->course_id,
            ];
        }

        return $courses;
    }

    protected function getWPCWModules()
    {
        $wpcwModules = \function_exists('wpcw_get_modules') ? wpcw_get_modules() : [];
        $modules = [(object) [
            'label' => __('Any Module'),
            'value' => 'any',
        ]];
        foreach ($wpcwModules as $module) {
            $modules[] = (object) [
                'label' => $module->module_title,
                'value' => $module->module_id,
            ];
        }

        return $modules;
    }

    protected function getWPCWUnits()
    {
        $wpcwUnits = \function_exists('wpcw_get_units') ? wpcw_get_units() : [];
        $units = [(object) [
            'label' => __('Any Unit', 'bit-integrations-pro'),
            'value' => 'any',
        ]];
        foreach ($wpcwUnits as $unit) {
            $postData = $unit->get_post_data();
            $units[] = (object) [
                'label' => $postData['post_title'],
                'value' => $unit->unit_id,
            ];
        }

        return $units;
    }
}
