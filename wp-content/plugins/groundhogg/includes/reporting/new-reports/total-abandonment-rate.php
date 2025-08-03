<?php

namespace Groundhogg\Reporting\New_Reports;


use Groundhogg\Funnel;
use function Groundhogg\get_db;

class Total_Abandonment_Rate extends Base_Funnel_Quick_Stat_Report {

	/**
	 * Query the results
	 *
	 * @param $start int
	 * @param $end   int
	 *
	 * @return mixed
	 */
	protected function query( $start, $end ) {

		$conversion_steps       = $this->get_funnel()->get_conversion_step_ids();
		$num_contacts_converted = $this->get_num_contacts_by_step( $conversion_steps, $start, $end );

		return $this->get_num_contacts_by_step( $this->get_funnel()->get_entry_step_ids(), $start, $end ) - $num_contacts_converted;

	}

	/**
	 * Query the vs results
	 *
	 * So we are comparing to the number of contacts which completed the first step
	 *
	 * @param $start
	 * @param $end
	 *
	 * @return mixed
	 */
	protected function query_vs( $start, $end ) {
		return $this->get_num_contacts_by_step( $this->get_funnel()->get_entry_step_ids(), $start, $end );
	}

	/**
	 * Override the arrow props
	 *
	 * @param int $current_data
	 * @param int $compare_data
	 *
	 * @return array
	 */
	protected function get_arrow_properties( $current_data, $compare_data ) {
		$direction = '';
		$color     = '';

		if ( $current_data < $compare_data ) {
			$direction = 'down';
			$color     = 'green';
		} else if ( $current_data > $compare_data ) {
			$direction = 'up';
			$color     = 'red';
		}

		return [
			'direction' => $direction,
			'color'     => $color,
		];
	}

}
