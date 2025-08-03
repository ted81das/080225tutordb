<?php
/**
 * Quiz Importer
 *
 * @package TutorPro\Tools
 * @author  Themeum<support@themeum.com>
 * @link    https://themeum.com
 * @since   3.6.0
 */

namespace TutorPro\Tools;

use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use TUTOR\Quiz;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Importer Class.
 */
class QuizImporter {

	/**
	 * Quiz Class Instance.
	 *
	 * @since 3.6.0
	 */
	private $quiz;

	/**
	 * Quiz Importer Class Constructor.
	 */
	public function __construct() {
		$this->quiz = new Quiz();
	}

	/**
	 * Prepares meta data of quiz post type.
	 *
	 * @since 3.6.0
	 *
	 * @param array $quiz_meta the quiz meta data to prepare.
	 *
	 * @return array
	 */
	public function prepare_quiz_meta( $quiz_meta ) {
		$quiz_meta = array_map( fn( $val ) => $val[0], $quiz_meta );

		Importer::reset_post_data( 'content_drip_settings' );
		if ( isset( $quiz_meta['_content_drip_settings'] ) ) {
			$_POST['content_drip_settings'] = $quiz_meta['_content_drip_settings'];
		}

		Importer::reset_post_data( 'quiz_option' );
		if ( isset( $quiz_meta[ QUIZ::META_QUIZ_OPTION ] ) ) {
			$_POST['quiz_option'] = $quiz_meta[ QUIZ::META_QUIZ_OPTION ];
		}
	}

	/**
	 * Flatten all nested quiz question and answer data to a single array.
	 *
	 * @since 3.6.0
	 *
	 * @param array $data the quiz question answer data to flatten.
	 *
	 * @return array
	 */
	public function flatten_quiz_question_answer( $data ) {
		$flatten_content = array();

		if ( $data ) {
			foreach ( $data as $post_id => $question_answer ) {
				$questions = array_column( $question_answer, 'question' );
				$answers   = array_column( $question_answer, 'answers' );
				$answers   = array_merge( ...$answers );
				foreach ( $questions as $questions_data ) {
					$questions_data['quiz_id']     = $post_id;
					$flatten_content['question'][] = $questions_data;
				}

				foreach ( $answers as $answers_data ) {
					$flatten_content['answers'][] = $answers_data;
				}
			}
		}

		return $flatten_content;
	}

	/**
	 * Prepare quiz question answer data for bulk insertion.
	 *
	 * @since 3.6.0
	 *
	 * @param array $quiz_questions_answers the quiz question answer data to prepare.
	 *
	 * @return array
	 */
	private function prepare_quiz_questions_answers( $quiz_questions_answers ) {
		$questions       = $quiz_questions_answers['question'];
		$answers         = $quiz_questions_answers['answers'];
		$final_questions = array();
		$final_answers   = array();

		$question_types = array(
			'single_choice',
			'multiple_choice',
			'ordering',
			'matching',
			'image_matching',
			'image_answering',
			'fill_in_the_blank',
			'true_false',
		);

		foreach ( $questions as $question ) {
			$rules = array(
				'quiz_id'           => 'required|numeric',
				'question_mark'     => 'required|numeric',
				'question_title'    => 'required',
				'question_type'     => 'required|match_string:' . implode( ',', $question_types ),
				'question_settings' => 'required',
			);

			$validate = ValidationHelper::validate( $rules, $question );

			if ( ! $validate->success ) {
				continue;
			}

			$question['question_title'] = addslashes( $question['question_title'] );

			if ( isset( $question['answer_explanation'] ) ) {
				$question['answer_explanation'] = addslashes( $question['answer_explanation'] );
			}

			$question_settings = $question['question_settings'];

			if ( ! in_array( $question_settings['question_type'], $question_types ) ) {
				continue;
			}

			$question['question_settings'] = maybe_serialize( $question_settings );

			if ( isset( $question['question_id'] ) ) {
				unset( $question['question_id'] );
			}

			array_push( $final_questions, $question );
		}

		foreach ( $answers as $answer ) {
			$rules = array(
				'belongs_question_id'   => 'required|numeric',
				'answer_title'          => 'required',
				'question_type'         => '',
				'belongs_question_type' => 'required|match_string:' . implode( ',', $question_types ),
			);

			$validate = ValidationHelper::validate( $rules, $answer );

			if ( ! $validate->success ) {
				continue;
			}

			if ( isset( $question['answer_title'] ) ) {
				$question['answer_title'] = addslashes( $question['answer_title'] );
			}

			if ( $answer['image_url'] ) {
				$upload_data = Importer::url_upload_file( $answer['image_url' ] );

				if ( ! is_wp_error( $upload_data ) ) {
					$answer['image_id'] = $upload_data['id'];
				}
			}

			unset( $answer['image_url'] );

			if ( isset( $answer['answer_id'] ) ) {
				unset( $answer['answer_id'] );
			}

			array_push( $final_answers, $answer );
		}

		return array(
			'question' => $final_questions,
			'answers'  => $final_answers,
		);
	}


	/**
	 * Bulk save quiz question and answer into database.
	 *
	 * @since 3.6.0
	 *
	 * @param array $quiz_questions_answers the quiz question answers to save.
	 *
	 * @return bool|\WP_Error
	 */
	public function save_quiz_questions_answers( $quiz_questions_answers ) {
		global $wpdb;

		$result = true;

		$table_question = "{$wpdb->prefix}tutor_quiz_questions";
		$table_answer   = "{$wpdb->prefix}tutor_quiz_question_answers";

		$questions             = $quiz_questions_answers['question'];
		$previous_question_ids = array_column( $questions, 'question_id' );

		$quiz_questions_answers = $this->prepare_quiz_questions_answers( $quiz_questions_answers );

		$answers   = $quiz_questions_answers['answers'];
		$questions = $quiz_questions_answers['question'];

		try {
			$result = QueryHelper::insert_multiple_rows( $table_question, $questions, false, false );
			if ( $result ) {
				$question_ids = $wpdb->get_results(
					"SELECT question_id FROM {$table_question} WHERE question_id >= LAST_INSERT_ID()",
					'ARRAY_N'
				);
				// Flatten ids.
				$question_ids = array_merge( ...$question_ids );
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'db_insert_fail', __( 'Error inserting quiz questions', 'tutor-pro' ), $e->getMessage() );
		}

		if ( $question_ids && $previous_question_ids ) {
			$question_ids = array_combine( $previous_question_ids, $question_ids );
		}

		if ( $answers && $question_ids ) {
			$answers = array_map(
				function ( $val ) use ( $question_ids ) {
					$val['belongs_question_id'] = $question_ids[ $val['belongs_question_id'] ];
					return $val;
				},
				$answers
			);
		}

		try {
			$result = QueryHelper::insert_multiple_rows( $table_answer, $answers, false, false );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'db_insert_fail', __( 'Error inserting quiz questions', 'tutor-pro' ), $e->getMessage() );
		}

		return $result;
	}
}
