<?php

namespace FormVibes\Classes;

use Carbon\Carbon;

/**
 * A utility class for the Carbon.
 */

class CarbonUtils {

	/**
	 * Gets the date range by given preset
	 *
	 * @access public
	 * @param string $preset The preset.
	 * @since 1.4.4
	 * @return array [
	 *                  'from_date' => '01/01/2022',
	 *                  'to_date' => '01/01/2022'
	 * ]
	 */
	public static function get_preset_date_range( $preset ) {
		$gmt_offset = get_option( 'gmt_offset' );
		$hours      = (int) $gmt_offset;
		$minutes    = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

		if ( $hours >= 0 ) {
			$time_zone = '+' . $hours . ':' . $minutes;
		} else {
			$time_zone = $hours . ':' . $minutes;
		}
		$dates = [];

		switch ( $preset ) {
			case 'Today':
				$dates['from_date'] = Carbon::now( $time_zone );
				$dates['to_date']   = Carbon::now( $time_zone );

				return $dates;

			case 'Yesterday':
				$dates['from_date'] = Carbon::now( $time_zone )->subDay();
				$dates['to_date']   = Carbon::now( $time_zone )->subDay();

				return $dates;

			case 'Last_7_Days':
				$dates['from_date'] = Carbon::now( $time_zone )->subDays( 6 );
				$dates['to_date']   = Carbon::now( $time_zone );

				return $dates;

			case 'This_Week':
				$start_week = get_option( 'start_of_week' );
				if ( 0 !== $start_week ) {
					$staticstart  = Carbon::now( $time_zone )->startOfWeek( Carbon::MONDAY );
					$staticfinish = Carbon::now( $time_zone )->endOfWeek( Carbon::SUNDAY );
				} else {
					$staticstart  = Carbon::now( $time_zone )->startOfWeek( Carbon::SUNDAY );
					$staticfinish = Carbon::now( $time_zone )->endOfWeek( Carbon::SATURDAY );
				}
				$dates['from_date'] = $staticstart;
				$dates['to_date']   = $staticfinish;
				return $dates;

			case 'Last_Week':
				$start_week = get_option( 'start_of_week' );
				if ( 0 !== $start_week ) {
					$staticstart  = Carbon::now( $time_zone )->startOfWeek( Carbon::MONDAY )->subDays( 7 );
					$staticfinish = Carbon::now( $time_zone )->endOfWeek( Carbon::SUNDAY )->subDays( 7 );
				} else {
					$staticstart  = Carbon::now( $time_zone )->startOfWeek( Carbon::SUNDAY )->subDays( 7 );
					$staticfinish = Carbon::now( $time_zone )->endOfWeek( Carbon::SATURDAY )->subDays( 7 );
				}

				$dates['from_date'] = $staticstart;
				$dates['to_date']   = $staticfinish;

				return $dates;

			case 'Last_30_Days':
				$dates['from_date'] = Carbon::now( $time_zone )->subDays( 30 );
				$dates['to_date']   = Carbon::now( $time_zone );

				return $dates;

			case 'This_Month':
				$dates['from_date'] = Carbon::now( $time_zone )->startOfMonth();
				$dates['to_date']   = Carbon::now( $time_zone )->endOfMonth();

				return $dates;

			case 'Last_Month':
				$dates['from_date'] = Carbon::now( $time_zone )->subMonth()->startOfMonth();
				$dates['to_date']   = Carbon::now( $time_zone )->subMonth()->endOfMonth();

				return $dates;

			case 'This_Quarter':
				$dates['from_date'] = Carbon::now( $time_zone )->startOfQuarter();
				$dates['to_date']   = Carbon::now( $time_zone )->endOfQuarter();

				return $dates;

			case 'Last_Quarter':
				$dates['from_date'] = Carbon::now( $time_zone )->subMonths( 3 )->startOfQuarter();
				$dates['to_date']   = Carbon::now( $time_zone )->subMonths( 3 )->endOfQuarter();

				return $dates;

			case 'This_Year':
				$dates['from_date'] = Carbon::now( $time_zone )->startOfYear();
				$dates['to_date']   = Carbon::now( $time_zone )->endOfYear();

				return $dates;

			case 'Last_Year':
				$dates['from_date'] = Carbon::now( $time_zone )->subMonths( 12 )->startOfYear();
				$dates['to_date']   = Carbon::now( $time_zone )->subMonths( 12 )->endOfYear();

				return $dates;

			case 'All_Time':
				$dates['from_date'] = new Carbon( '29-05-2019' );
				$dates['to_date']   = Carbon::now( $time_zone );
				return $dates;
		}
	}
}
