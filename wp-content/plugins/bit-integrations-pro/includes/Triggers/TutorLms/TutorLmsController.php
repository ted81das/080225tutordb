<?php

namespace BitApps\BTCBI_PRO\Triggers\TutorLms;

use BitCode\FI\Flow\Flow;

final class TutorLmsController
{
    public static function info()
    {
        $plugin_path = 'tutor/tutor.php';

        return [
            'name'           => 'Tutor LMS',
            'title'          => __('Tutor LMS - eLearning and online course solution', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'tutor-pro/tutor-pro.php',
            'type'           => 'form',
            'is_active'      => \function_exists('tutor'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'tutorlms/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'tutorlms/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!\function_exists('tutor')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Tutor LMS'));
        }

        $tutor_action = [];
        $types = [
            __('Course-Enroll', 'bit-integrations-pro'),
            __('User attempts(submit) a quiz', 'bit-integrations-pro'),
            __('Completed a lesson', 'bit-integrations-pro'),
            __('Complete a course', 'bit-integrations-pro'),
            __('User achieves a targeted percentage on a quiz', 'bit-integrations-pro')
        ];

        foreach ($types as $index => $type) {
            $tutor_action[] = (object) [
                'id'    => $index + 1,
                'title' => $type,
            ];
        }
        wp_send_json_success($tutor_action);
    }

    public function get_a_form($data)
    {
        if (!\function_exists('tutor')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Tutor LMS'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Trigger type doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = self::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Trigger doesn\'t exists any field', 'bit-integrations-pro'));
        }

        if ($data->id == 1) {
            $courses = [];

            $courseList = get_posts([
                'post_type'   => 'courses',
                'post_status' => 'publish',
                'numberposts' => -1
            ]);

            $courses[] = [
                'course_id'    => 'any',
                'course_title' => __('Any Course', 'bit-integrations-pro'),
            ];

            foreach ($courseList as $key => $val) {
                $courses[] = [
                    'course_id'    => $val->ID,
                    'course_title' => $val->post_title,
                ];
            }
            $responseData['courses'] = $courses;
        } elseif ($data->id == 2) {
            $quizzes = [];

            $allQuiz = get_posts([
                'post_type'   => 'tutor_quiz',
                'post_status' => 'publish',
                'numberposts' => -1
            ]);

            $quizzes[] = [
                'quiz_id'    => 'any',
                'quiz_title' => __('Any Quiz', 'bit-integrations-pro'),
            ];

            foreach ($allQuiz as $key => $val) {
                $quizzes[] = [
                    'quiz_id'    => $val->ID,
                    'quiz_title' => $val->post_title,
                ];
                $responseData['quizzes'] = $quizzes;
            }
        } elseif ($data->id == 3) {
            $lessons = [];

            $lessonList = get_posts([
                'post_type'   => 'lesson',
                'post_status' => 'publish',
                'numberposts' => -1
            ]);

            $lessons[] = [
                'lesson_id'    => 'any',
                'lesson_title' => __('Any Lesson', 'bit-integrations-pro'),
            ];

            foreach ($lessonList as $key => $val) {
                $lessons[] = [
                    'lesson_id'    => $val->ID,
                    'lesson_title' => $val->post_title,
                ];
            }
            $responseData['lessons'] = $lessons;
        } elseif ($data->id == 4) {
            $courses = [];

            $courseList = get_posts([
                'post_type'   => 'courses',
                'post_status' => 'publish',
                'numberposts' => -1
            ]);

            $courses[] = [
                'course_id'    => 'any',
                'course_title' => __('Any Course', 'bit-integrations-pro'),
            ];

            foreach ($courseList as $key => $val) {
                $courses[] = [
                    'course_id'    => $val->ID,
                    'course_title' => $val->post_title,
                ];
            }
            $responseData['courses'] = $courses;
        } elseif ($data->id == 5) {
            $quizzes = [];

            $allQuiz = get_posts([
                'post_type'   => 'tutor_quiz',
                'post_status' => 'publish',
                'numberposts' => -1
            ]);

            $quizzes[] = [
                'quiz_id'    => 'any',
                'quiz_title' => __('Any Quiz', 'bit-integrations-pro'),
            ];

            foreach ($allQuiz as $key => $val) {
                $quizzes[] = [
                    'quiz_id'    => $val->ID,
                    'quiz_title' => $val->post_title,
                ];
            }
            $responseData['quizzes'] = $quizzes;

            $list = [
                'equal_to'           => 'Equal to',
                'not_equal_to'       => 'Not equal to',
                'less_than'          => 'Less than',
                'greater_than'       => 'Greater than',
                'greater_than_equal' => 'Greater than or equal',
                'less_than_equal'    => 'Less than or equal',
            ];
            $percentageCondition = [];

            foreach ($list as $key => $value) {
                $percentageCondition[] = [
                    'condition_id'    => $key,
                    'condition_title' => $value
                ];
            }
            $responseData['percentageCondition'] = $percentageCondition;
        }

        $responseData['fields'] = $fields;
        wp_send_json_success($responseData);
    }

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
        if ($id == 1) {
            $entity = 'course-enroll';
        } elseif ($id == 2) {
            $entity = 'quiz-attempt';
        } elseif ($id == 3) {
            $entity = 'lesson-completed';
        } elseif ($id == 4) {
            $entity = 'course-completed';
        } elseif ($id == 5) {
            $entity = 'targeted-percentage';
        }

        if ($entity === 'course-enroll') {
            $fields = [
                'Course ID' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro')
                ],
                'Course Title' => (object) [
                    'fieldKey'  => 'course_title',
                    'fieldName' => __('Course Title', 'bit-integrations-pro'),
                    'required'  => true
                ],
                'Course Author' => (object) [
                    'fieldKey'  => 'course_author',
                    'fieldName' => __('Course Author', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Student ID' => (object) [
                    'fieldKey'  => 'student_id',
                    'fieldName' => __('Student ID', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Student Name' => (object) [
                    'fieldKey'  => 'student_name',
                    'fieldName' => __('Student Name', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Student First Name' => (object) [
                    'fieldKey'  => 'student_first_name',
                    'fieldName' => __('Student First Name', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Student last Name' => (object) [
                    'fieldKey'  => 'student_last_name',
                    'fieldName' => __('Student Last Name', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Student Email' => (object) [
                    'fieldKey'  => 'student_email',
                    'fieldName' => __('Student Email', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Maximum Student' => (object) [
                    'fieldKey'  => 'maximum_students',
                    'fieldName' => __('Maximum Student', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Course Duration' => (object) [
                    'fieldKey'  => '_course_duration',
                    'fieldName' => __('Course Duration', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Tutor Course Level' => (object) [
                    'fieldKey'  => '_tutor_course_level',
                    'fieldName' => __('Tutor Course Level', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Tutor Course Benifits' => (object) [
                    'fieldKey'  => '_tutor_course_benefits',
                    'fieldName' => __('Tutor Course Benifits', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Tutor Course Requirements' => (object) [
                    'fieldKey'  => '_tutor_course_requirements',
                    'fieldName' => __('Tutor Course Requirements', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Tutor Course Material Includes' => (object) [
                    'fieldKey'  => '_tutor_course_material_includes',
                    'fieldName' => __('Tutor Course Material Includes', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Tutor Course Product ID' => (object) [
                    'fieldKey'  => '_tutor_course_product_id',
                    'fieldName' => __('Tutor Course Product ID', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Tutor Course Price Type' => (object) [
                    'fieldKey'  => '_tutor_course_price_type',
                    'fieldName' => __('Tutor Course Price Type', 'bit-integrations-pro'),
                    'required'  => false
                ],
            ];
        } elseif ($entity === 'quiz-attempt' || $entity === 'targeted-percentage') {
            $fields = [
                'Attempt ID' => (object) [
                    'fieldKey'  => 'attempt_id',
                    'fieldName' => __('Attempt ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course ID' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Quiz ID' => (object) [
                    'fieldKey'  => 'quiz_id',
                    'fieldName' => __('Quiz ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'User ID' => (object) [
                    'fieldKey'  => 'user_id',
                    'fieldName' => __('User Id', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Total Questions' => (object) [
                    'fieldKey'  => 'total_questions',
                    'fieldName' => __('Total Questions', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Total Answered Questions' => (object) [
                    'fieldKey'  => 'total_answered_questions',
                    'fieldName' => __('Total Answered Questions', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Total Marks' => (object) [
                    'fieldKey'  => 'total_marks',
                    'fieldName' => __('Total Marks', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Total Marks' => (object) [
                    'fieldKey'  => 'total_marks',
                    'fieldName' => __('Total Marks', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Earn Marks' => (object) [
                    'fieldKey'  => 'earned_marks',
                    'fieldName' => __('Earn Marks', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Attempt Started At' => (object) [
                    'fieldKey'  => 'attempt_started_at',
                    'fieldName' => __('Attempt Started At', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Attempt Ended At' => (object) [
                    'fieldKey'  => 'attempt_ended_at',
                    'fieldName' => __('Attempt Ended At', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Passing Grade' => (object) [
                    'fieldKey'  => 'passing_grade',
                    'fieldName' => __('Passing Grade', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Result Status(Passed/Failed)' => (object) [
                    'fieldKey'  => 'result_status',
                    'fieldName' => __('Result Status(Passed/Failed)', 'bit-integrations-pro'),
                    'required'  => false,
                ],
            ];

            if ($entity === 'targeted-percentage') {
                $fieldsTmp = [
                    'Achived Status' => (object) [
                        'fieldKey'  => 'achived_status',
                        'fieldName' => __('Achived Status', 'bit-integrations-pro'),
                        'required'  => false,
                    ],
                ];
                $fields = $fields + $fieldsTmp;
            }
        } elseif ($entity === 'lesson-completed') {
            $fields = [
                'Lesson ID' => (object) [
                    'fieldKey'  => 'lesson_id',
                    'fieldName' => __('Lesson ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Lesson Title' => (object) [
                    'fieldKey'  => 'lesson_title',
                    'fieldName' => __('Lesson Title', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Lesson Description' => (object) [
                    'fieldKey'  => 'lesson_description',
                    'fieldName' => __('Lesson Description', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Lesson URL' => (object) [
                    'fieldKey'  => 'lesson_url',
                    'fieldName' => __('Lesson URL', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Topic ID' => (object) [
                    'fieldKey'  => 'topic_id',
                    'fieldName' => __('Topic ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Topic Title' => (object) [
                    'fieldKey'  => 'topic_title',
                    'fieldName' => __('Topic Title', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Topic Description' => (object) [
                    'fieldKey'  => 'topic_description',
                    'fieldName' => __('Topic Description', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Topic URL' => (object) [
                    'fieldKey'  => 'topic_url',
                    'fieldName' => __('Topic URL', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course ID' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course Name' => (object) [
                    'fieldKey'  => 'course_name',
                    'fieldName' => __('Course Name', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course Description' => (object) [
                    'fieldKey'  => 'course_description',
                    'fieldName' => __('Course Description', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course URL' => (object) [
                    'fieldKey'  => 'course_url',
                    'fieldName' => __('Course URL', 'bit-integrations-pro'),
                    'required'  => false,
                ],
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
        } elseif ($entity === 'course-completed') {
            $fields = [
                'Course ID' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course Name' => (object) [
                    'fieldKey'  => 'course_name',
                    'fieldName' => __('Course Name', 'bit-integrations-pro'),
                    'required'  => false,
                ],
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

    public static function metaboxFields($module)
    {
        $fileTypes = [
            'image',
            'image_upload',
            'file_advanced',
            'file_upload',
            'single_image',
            'file',
            'image_advanced',
            'video'
        ];

        $metaboxFields = [];
        $metaboxUploadFields = [];

        if (\function_exists('rwmb_meta')) {
            if ($module === 'customer') {
                $field_registry = rwmb_get_registry('field');
                $meta_boxes = $field_registry->get_by_object_type($object_type = 'user');
                $metaFields = isset($meta_boxes['user']) && \is_array($meta_boxes['user']) ? array_values($meta_boxes['user']) : [];
            } else {
                $metaFields = array_values(rwmb_get_object_fields($module));
            }
            foreach ($metaFields as $index => $field) {
                if (!\in_array($field['type'], $fileTypes)) {
                    $metaboxFields[$index] = (object) [
                        'fieldKey'  => $field['id'],
                        'fieldName' => 'Metabox Field - ' . $field['name'],
                        'required'  => $field['required'],
                    ];
                } else {
                    $metaboxUploadFields[$index] = (object) [
                        'fieldKey'  => $field['id'],
                        'fieldName' => 'Metabox Field - ' . $field['name'],
                        'required'  => $field['required'],
                    ];
                }
            }
        }

        return ['meta_fields' => $metaboxFields, 'upload_fields' => $metaboxUploadFields];
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

    public static function handle_course_enroll($course_id, $enrollment_id)
    {
        $flows = Flow::exists('TutorLms', 1);
        $flows = self::flowFilter($flows, 'selectedCourse', $course_id);
        if (!$flows) {
            return;
        }

        $author_id = get_post_field('post_author', $course_id);
        $author_name = get_the_author_meta('display_name', $author_id);
        $student_id = get_post_field('post_author', $enrollment_id);
        $userData = get_userdata($student_id);
        $result_student = [];

        if ($student_id && $userData) {
            $result_student = [
                'student_id'         => $student_id,
                'student_name'       => $userData->display_name,
                'student_first_name' => $userData->user_firstname,
                'student_last_name'  => $userData->user_lastname,
                'student_email'      => $userData->user_email,
            ];
        }

        $result_course = [];
        $course = get_post($course_id);
        $result_course = [
            'course_id'     => $course->ID,
            'course_title'  => $course->post_title,
            'course_author' => $author_name,
        ];
        $result = $result_student + $result_course;

        $courseInfo = get_post_meta($course_id);
        $course_temp = [];
        foreach ($courseInfo as $key => $val) {
            if (\is_array($val)) {
                $val = maybe_unserialize($val[0]);
            }
            $course_temp[$key] = $val;
        }
        $result = $result + $course_temp;

        $result['post_id'] = $enrollment_id;

        Flow::execute('TutorLms', 1, $result, $flows);
    }

    public static function handleQuizAttempt($attempt_id)
    {
        $flows = Flow::exists('TutorLms', 2);

        $attempt = tutor_utils()->get_attempt($attempt_id);

        $quiz_id = $attempt->quiz_id;

        $flows = self::flowFilter($flows, 'selectedQuiz', $quiz_id);
        if (!$flows) {
            return;
        }

        if ('tutor_quiz' !== get_post_type($quiz_id)) {
            return;
        }

        if ('attempt_ended' != $attempt->attempt_status && 'review_required' != $attempt->attempt_status) {
            return;
        }

        $attempt_details = [];
        $attempt_info = [];

        foreach ($attempt as $key => $val) {
            if (\is_array($val)) {
                $val = maybe_unserialize($val[0]);
            }
            $attempt_details[$key] = maybe_unserialize($val);
        }
        if (\array_key_exists('attempt_info', $attempt_details)) {
            $attempt_info_tmp = $attempt_details['attempt_info'];
            unset($attempt_details['attempt_info']);

            foreach ($attempt_info_tmp as $key => $val) {
                $attempt_info[$key] = maybe_unserialize($val);
            }

            $attempt_details['passing_grade'] = $attempt_info['passing_grade'];
            $totalMark = $attempt_details['total_marks'];
            $earnMark = $attempt_details['earned_marks'];
            $passGrade = $attempt_details['passing_grade'];
            $mark = $totalMark * ($passGrade / 100);

            if ('review_required' == $attempt->attempt_status) {
                $attempt_details['result_status'] = 'Pending';
            } elseif ($earnMark >= $mark) {
                $attempt_details['result_status'] = 'Passed';
            } else {
                $attempt_details['result_status'] = 'Failed';
            }
        }

        $attempt_details['post_id'] = $attempt_id;

        Flow::execute('TutorLms', 2, $attempt_details, $flows);
    }

    public static function handleQuizTarget($attempt_id)
    {
        $flows = Flow::exists('TutorLms', 5);

        $attempt = tutor_utils()->get_attempt($attempt_id);

        $quiz_id = $attempt->quiz_id;

        $flows = self::flowFilter($flows, 'selectedQuiz', $quiz_id);
        if (!$flows) {
            return;
        }

        if ('tutor_quiz' !== get_post_type($quiz_id)) {
            return;
        }

        if ('attempt_ended' !== $attempt->attempt_status) {
            return;
        }

        $attempt_details = [];
        $attempt_info = [];

        foreach ($attempt as $key => $val) {
            if (\is_array($val)) {
                $val = maybe_unserialize($val[0]);
            }
            $attempt_details[$key] = maybe_unserialize($val);
        }

        if (\array_key_exists('attempt_info', $attempt_details)) {
            $attempt_info_tmp = $attempt_details['attempt_info'];
            unset($attempt_details['attempt_info']);

            foreach ($attempt_info_tmp as $key => $val) {
                $attempt_info[$key] = maybe_unserialize($val);
            }

            $attempt_details['passing_grade'] = $attempt_info['passing_grade'];
            $totalMark = $attempt_details['total_marks'];
            $earnMark = $attempt_details['earned_marks'];
            $passGrade = $attempt_details['passing_grade'];
            $mark = $totalMark * ($passGrade / 100);

            if ($earnMark >= $mark) {
                $attempt_details['result_status'] = 'Passed';
            } else {
                $attempt_details['result_status'] = 'Failed';
            }
            foreach ($flows as $flow) {
                $flow_details = $flow->flow_details;
                $reqPercent = $flow_details->requiredPercent;
                $mark = $totalMark * ($reqPercent / 100);
                $condition = $flow_details->selectedCondition;
                $achived = self::checkedAchived($condition, $mark, $earnMark);
                $attempt_details['achived_status'] = $achived;

                $attempt_details['post_id'] = $attempt_id;

                Flow::execute('TutorLms', 5, $attempt_details, $flows = [$flow]);
            }
        }
    }

    public static function checkedAchived($condition, $mark, $earnMark)
    {
        $res = 'Not Achived';

        if ($condition === 'equal_to') {
            if ($earnMark == $mark) {
                $res = 'Achived';
            }
        } elseif ($condition === 'not_equal_to') {
            if ($earnMark != $mark) {
                $res = 'Achived';
            }
        } elseif ($condition === 'less_than') {
            if ($earnMark < $mark) {
                $res = 'Achived';
            }
        } elseif ($condition === 'greater_than') {
            if ($earnMark > $mark) {
                $res = 'Achived';
            }
        } elseif ($condition === 'greater_than_equal') {
            if ($earnMark >= $mark) {
                $res = 'Achived';
            }
        } elseif ($condition === 'less_than_equal') {
            if ($earnMark <= $mark) {
                $res = 'Achived';
            }
        }

        return $res;
    }

    public static function handleLessonComplete($lesson_id)
    {
        $flows = Flow::exists('TutorLms', 3);
        $flows = self::flowFilter($flows, 'selectedLesson', $lesson_id);

        if (!$flows) {
            return;
        }
        $lessonPost = get_post($lesson_id);

        $lessonData = [];
        $lessonData = [
            'lesson_id'          => $lessonPost->ID,
            'lesson_title'       => $lessonPost->post_title,
            'lesson_description' => $lessonPost->post_content,
            'lesson_url'         => $lessonPost->guid,
        ];

        $user = self::getUserInfo(get_current_user_id());
        $current_user = [
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        $courseData = [];
        $topicPost = get_post($lessonPost->post_parent);
        $topicData = [
            'topic_id'          => $topicPost->ID,
            'topic_title'       => $topicPost->post_title,
            'topic_description' => $topicPost->post_content,
            'topic_url'         => $topicPost->guid,
        ];
        $coursePost = get_post($topicPost->post_parent);
        $courseData = [
            'course_id'          => $coursePost->ID,
            'course_name'        => $coursePost->post_title,
            'course_description' => $coursePost->post_content,
            'course_url'         => $coursePost->guid,
        ];

        $lessonDataFinal = $lessonData + $topicData + $courseData + $current_user;
        $lessonDataFinal['post_id'] = $lesson_id;
        Flow::execute('TutorLms', 3, $lessonDataFinal, $flows);
    }

    public static function handleCourseComplete($course_id)
    {
        $flows = Flow::exists('TutorLms', 4);
        $flows = self::flowFilter($flows, 'selectedCourse', $course_id);

        if (!$flows) {
            return;
        }

        $coursePost = get_post($course_id);

        $courseData = [];
        $courseData = [
            'course_id'    => $coursePost->ID,
            'course_title' => $coursePost->post_title,
            'course_url'   => $coursePost->guid,
        ];

        $user = self::getUserInfo(get_current_user_id());
        $current_user = [
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        $courseDataFinal = $courseData + $current_user;

        $courseDataFinal['post_id'] = $course_id;
        Flow::execute('TutorLms', 4, $courseDataFinal, $flows);
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
}
