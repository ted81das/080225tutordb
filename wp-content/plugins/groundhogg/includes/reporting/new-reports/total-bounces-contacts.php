<?php

namespace Groundhogg\Reporting\New_Reports;

use Groundhogg\Contact_Query;
use Groundhogg\Plugin;
use Groundhogg\Preferences;
use function Groundhogg\admin_page_url;
use function Groundhogg\base64_json_encode;

class Total_Bounces_Contacts extends Base_Negative_Quick_Stat {

	public function get_link() {
		return admin_page_url( 'gh_contacts', [
			'filters' => base64_json_encode( [
				[
					[
						'type'       => 'optin_status_changed',
						'value'      => [ Preferences::HARD_BOUNCE ],
						'date_range' => 'between',
						'before' => $this->endDate->ymd(),
						'after'  => $this->startDate->ymd()
					]
				]
			] )
		] );
	}

	/**
	 * Query the results
	 *
	 * @param $start int
	 * @param $end   int
	 *
	 * @return mixed
	 */
	protected function query( $start, $end ) {

		$query = new Contact_Query();

		$query->set_date_key( 'date_optin_status_changed' );

		$start = Plugin::instance()->utils->date_time->convert_to_local_time( $start );
		$end   = Plugin::instance()->utils->date_time->convert_to_local_time( $end );

		return $query->query( [
			'count'        => true,
			'optin_status' => Preferences::HARD_BOUNCE,
			'date_query'   => [
				'after'  => date( 'Y-m-d H:i:s', $start ),
				'before' => date( 'Y-m-d H:i:s', $end ),
			]
		] );
	}
}
