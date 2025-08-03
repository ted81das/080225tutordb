<?php

namespace BitApps\BTCBI_PRO\Triggers\ThriveApprentice;

class ThriveApprenticeHelper
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
                'fieldName' => __('Email', 'bit-integrations-pro'),
            ],
        ];

        if ($id == 1) {
            $fields = [
                'Course Id' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro')
                ],
                'Course Title' => (object) [
                    'fieldKey'  => 'course_title',
                    'fieldName' => __('Course Title', 'bit-integrations-pro')
                ],
            ];

            $fields = array_merge($userFields, $fields);
        } elseif ($id == 2) {
            $fields = [
                'Lesson Id' => (object) [
                    'fieldKey'  => 'lesson_id',
                    'fieldName' => __('Lesson ID', 'bit-integrations-pro')
                ],
                'Lesson Title' => (object) [
                    'fieldKey'  => 'lesson_title',
                    'fieldName' => __('Lesson Title', 'bit-integrations-pro')
                ],
            ];

            $fields = array_merge($userFields, $fields);
        } elseif ($id == 3) {
            $fields = [
                'Module Id' => (object) [
                    'fieldKey'  => 'module_id',
                    'fieldName' => __('Module ID', 'bit-integrations-pro')
                ],
                'Module Title' => (object) [
                    'fieldKey'  => 'module_title',
                    'fieldName' => __('Module Title', 'bit-integrations-pro')
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

    public static function getAllCourse()
    {
        $allCourse = [];
        if (!\function_exists('tva_get_courses')) {
            return [];
        }
        $courses = tva_get_courses(['published' => true]);

        foreach ($courses as $course) {
            $allCourse[] = [
                'id'    => $course->term_id,
                'title' => $course->name,
            ];
        }

        return $allCourse;
    }

    public static function getAllLesson()
    {
        $args = [
            'post_type'      => 'tva_lesson',
            'posts_per_page' => 999,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ];
        $allLessonPost = get_posts($args);
        foreach ($allLessonPost as $key => $value) {
            $allLesson[] = [
                'id'    => $value->ID,
                'title' => $value->post_title,
            ];
        }

        return $allLesson;
    }

    public static function getAllModule()
    {
        $args = [
            'post_type'      => 'tva_module',
            'posts_per_page' => 999,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ];
        $allModulePost = get_posts($args);
        foreach ($allModulePost as $key => $value) {
            $allModule[] = [
                'id'    => $value->ID,
                'title' => $value->post_title,
            ];
        }

        return $allModule;
    }
}
