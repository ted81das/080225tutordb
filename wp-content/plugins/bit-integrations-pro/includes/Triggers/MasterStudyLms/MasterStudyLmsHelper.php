<?php

namespace BitApps\BTCBI_PRO\Triggers\MasterStudyLms;

class MasterStudyLmsHelper
{
    public static function fields($id)
    {
        if (empty($id)) {
            wp_send_json_error(
                __(
                    'Requested parameter is empty',
                    'bit-integrations-pro'
                ),
                400
            );
        }

        $userFields = [
            'First Name' => (object) [
                'fieldKey'  => 'first_name',
                'fieldName' => __('First Name', 'bit-integrations-pro')
            ],
            'Last Name' => (object) [
                'fieldKey'  => 'last_name',
                'fieldName' => __('Last Name', 'bit-integrations-pro'),
            ],
            'Nick Name' => (object) [
                'fieldKey'  => 'nickname',
                'fieldName' => __('Nick Name', 'bit-integrations-pro'),
            ],
            'Avatar URL' => (object) [
                'fieldKey'  => 'avatar_url',
                'fieldName' => __('Avatar URL', 'bit-integrations-pro'),
            ],
            'Email' => (object) [
                'fieldKey'  => 'user_email',
                'fieldName' => __('Email', 'bit-integrations-pro'),
            ],
        ];

        if ($id == 1 || $id == 3) {
            $fields = [
                'Course Id' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro'),
                ],
                'Course Title' => (object) [
                    'fieldKey'  => 'course_title',
                    'fieldName' => __('Course Title', 'bit-integrations-pro'),
                ],
                'Course Description' => (object) [
                    'fieldKey'  => 'course_description',
                    'fieldName' => __('Course Description', 'bit-integrations-pro'),
                ],
            ];

            $fields = array_merge($userFields, $fields);
        } elseif ($id == 4 || $id == 5) {
            $fields = [
                'Quiz Id' => (object) [
                    'fieldKey'  => 'quiz_id',
                    'fieldName' => __('Quiz ID', 'bit-integrations-pro'),
                ],
                'Quiz Title' => (object) [
                    'fieldKey'  => 'quiz_title',
                    'fieldName' => __('Quiz Title', 'bit-integrations-pro'),
                ],
                'Quiz Description' => (object) [
                    'fieldKey'  => 'quiz_description',
                    'fieldName' => __('Quiz Description', 'bit-integrations-pro'),
                ],
                'Course Id' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro'),
                ],
                'Course Title' => (object) [
                    'fieldKey'  => 'course_title',
                    'fieldName' => __('Course Title', 'bit-integrations-pro'),
                ],
                'Course Description' => (object) [
                    'fieldKey'  => 'course_description',
                    'fieldName' => __('Course Description', 'bit-integrations-pro'),
                ],
            ];

            $fields = array_merge($userFields, $fields);
        } elseif ($id == 2) {
            $fields = [
                'Lesson Id' => (object) [
                    'fieldKey'  => 'lesson_id',
                    'fieldName' => __('Lesson ID', 'bit-integrations-pro'),
                ],
                'Lesson Title' => (object) [
                    'fieldKey'  => 'lesson_title',
                    'fieldName' => __('Lesson Title', 'bit-integrations-pro'),
                ],
                'Lesson Description' => (object) [
                    'fieldKey'  => 'lesson_description',
                    'fieldName' => __('Lesson Description', 'bit-integrations-pro'),
                ],
            ];

            $fields = array_merge($userFields, $fields);
        } elseif ($id == 6) {
            $fields = [
                'Point Distribution' => (object) [
                    'fieldKey'  => 'distribution',
                    'fieldName' => __('Point Distribution', 'bit-integrations-pro'),
                ],
                'Point Score' => (object) [
                    'fieldKey'  => 'score',
                    'fieldName' => __('Point Score', 'bit-integrations-pro'),
                ],
                'Repeat' => (object) [
                    'fieldKey'  => 'repeat',
                    'fieldName' => __('Repeat', 'bit-integrations-pro'),
                ],
            ];

            $fields = array_merge($userFields, $fields);
        }

        foreach ($fields as $field) {
            $fieldsNew[] = [
                'name'  => $field->fieldKey,
                'type'  => 'text',
                'label' => $field->fieldName,
            ];
        }

        return $fieldsNew;
    }

    public static function getAllCourse()
    {
        $allCourse = [];
        $args = [
            'post_type'      => 'stm-courses',
            'posts_per_page' => 999,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ];
        $courses = get_posts($args);
        foreach ($courses as $key => $value) {
            $allCourse[] = [
                'id'    => $value->ID,
                'title' => $value->post_title,
            ];
        }

        return $allCourse;
    }

    public static function getAllQuiz($courseId)
    {
        global $wpdb;

        if ($courseId === 'any') {
            $query = $wpdb->prepare(
                "SELECT ID, post_title, post_content
            FROM {$wpdb->posts}
            WHERE post_type = %s
            ORDER BY post_title ASC",
                'stm-quizzes'
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT p.ID, p.post_title, p.post_content
            FROM {$wpdb->posts} p
            JOIN {$wpdb->prefix}stm_lms_curriculum_materials cm ON p.ID = cm.post_id
            JOIN {$wpdb->prefix}stm_lms_curriculum_sections cs ON cm.section_id = cs.id
            WHERE p.post_type = %s
            AND cs.course_id = %d
            ORDER BY p.post_title ASC",
                'stm-quizzes',
                absint($courseId)
            );
        }

        $quizzes = $wpdb->get_results($query);

        return $quizzes;
    }

    public static function getCourseDetail($courseId)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title,post_content FROM {$wpdb->posts}
                WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'stm-courses' AND {$wpdb->posts}.ID = %d",
                $courseId
            )
        );
    }

    public static function getQuizDetails($quiz_id)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title,post_content FROM {$wpdb->posts}
                 WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'stm-quizzes' AND {$wpdb->posts}.ID = %d",
                $quiz_id
            )
        );
    }

    public static function getLessonDetail($lessonId)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title,post_content FROM {$wpdb->posts}
        WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'stm-lessons' AND {$wpdb->posts}.ID = %d",
                $lessonId
            )
        );
    }

    public static function getAllLesson()
    {
        $allLesson = [];
        $args = [
            'post_type'      => 'stm-lessons',
            'posts_per_page' => 999,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ];
        $lessons = get_posts($args);
        foreach ($lessons as $key => $value) {
            $allLesson[] = [
                'id'    => $value->ID,
                'title' => $value->post_title,
            ];
        }

        return $allLesson;
    }

    public static function getAllDistribution()
    {
        return [
            [
                'id'    => 'any',
                'title' => __('Any Distribution', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'user_registered',
                'title' => __('Registration', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'course_purchased',
                'title' => __('Course purchase', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'lesson_passed',
                'title' => __('Lesson completion', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'quiz_passed',
                'title' => __('Passing quiz', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'perfect_quiz',
                'title' => __('Passing quiz with 100%', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'assignment_passed',
                'title' => __('Passing assignment', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'certificate_received',
                'title' => __('Course completion', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'group_joined',
                'title' => __('Joining group', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'friends_friendship_accepted',
                'title' => __('Making friends', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'user_registered_affiliate',
                'title' => __('User registered (Affiliate)', 'bit-integrations-pro'),
            ],
            [
                'id'    => 'course_purchased_affiliate',
                'title' => __('Course purchased (Affiliate)', 'bit-integrations-pro'),
            ],
        ];
    }

    public static function getUserInfo($user_id)
    {
        $userInfo = get_userdata($user_id);
        $user = [];
        if ($userInfo) {
            $userData = $userInfo->data;
            $user_meta = get_user_meta($user_id);
            $user = [
                'first_name' => $user_meta['first_name'][0],
                'last_name'  => $user_meta['last_name'][0],
                'user_email' => $userData->user_email,
                'nickname'   => $userData->user_nicename,
                'avatar_url' => get_avatar_url($user_id),
            ];
        }

        return $user;
    }

    public static function prepareFinalData($user_id, $action, $score, $selectedDistribution = null, $isAffiliate = false)
    {
        if ($isAffiliate) {
            $userInfo = MasterStudyLmsHelper::getUserInfo(static::get_affiliate_id($user_id));
            $distribution = array_column(MasterStudyLmsHelper::getAllDistribution(), 'title', 'id');

            $finalData = [
                'score'        => $score * static::affiliate_rate(),
                'distribution' => $distribution[$selectedDistribution]
            ];
        } else {
            $userInfo = MasterStudyLmsHelper::getUserInfo($user_id);
            $finalData = [
                'score'        => $score,
                'distribution' => $action['label']
            ];
        }

        return array_merge([
            'first_name' => $userInfo['first_name'],
            'last_name'  => $userInfo['last_name'],
            'nickname'   => $userInfo['nickname'],
            'avatar_url' => $userInfo['avatar_url'],
            'user_email' => $userInfo['user_email'],
            'repeat'     => $action['repeat'],
        ], $finalData);
    }

    private static function get_affiliate_id($user_id)
    {
        if (! empty($_COOKIE) && ! empty($_COOKIE['affiliate_id'])) {
            if (\intval($_COOKIE['affiliate_id'] !== $user_id)) {
                return \intval($_COOKIE['affiliate_id']);
            }
        }

        return get_user_meta($user_id, 'affiliate_id', true);
    }

    private static function affiliate_rate()
    {
        $options = get_option('stm_lms_point_system_settings', []);

        return (!empty($options['affiliate_points_rate']) ? $options['affiliate_points_rate'] : 10) / 100;
    }
}
