<?php

namespace Groundhogg\Reporting\New_Reports;

use Groundhogg\DB\Query\Table_Query;
use Groundhogg\Event;
use function Groundhogg\admin_page_url;
use function Groundhogg\base64_json_encode;

class Total_Emails_Sent extends Base_Quick_Stat {

	public function get_link() {

		$filter = [
			'type'       => 'email_received',
			'date_range' => 'between',
			'before'     => $this->endDate->ymd(),
			'after'      => $this->startDate->ymd()
		];

		if ( $this->get_email_id() ) {
			$filter['email_id'] = $this->get_email_id();
		}

		if ( $this->get_step_id() ) {
			$filter['step_id'] = $this->get_step_id();
		}

		if ( $this->get_funnel_id() ) {
			$filter['funnel_id'] = $this->get_funnel_id();
		}

		return admin_page_url( 'gh_contacts', [
			'filters' => base64_json_encode( [
				[
					$filter
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

		$eventsQuery = new Table_Query( 'events' );

		// Step ID is only used in the context of clicking into an email report for a funnel
		if ( $this->get_step_id() ) {

			$eventsQuery->where( 'event_type', Event::FUNNEL )
			            ->equals( 'status', Event::COMPLETE )
			            ->equals( 'funnel_id', $this->get_funnel_id() )
			            ->equals( 'email_id', $this->get_email_id() )
			            ->equals( 'step_id', $this->get_step_id() )
			            ->greaterThanEqualTo( 'time', $start )
			            ->lessThanEqualTo( 'time', $end );

			return $eventsQuery->count();
		}

		// All emails sent from a funnel
		if ( $this->get_funnel_id() ) {

			$eventsQuery->where( 'event_type', Event::FUNNEL )
			            ->equals( 'status', Event::COMPLETE )
			            ->equals( 'funnel_id', $this->get_funnel_id() )
			            ->notEquals( 'email_id', 0 )
			            ->greaterThanEqualTo( 'time', $start )
			            ->lessThanEqualTo( 'time', $end );

			return $eventsQuery->count();

		}

		// All emails sent, anything where email_id is not empty
		$eventsQuery->where()
		            ->equals( 'status', Event::COMPLETE )
		            ->notEquals( 'email_id', 0 )
		            ->greaterThanEqualTo( 'time', $start )
		            ->lessThanEqualTo( 'time', $end );

		return $eventsQuery->count();
	}
}
