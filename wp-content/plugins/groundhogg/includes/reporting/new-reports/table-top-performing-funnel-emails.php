<?php

namespace Groundhogg\Reporting\New_Reports;


use function Groundhogg\percentage;

class Table_Top_Performing_Funnel_Emails extends Base_Funnel_Email_Performance_Table_Report {

	/**
	 * Sort by multiple args
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 */
	public function sort( $a, $b ) {
		if ( $a['sent'] === $b['sent'] ) {

			if ( $a['opened'] === $b['opened'] ) {
				return $b['clicked'] - $a['clicked'];
			}

			return $b['opened'] - $a['opened'];
		}

		return $b['sent'] - $a['sent'];
	}

	protected function should_include( $sent, $opened, $clicked ) {

		if ( $this->get_funnel_id() ) {
			return percentage( $sent, $opened ) > 20 && percentage( $opened, $clicked ) > 10;
		}

		return $sent > 10 && percentage( $sent, $opened ) > 20 && percentage( $opened, $clicked ) > 10;
	}
}
