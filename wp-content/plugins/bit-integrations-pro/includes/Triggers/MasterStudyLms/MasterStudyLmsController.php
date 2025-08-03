<?php

namespace BitApps\BTCBI_PRO\Triggers\MasterStudyLms;

use BitCode\FI\Flow\Flow;

final class MasterStudyLmsController
{
    private const COMPLETE_COURSE = 1;

    private const COMPLETE_LESSON = 2;

    private const ENROLLED_COURSE = 3;

    private const PASSED_QUIZ = 4;

    private const FAILED_QUIZ = 5;

    private const EARN_POINT = 6;

    public static function info()
    {
        $plugin_path = self::pluginActive('get_name');

        return [
            'name'           => 'MasterStudyLms',
            'title'          => __('MasterStudyLms', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => is_plugin_active($plugin_path),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'masterstudylms/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'masterstudylms/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function pluginActive()
    {
        return (bool) (is_plugin_active('masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php'));
    }

    public function getAll()
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'MasterStudy Lms'));
        }

        wp_send_json_success([
            (object) ['id' => static::COMPLETE_COURSE, 'title' => __('User Complete a Course', 'bit-integrations-pro')],
            (object) ['id' => static::COMPLETE_LESSON, 'title' => __('User Complete a Lesson', 'bit-integrations-pro')],
            (object) ['id' => static::ENROLLED_COURSE, 'title' => __('User Enrolled in a Course', 'bit-integrations-pro')],
            (object) ['id' => static::PASSED_QUIZ, 'title' => __('User Passed a Quiz', 'bit-integrations-pro')],
            (object) ['id' => static::FAILED_QUIZ, 'title' => __('User Failed a Quiz', 'bit-integrations-pro')],
            (object) ['id' => static::EARN_POINT, 'title' => __('User Earns a Point', 'bit-integrations-pro')],
        ]);
    }

    public function get_a_form($data)
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'MasterStudy Lms'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Trigger type doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = MasterStudyLmsHelper::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Trigger doesn\'t exists any field', 'bit-integrations-pro'));
        }

        $responseData['fields'] = $fields;

        $id = $data->id;

        if (\in_array($id, [static::COMPLETE_COURSE, static::ENROLLED_COURSE, static::PASSED_QUIZ, static::FAILED_QUIZ])) {
            $responseData['allCourse'] = array_merge([[
                'id'    => 'any',
                'title' => __('Any Course', 'bit-integrations-pro')
            ]], MasterStudyLmsHelper::getAllCourse());
        } elseif ($id == static::COMPLETE_LESSON) {
            $responseData['allLesson'] = array_merge([[
                'id'    => 'any',
                'title' => __('Any Lesson', 'bit-integrations-pro')
            ]], MasterStudyLmsHelper::getAllLesson());
        } elseif ($id == static::EARN_POINT) {
            $responseData['allDistribution'] = MasterStudyLmsHelper::getAllDistribution();
        }

        wp_send_json_success($responseData);
    }

    public static function getAllQuizByCourse($data)
    {
        $quizzes = MasterStudyLmsHelper::getAllQuiz($data->course_id);
        if (empty($quizzes)) {
            wp_send_json_error(__('No quiz Found', 'bit-integrations-pro'));
        }
        foreach ($quizzes as $key => $value) {
            $allQuiz[] = [
                'id'    => $value->ID,
                'title' => $value->post_title,
            ];
        }
        $allQuiz = array_merge([['id' => 'any', 'title' => __('Any Quiz', 'bit-integrations-pro')]], $allQuiz);
        wp_send_json_success($allQuiz);
    }

    public static function handleCourseComplete($course_id, $user_id, $progress)
    {
        if (empty($progress) || absint($progress) < 100) {
            return;
        }

        $flows = Flow::exists('MasterStudyLms', static::COMPLETE_COURSE);

        if (!$flows) {
            return;
        }

        $userInfo = MasterStudyLmsHelper::getUserInfo($user_id);
        $courseDetails = MasterStudyLmsHelper::getCourseDetail($course_id);

        $finalData = [
            'user_id'            => $user_id,
            'course_id'          => $course_id,
            'course_title'       => $courseDetails[0]->post_title,
            'course_description' => $courseDetails[0]->post_content,
            'first_name'         => $userInfo['first_name'],
            'last_name'          => $userInfo['last_name'],
            'nickname'           => $userInfo['nickname'],
            'avatar_url'         => $userInfo['avatar_url'],
            'user_email'         => $userInfo['user_email'],
        ];

        $flowDetails = json_decode($flows[0]->flow_details);
        $selectedCourse = !empty($flowDetails->selectedCourse) ? $flowDetails->selectedCourse : [];

        if ($flows && (empty($selectedCourse) || $course_id == $selectedCourse || $selectedCourse === 'any')) {
            Flow::execute('MasterStudyLms', static::COMPLETE_COURSE, $finalData, $flows);
        }
    }

    public static function handlePointScoreCharge($user_id, $action_id, $score)
    {
        $flows = Flow::exists('MasterStudyLms', static::EARN_POINT);

        if (!$flows) {
            return;
        }

        $actions = stm_lms_point_system();
        $action = $actions[$action_id] ?? [];

        if (empty($action)) {
            return;
        }

        $finalData = MasterStudyLmsHelper::prepareFinalData($user_id, $action, $score);

        foreach ($flows as $flow) {
            $flow->flow_details = \is_string($flow->flow_details) ? json_decode($flow->flow_details) : $flow->flow_details;
            $selectedDistribution = $flow->flow_details->selectedDistribution ?? 'any';

            if (($action_id == 'user_registered' && $selectedDistribution == 'user_registered_affiliate')
            || ($action_id == 'course_purchased' && $selectedDistribution == 'course_purchased_affiliate')) {
                $finalData = MasterStudyLmsHelper::prepareFinalData($user_id, $action, $score, $selectedDistribution, true);
            } elseif ($selectedDistribution != 'any' && $selectedDistribution != $action_id) {
                continue;
            }

            Flow::execute('MasterStudyLms', static::EARN_POINT, $finalData, [$flow]);
        }
    }

    public static function handleCourseEnroll($user_id, $course_id)
    {
        $flows = Flow::exists('MasterStudyLms', static::ENROLLED_COURSE);
        if (!$flows) {
            return;
        }

        $userInfo = MasterStudyLmsHelper::getUserInfo($user_id);
        $courseDetails = MasterStudyLmsHelper::getCourseDetail($course_id);

        $finalData = [
            'user_id'            => $user_id,
            'course_id'          => $course_id,
            'course_title'       => $courseDetails[0]->post_title,
            'course_description' => $courseDetails[0]->post_content,
            'first_name'         => $userInfo['first_name'],
            'last_name'          => $userInfo['last_name'],
            'nickname'           => $userInfo['nickname'],
            'avatar_url'         => $userInfo['avatar_url'],
            'user_email'         => $userInfo['user_email'],
        ];

        $flowDetails = json_decode($flows[0]->flow_details);
        $selectedCourse = !empty($flowDetails->selectedCourse) ? $flowDetails->selectedCourse : [];
        if ($flows && (empty($selectedCourse) || $course_id == $selectedCourse || $selectedCourse === 'any')) {
            Flow::execute('MasterStudyLms', static::ENROLLED_COURSE, $finalData, $flows);
        }
    }

    public static function handleLessonComplete($user_id, $lesson_id)
    {
        $flows = Flow::exists('MasterStudyLms', static::COMPLETE_LESSON);
        if (!$flows) {
            return;
        }

        $userInfo = MasterStudyLmsHelper::getUserInfo($user_id);
        $lessonDetails = MasterStudyLmsHelper::getLessonDetail($lesson_id);

        $finalData = [
            'user_id'            => $user_id,
            'lesson_id'          => $lesson_id,
            'lesson_title'       => $lessonDetails[0]->post_title,
            'lesson_description' => $lessonDetails[0]->post_content,
            'first_name'         => $userInfo['first_name'],
            'last_name'          => $userInfo['last_name'],
            'nickname'           => $userInfo['nickname'],
            'avatar_url'         => $userInfo['avatar_url'],
            'user_email'         => $userInfo['user_email'],
        ];

        $flowDetails = json_decode($flows[0]->flow_details);
        $selectedLesson = !empty($flowDetails->selectedLesson) ? $flowDetails->selectedLesson : [];
        if ($flows && (empty($selectedLesson) || $lesson_id == $selectedLesson || $selectedLesson === 'any')) {
            Flow::execute('MasterStudyLms', static::COMPLETE_LESSON, $finalData, $flows);
        }
    }

    public static function handleQuizComplete($user_id, $quiz_id, $user_quiz_progress)
    {
        $flows = Flow::exists('MasterStudyLms', static::PASSED_QUIZ);
        if (!$flows) {
            return;
        }

        $userInfo = MasterStudyLmsHelper::getUserInfo($user_id);
        $quizDetails = MasterStudyLmsHelper::getQuizDetails($quiz_id);

        $flowDetails = json_decode($flows[0]->flow_details);
        $selectedCourse = !empty($flowDetails->selectedCourse) ? $flowDetails->selectedCourse : [];
        $courseDetails = MasterStudyLmsHelper::getCourseDetail($selectedCourse);

        $finalData = [
            'user_id'            => $user_id,
            'course_id'          => $selectedCourse,
            'course_title'       => $courseDetails[0]->post_title,
            'course_description' => $courseDetails[0]->post_content,
            'quiz_id'            => $quiz_id,
            'quiz_title'         => $quizDetails[0]->post_title,
            'quiz_description'   => $quizDetails[0]->post_content,
            'first_name'         => $userInfo['first_name'],
            'last_name'          => $userInfo['last_name'],
            'nickname'           => $userInfo['nickname'],
            'avatar_url'         => $userInfo['avatar_url'],
            'user_email'         => $userInfo['user_email'],
        ];

        $selectedQuiz = !empty($flowDetails->selectedQuiz) ? $flowDetails->selectedQuiz : [];

        if ((empty($selectedQuiz) || $quiz_id == $selectedQuiz || $selectedQuiz === 'any')) {
            Flow::execute('MasterStudyLms', static::PASSED_QUIZ, $finalData, $flows);
        }
    }

    public static function handleQuizFailed($user_id, $quiz_id, $user_quiz_progress)
    {
        $flows = Flow::exists('MasterStudyLms', static::FAILED_QUIZ);
        if (!$flows) {
            return;
        }

        $userInfo = MasterStudyLmsHelper::getUserInfo($user_id);
        $quizDetails = MasterStudyLmsHelper::getQuizDetails($quiz_id);

        $flowDetails = json_decode($flows[0]->flow_details);
        $selectedCourse = !empty($flowDetails->selectedCourse) ? $flowDetails->selectedCourse : [];
        $courseDetails = MasterStudyLmsHelper::getCourseDetail($selectedCourse);

        $finalData = [
            'user_id'            => $user_id,
            'course_id'          => $selectedCourse,
            'course_title'       => $courseDetails[0]->post_title,
            'course_description' => $courseDetails[0]->post_content,
            'quiz_id'            => $quiz_id,
            'quiz_title'         => $quizDetails[0]->post_title,
            'quiz_description'   => $quizDetails[0]->post_content,
            'first_name'         => $userInfo['first_name'],
            'last_name'          => $userInfo['last_name'],
            'nickname'           => $userInfo['nickname'],
            'avatar_url'         => $userInfo['avatar_url'],
            'user_email'         => $userInfo['user_email'],
        ];

        $selectedQuiz = !empty($flowDetails->selectedQuiz) ? $flowDetails->selectedQuiz : [];

        if ((empty($selectedQuiz) || $quiz_id == $selectedQuiz || $selectedQuiz === 'any')) {
            Flow::execute('MasterStudyLms', static::FAILED_QUIZ, $finalData, $flows);
        }
    }

    // when edit course

    public static function getAllCourseEdit()
    {
        $allCourse = MasterStudyLmsHelper::getAllCourse();
        $allCourse = array_merge([[
            'id'    => 'any',
            'title' => __('Any Course', 'bit-integrations-pro')
        ]], $allCourse);
        wp_send_json_success($allCourse);
    }

    public static function getAllDistributionEdit()
    {
        wp_send_json_success(MasterStudyLmsHelper::getAllDistribution());
    }

    public static function getAllLessonEdit()
    {
        $allLesson = MasterStudyLmsHelper::getAllLesson();
        $allLesson = array_merge([[
            'id'    => 'any',
            'title' => __('Any Lesson', 'bit-integrations-pro')
        ]], $allLesson);
        wp_send_json_success($allLesson);
    }
}
