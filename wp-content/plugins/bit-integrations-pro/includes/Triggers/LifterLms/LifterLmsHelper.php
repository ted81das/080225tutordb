<?php

namespace BitApps\BTCBI_PRO\Triggers\LifterLms;

class LifterLmsHelper
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
                'fieldName' => __('Last Name', 'bit-integrations-pro')
            ],
            'Nick Name' => (object) [
                'fieldKey'  => 'nickname',
                'fieldName' => __('Nick Name', 'bit-integrations-pro')
            ],
            'Avatar URL' => (object) [
                'fieldKey'  => 'avatar_url',
                'fieldName' => __('Avatar URL', 'bit-integrations-pro')
            ],
            'Email' => (object) [
                'fieldKey'  => 'user_email',
                'fieldName' => __('Email', 'bit-integrations-pro')
            ],
        ];

        if ($id == 1 || $id == 2 || $id == 3) {
            $fields = [
                'User Id' => (object) [
                    'fieldKey'  => 'user_id',
                    'fieldName' => __('User Id', 'bit-integrations-pro')
                ],
                'Quiz Id' => (object) [
                    'fieldKey'  => 'quiz_id',
                    'fieldName' => __('Quiz ID', 'bit-integrations-pro')
                ],
                'Quiz Title' => (object) [
                    'fieldKey'  => 'quiz_title',
                    'fieldName' => __('Quiz Title', 'bit-integrations-pro')
                ],
            ];

            $fields = array_merge($userFields, $fields);
        } elseif ($id == 4) {
            $fields = [
                'User Id' => (object) [
                    'fieldKey'  => 'user_id',
                    'fieldName' => __('User Id', 'bit-integrations-pro')
                ],
                'Lesson Title' => (object) [
                    'fieldKey'  => 'lesson_title',
                    'fieldName' => __('Lesson Title', 'bit-integrations-pro')
                ],
                'Lesson Id' => (object) [
                    'fieldKey'  => 'lesson_id',
                    'fieldName' => __('Lesson ID', 'bit-integrations-pro')
                ],
            ];
            $fields = array_merge($userFields, $fields);
        } elseif ($id == 5 || $id == 6 || $id == 7) {
            $courseField = [
                'User Id' => (object) [
                    'fieldKey'  => 'user_id',
                    'fieldName' => __('User Id', 'bit-integrations-pro')
                ],
                'Course Title' => (object) [
                    'fieldKey'  => 'course_title',
                    'fieldName' => __('Course Title', 'bit-integrations-pro')
                ],
                'Course Id' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro')
                ],
            ];

            $fields = array_merge($userFields, $courseField);
        } elseif ($id == 8) {
            $membershipField = [
                'User Id' => (object) [
                    'fieldKey'  => 'user_id',
                    'fieldName' => __('User Id', 'bit-integrations-pro')
                ],
                'Membership Title' => (object) [
                    'fieldKey'  => 'membership_title',
                    'fieldName' => __('Membership Title', 'bit-integrations-pro')
                ],
                'Membership Id' => (object) [
                    'fieldKey'  => 'membership_id',
                    'fieldName' => __('Membership ID', 'bit-integrations-pro')
                ],
            ];
            $fields = array_merge($userFields, $membershipField);
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

    public static function getAllQuiz()
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts}
                    WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'llms_quiz' ORDER BY post_title"
            )
        );
    }

    public static function getAllLesson()
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts}
                    WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'lesson' ORDER BY post_title"
            )
        );
    }

    public static function getQuizDetail($quizId)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts}
                WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'llms_quiz' AND {$wpdb->posts}.ID = %d",
                $quizId
            )
        );
    }

    public static function getLessonDetail($lessonId)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts}
                WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'lesson' AND {$wpdb->posts}.ID = %d",
                $lessonId
            )
        );
    }

    public static function getAllCourse()
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts}
                WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'course' ORDER BY post_title"
            )
        );
    }

    public static function getCourseDetail($courseId)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts}
                WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'course' AND {$wpdb->posts}.ID = %d",
                $courseId
            )
        );
    }

    public static function getAllMembership()
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts}
                WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'llms_membership' ORDER BY post_title"
            )
        );
    }

    public static function getMembershipDetail($membershipId)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM {$wpdb->posts}
        WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'llms_membership' AND {$wpdb->posts}.ID = %d",
                $membershipId
            )
        );
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
}

// demo comment
