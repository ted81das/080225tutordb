<?php

namespace BitApps\BTCBI_PRO\Triggers\AcademyLms;

use BitCode\FI\Flow\Flow;

// use Academy\Traits\Lessons;

final class AcademyLmsController
{
    public static function info()
    {
        $plugin_path = 'academy/academy.php';

        return [
            'name'           => 'Academy Lms',
            'title'          => __('Academy LMS – eLearning and online course solution for WordPress', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => class_exists('Academy'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'academylms/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'academylms/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function isPluginActive()
    {
        if (!class_exists('Academy')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Academy Lms'));
        }
    }

    public function getAll()
    {
        self::isPluginActive();
        $types = [
            __('User Enrolled in a Course', 'bit-integrations-pro'),
            __('User attempts(submit) a quiz', 'bit-integrations-pro'),
            __('User Complete a Lesson', 'bit-integrations-pro'),
            __('User Complete a Course', 'bit-integrations-pro'),
            __('User achieves a targeted percentage on a quiz', 'bit-integrations-pro')
        ];

        $academy_action = [];
        foreach ($types as $index => $type) {
            $academy_action[] = (object) [
                'id'    => $index + 1,
                'title' => $type,
            ];
        }
        wp_send_json_success($academy_action);
    }

    public function get_a_form($data)
    {
        self::isPluginActive();
        if (empty($data->id)) {
            wp_send_json_error(__('Trigger type doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = self::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Trigger doesn\'t exists any field', 'bit-integrations-pro'));
        }

        if ($data->id == 1 || $data->id == 4) {
            $courses = [];
            $courseList = get_posts([
                'post_type'   => 'academy_courses',
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
                'post_type'   => 'academy_quiz',
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
            $lessonList = \Academy\Traits\Lessons::get_lessons();
            $lessons = [];
            $lessons[] = [
                'lesson_id'    => 'any',
                'lesson_title' => __('Any Lesson', 'bit-integrations-pro'),
            ];

            foreach ($lessonList as $key => $val) {
                $lessons[] = [
                    'lesson_id'    => $val->ID,
                    'lesson_title' => $val->lesson_title,
                ];
            }
            $responseData['lessons'] = $lessons;
        } elseif ($data->id == 5) {
            $quizzes = [];

            $allQuiz = get_posts([
                'post_type'   => 'academy_quiz',
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
                'equal_to'           => __('Equal to', 'bit-integrations-pro'),
                'not_equal_to'       => __('Not equal to', 'bit-integrations-pro'),
                'less_than'          => __('Less than', 'bit-integrations-pro'),
                'greater_than'       => __('Greater than', 'bit-integrations-pro'),
                'greater_than_equal' => __('Greater than or equal', 'bit-integrations-pro'),
                'less_than_equal'    => __('Less than or equal', 'bit-integrations-pro'),
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
                    'fieldName' => __('Course ID', 'bit-integrations-pro'),
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
                'Maximum Student' => (object) [
                    'fieldKey'  => 'academy_course_max_students',
                    'fieldName' => __('Maximum Student', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Course Curriculam' => (object) [
                    'fieldKey'  => 'academy_course_curriculum',
                    'fieldName' => __('Course Curriculam', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Course Duration' => (object) [
                    'fieldKey'  => 'academy_course_duration',
                    'fieldName' => __('Course Duration', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Academy Course Level' => (object) [
                    'fieldKey'  => 'academy_course_difficulty_level',
                    'fieldName' => __('Academy Course Level', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Academy Course Benifits' => (object) [
                    'fieldKey'  => 'academy_course_benefits',
                    'fieldName' => __('Academy Course Benifits', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Academy Course Requirements' => (object) [
                    'fieldKey'  => 'academy_course_requirements',
                    'fieldName' => __('Academy Course Requirements', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Academy Course Material Includes' => (object) [
                    'fieldKey'  => 'academy_course_materials_included',
                    'fieldName' => __('Academy Course Material Includes', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Academy Course Product ID' => (object) [
                    'fieldKey'  => 'academy_course_product_id',
                    'fieldName' => __('Academy Course Product ID', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'Academy Course Price Type' => (object) [
                    'fieldKey'  => 'academy_course_type',
                    'fieldName' => __('Academy Course Price Type', 'bit-integrations-pro'),
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
                'Attempt Info' => (object) [
                    'fieldKey'  => 'attempt_info',
                    'fieldName' => __('Attempt Info', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Result Status(Passed/Failed)' => (object) [
                    'fieldKey'  => 'attempt_status',
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
                'Lesson Status' => (object) [
                    'fieldKey'  => 'lesson_status',
                    'fieldName' => __('Lesson Status', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Quiz ID' => (object) [
                    'fieldKey'  => 'quiz_id',
                    'fieldName' => __('Quiz ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Quiz Title' => (object) [
                    'fieldKey'  => 'quiz_title',
                    'fieldName' => __('Quiz Title', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Quiz Description' => (object) [
                    'fieldKey'  => 'quiz_description',
                    'fieldName' => __('Quiz Description', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Quiz URL' => (object) [
                    'fieldKey'  => 'quiz_url',
                    'fieldName' => __('Quiz URL', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course ID' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course Name' => (object) [
                    'fieldKey'  => 'course_title',
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
                    'fieldName' => __('First Name', 'bit-integrations-pro'),
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
        } elseif ($entity === 'course-completed') {
            $fields = [
                'Course ID' => (object) [
                    'fieldKey'  => 'course_id',
                    'fieldName' => __('Course ID', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Course Title' => (object) [
                    'fieldKey'  => 'course_title',
                    'fieldName' => __('Course Title', 'bit-integrations-pro'),
                    'required'  => false
                ],
                'First Name' => (object) [
                    'fieldKey'  => 'first_name',
                    'fieldName' => __('First Name', 'bit-integrations-pro'),
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

    public static function handle_course_enroll($course_id, $enrollment_id)
    {
        $flows = Flow::exists('AcademyLms', 1);
        $flows = self::flowFilter($flows, 'selectedCourse', $course_id);
        if (!$flows) {
            return;
        }

        $author_id = get_post_field('post_author', $course_id);
        $author_name = get_the_author_meta('display_name', $author_id);

        $student_id = get_post_field('post_author', $enrollment_id);
        $student_name = get_the_author_meta('display_name', $student_id);
        $result_student = [];
        if ($student_id && $student_name) {
            $result_student = [
                'student_id'   => $student_id,
                'student_name' => $student_name,
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
        Flow::execute('AcademyLms', 1, $result, $flows);
    }

    public static function handleQuizAttempt($attempt)
    {
        $flows = Flow::exists('AcademyLms', 2);
        $quiz_id = $attempt->quiz_id;

        $flows = $flows ? self::flowFilter($flows, 'selectedQuiz', $quiz_id) : false;
        if (!$flows || empty($flow)) {
            return;
        }

        if ('academy_quiz' !== get_post_type($quiz_id)) {
            return;
        }

        if ('pending' === $attempt->attempt_status) {
            return;
        }

        $attempt_details = [];
        foreach ($attempt as $key => $val) {
            if (\is_array($val)) {
                $val = maybe_unserialize($val[0]);
            }
            $attempt_details[$key] = maybe_unserialize($val);
        }

        Flow::execute('AcademyLms', 2, $attempt_details, $flows);
    }

    public static function handleQuizTarget($attempt)
    {
        $flows = Flow::exists('AcademyLms', 5);
        $quiz_id = $attempt->quiz_id;

        $flows = $flows ? self::flowFilter($flows, 'selectedQuiz', $quiz_id) : false;
        if (!$flows) {
            return;
        }

        if ('academy_quiz' !== get_post_type($quiz_id)) {
            return;
        }

        if ('pending' === $attempt->attempt_status) {
            return;
        }

        $attempt_details = [];
        foreach ($attempt as $key => $val) {
            if (\is_array($val)) {
                $val = maybe_unserialize($val[0]);
            }
            $attempt_details[$key] = maybe_unserialize($val);
        }
        foreach ($flows as $flow) {
            $flow_details = $flow->flow_details;
            $reqPercent = $flow_details->requiredPercent;
            $mark = $attempt_details['total_marks'] * ($reqPercent / 100);
            $condition = $flow_details->selectedCondition;
            $achived = self::checkedAchived($condition, $mark, $attempt_details['earned_marks']);
            $attempt_details['achived_status'] = $achived;
        }
        Flow::execute('AcademyLms', 5, $attempt_details, $flows);
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

    public static function handleLessonComplete($topic_type, $course_id, $topic_id, $user_id)
    {
        $flows = Flow::exists('AcademyLms', 3);
        $flows = $flows ? self::flowFilter($flows, 'selectedLesson', $topic_id) : false;
        if (!$flows) {
            return;
        }

        $topicData = [];
        if ($topic_type === 'lesson') {
            $lessonPost = \Academy\Traits\Lessons::get_lesson($topic_id);
            $topicData = [
                'lesson_id'          => $lessonPost->ID,
                'lesson_title'       => $lessonPost->lesson_title,
                'lesson_description' => $lessonPost->lesson_content,
                'lesson_status'      => $lessonPost->lesson_status,
            ];
        }

        if ($topic_type === 'quiz') {
            $quiz = get_post($topic_id);
            $topicData = [
                'quiz_id'          => $quiz->ID,
                'quiz_title'       => $quiz->post_title,
                'quiz_description' => $quiz->post_content,
                'quiz_url'         => $quiz->guid,
            ];
        }

        $user = self::getUserInfo($user_id);
        $current_user = [
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'user_email' => $user['user_email'],
            'nickname'   => $user['nickname'],
            'avatar_url' => $user['avatar_url'],
        ];

        $courseData = [];
        $coursePost = get_post($course_id);
        $courseData = [
            'course_id'          => $coursePost->ID,
            'course_title'       => $coursePost->post_title,
            'course_description' => $coursePost->post_content,
            'course_url'         => $coursePost->guid,
        ];

        $lessonDataFinal = $topicData + $courseData + $current_user;
        $lessonDataFinal['post_id'] = $topic_id;
        Flow::execute('AcademyLms', 3, $lessonDataFinal, $flows);
    }

    public static function handleCourseComplete($course_id)
    {
        $flows = Flow::exists('AcademyLms', 4);
        $flows = $flows ? self::flowFilter($flows, 'selectedCourse', $course_id) : false;

        if (!$flows) {
            return;
        }

        $coursePost = get_post($course_id);
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
        Flow::execute('AcademyLms', 4, $courseDataFinal, $flows);
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
