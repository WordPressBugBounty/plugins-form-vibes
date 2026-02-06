<?php
// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
namespace FormVibes\Classes;

use Carbon\Carbon;
use Stripe\Util\Util;
use FormVibes\Classes\Settings;

/**
 * A utility class for managing the plugin
 */

class Utils {


	/**
	 * Get plugin translation strings
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_i18n() {
		$i18n = [];

		$i18n = [
			'submissions'                     => esc_html__( 'Submissions', 'wpv-fv' ),
			'today'                           => esc_html__( 'Today', 'wpv-fv' ),
			'yesterday'                       => esc_html__( 'Yesterday', 'wpv-fv' ),
			'this_week'                       => esc_html__( 'This Week', 'wpv-fv' ),
			'last_week'                       => esc_html__( 'Last Week', 'wpv-fv' ),
			'last_30_days'                    => esc_html__( 'Last 30 Days', 'wpv-fv' ),
			'this_month'                      => esc_html__( 'This Month', 'wpv-fv' ),
			'last_month'                      => esc_html__( 'Last Month', 'wpv-fv' ),
			'this_quarter'                    => esc_html__( 'This Quarter', 'wpv-fv' ),
			'last_quarter'                    => esc_html__( 'Last Quarter', 'wpv-fv' ),
			'this_year'                       => esc_html__( 'This Year', 'wpv-fv' ),
			'last_year'                       => esc_html__( 'Last Year', 'wpv-fv' ),
			'all_time'                        => esc_html__( 'All Time', 'wpv-fv' ),
			'select_action'                   => esc_html__( 'Select Action', 'wpv-fv' ),
			'quick_export'                    => esc_html__( 'Quick Export', 'wpv-fv' ),
			'delete'                          => esc_html__( 'Delete', 'wpv-fv' ),
			'status'                          => esc_html__( 'Status', 'wpv-fv' ),
			'entries'                         => esc_html__( 'Entries', 'wpv-fv' ),
			'entry'                           => esc_html__( 'Entry', 'wpv-fv' ),
			'delete_msg'                      => esc_html__( 'Please select a row to delete', 'wpv-fv' ),
			'delete_msg_confirm'              => esc_html__( 'Are you sure you want to delete', 'wpv-fv' ),
			'delete_file'                     => esc_html__( 'Delete File', 'wpv-fv' ),
			'delete_entires'                  => esc_html__( 'Delete Entries', 'wpv-fv' ),
			'delete_entires_msg'              => esc_html__( 'Are you sure you want to delete the entries', 'wpv-fv' ),
			'delete_note'                     => esc_html__( 'Delete Note', 'wpv-fv' ),
			'entry_status'                    => esc_html__( 'Entry Status', 'wpv-fv' ),
			'select_status'                   => esc_html__( 'Select Status', 'wpv-fv' ),
			'show'                            => esc_html__( 'Show', 'wpv-fv' ),
			'ssnc'                            => esc_html__( 'Show Serial Number column', 'wpv-fv' ),
			'sdonre'                          => esc_html__( 'Show data on row expand', 'wpv-fv' ),
			'show_notes'                      => esc_html__( 'Show Notes', 'wpv-fv' ),
			'hide_notes'                      => esc_html__( 'Hide Notes', 'wpv-fv' ),
			'read'                            => esc_html__( 'Read', 'wpv-fv' ),
			'unread'                          => esc_html__( 'Unread', 'wpv-fv' ),
			'spam'                            => esc_html__( 'Spam', 'wpv-fv' ),
			'or_condition_met_msg'            => esc_html__( 'Any of the condition must be met', 'wpv-fv' ),
			'and_condition_met_msg'           => esc_html__( 'Every condition must be met', 'wpv-fv' ),
			'or'                              => esc_html__( 'OR', 'wpv-fv' ),
			'and'                             => esc_html__( 'AND', 'wpv-fv' ),
			'export'                          => esc_html__( 'Export', 'wpv-fv' ),
			'select_column'                   => esc_html__( 'Select Column', 'wpv-fv' ),
			'add_new_condition'               => esc_html__( 'Add New Condition', 'wpv-fv' ),
			'apply_filters'                   => esc_html__( 'Apply Filters', 'wpv-fv' ),
			'field_name'                      => esc_html__( 'Field Name', 'wpv-fv' ),
			'column_label'                    => esc_html__( 'Column Label', 'wpv-fv' ),
			'column_visibility'               => esc_html__( 'Column Visibility', 'wpv-fv' ),
			'cancel'                          => esc_html__( 'Cancel', 'wpv-fv' ),
			'save_changes'                    => esc_html__( 'Save Changes', 'wpv-fv' ),
			'add_note'                        => esc_html__( 'Add Note', 'wpv-fv' ),
			'analytics'                       => esc_html__( 'Analytics', 'wpv-fv' ),
			'by_day'                          => esc_html__( 'By Day', 'wpv-fv' ),
			'by_week'                         => esc_html__( 'By Week', 'wpv-fv' ),
			'by_month'                        => esc_html__( 'By Month', 'wpv-fv' ),
			'event_logs'                      => esc_html__( 'Event Logs', 'wpv-fv' ),
			'profiles_type'                   => esc_html__( 'Profiles Type', 'wpv-fv' ),
			'local_download'                  => esc_html__( 'Local Download', 'wpv-fv' ),
			'google_sheet'                    => esc_html__( 'Google Sheet', 'wpv-fv' ),
			'form'                            => esc_html__( 'Form', 'wpv-fv' ),
			'date_range'                      => esc_html__( 'Date Range', 'wpv-fv' ),
			'data_filters'                    => esc_html__( 'Data Filters', 'wpv-fv' ),
			'table_columns'                   => esc_html__( 'Table Columns', 'wpv-fv' ),
			'data_source'                     => esc_html__( 'Data Source', 'wpv-fv' ),
			'fields'                          => esc_html__( 'Fields', 'wpv-fv' ),
			'table_settings'                  => esc_html__( 'Table Settings', 'wpv-fv' ),
			'search'                          => esc_html__( 'Search', 'wpv-fv' ),
			'counter_settings'                => esc_html__( 'Counter Settings', 'wpv-fv' ),
			'sheet_headers'                   => esc_html__( 'Sheet Headers', 'wpv-fv' ),
			'form_fields'                     => esc_html__( 'Form Fields', 'wpv-fv' ),
			'exported_files'                  => esc_html__( 'Exported Files', 'wpv-fv' ),
			'no_export_msg'                   => esc_html__( 'No Exported Files Found!', 'wpv-fv' ),
			'no_google_sheet_credentials_msg' => esc_html__( 'Client ID and Client Secret are required to export to Google Sheets.', 'wpv-fv' ),
			'enter_details'                   => esc_html__( 'Enter Details', 'wpv-fv' ),
			'table'                           => esc_html__( 'Table', 'wpv-fv' ),
			'counter'                         => esc_html__( 'Counter', 'wpv-fv' ),
			'display_type'                    => esc_html__( 'Display Type', 'wpv-fv' ),
			'ftdore'                          => esc_html__( 'Field to display on row expand', 'wpv-fv' ),

			'default_rows'                    => esc_html__( 'Default Rows', 'wpv-fv' ),
			'pagination'                      => esc_html__( 'Pagination', 'wpv-fv' ),
			'limit'                           => esc_html__( 'Limit', 'wpv-fv' ),
			'serial_number_column_title'      => esc_html__( 'Serial Number Column Title', 'wpv-fv' ),
			'search_columns'                  => esc_html__( 'Search Columns', 'wpv-fv' ),
			'search_operator'                 => esc_html__( 'Search Operator', 'wpv-fv' ),
			'display_filter_on_frontend'      => esc_html__( 'Display filter on frontend', 'wpv-fv' ),
			'alignment'                       => esc_html__( 'Alignment', 'wpv-fv' ),
			'custom_message'                  => esc_html__( 'Custom Message', 'wpv-fv' ),
			'save_settings'                   => esc_html__( 'Save Settings', 'wpv-fv' ),
			'role_manager'                    => esc_html__( 'Role Manager', 'wpv-fv' ),
			'google_sheets'                   => esc_html__( 'Google Sheets', 'wpv-fv' ),
			'settings'                        => esc_html__( 'Settings', 'wpv-fv' ),
			'client_id'                       => esc_html__( 'Client ID', 'wpv-fv' ),
			'client_secret'                   => esc_html__( 'Client Secret', 'wpv-fv' ),

			'authenticating'                  => esc_html__( 'Authenticating', 'wpv-fv' ),
			're_authenticate'                 => esc_html__( 'Re Authenticate', 'wpv-fv' ),
			'authenticate'                    => esc_html__( 'Authenticate', 'wpv-fv' ),
			'instructions'                    => esc_html__( 'Instructions', 'wpv-fv' ),
			'filter'                    => esc_html__( 'Filter', 'wpv-fv' ),
		];

		return apply_filters( 'wpv-fv_builder_i18n', $i18n );
	}
	/**
	 * Creates a default parameters array.
	 *
	 * @access public
	 * @param array $params Parameters to merge.
	 * @since 1.4.4
	 * @return array
	 */
	public static function make_params( $params ) {
		$temp = [
			'query_type' => '',
			'per_page'   => '',
			'page_num'   => '',
			'fromDate'   => '',
			'toDate'     => '',
			'plugin'     => '',
			'formid'     => '',
		];

		return array_merge( $temp, $params );
	}

	/**
	 * Its shows the notice for deactivating the plugin.
	 *
	 * @access public
	 * @param bool $is_outer If the notice is outside of plugin div.
	 * @since 1.4.4
	 * @return void
	 */
	public static function show_disable_free_notice( $is_outer = false ) {
		$deactivate_url = wp_nonce_url( self_admin_url( 'plugins.php?action=deactivate&plugin=form-vibes/form-vibes.php' ), 'deactivate-plugin_form-vibes/form-vibes.php' );
		$classnames     = '';

		if ( $is_outer ) {
			$classnames = 'fv-notice-outer';
		}

		?>
		<div class="notice notice-info is-dismissible <?php echo esc_html( $classnames ); ?>">
			<p>
				From <b>Form Vibes 1.4.0</b> onwards free version of plugin is not required if you have Pro version activated!
				<a href="<?php echo esc_url( $deactivate_url ); ?>" class="button-primary">Deactivate Plugin</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Gets the plugin logo svg
	 *
	 * @access public
	 * @since 1.4.4
	 * @return string
	 */
	public static function get_fv_logo_svg() {
		return '<svg
								width="30px"
								height="30px"
								viewBox="0 0 1340 1340"
								version="1.1"
							>
								<g
									id="Page-1"
									stroke="none"
									strokeWidth="1"
									fill="none"
									fillRule="evenodd"
								>
									<g
										id="Artboard"
										transform="translate(-534.000000, -2416.000000)"
										fillRule="nonzero"
									>
										<g
											id="g2950"
											transform="translate(533.017848, 2415.845322)"
										>
											<circle
												id="circle2932"
												fill="#FF6634"
												cx="670.8755"
												cy="670.048026"
												r="669.893348"
											/>
											<path
												d="M1151.33208,306.590013 L677.378555,1255.1191 C652.922932,1206.07005 596.398044,1092.25648 590.075594,1079.88578 L589.97149,1079.68286 L975.423414,306.590013 L1151.33208,306.590013 Z M589.883553,1079.51122 L589.97149,1079.68286 L589.940317,1079.74735 C589.355382,1078.52494 589.363884,1078.50163 589.883553,1079.51122 Z M847.757385,306.589865 L780.639908,441.206555 L447.47449,441.984865 L493.60549,534.507865 L755.139896,534.508386 L690.467151,664.221407 L558.27749,664.220865 L613.86395,775.707927 L526.108098,951.716924 L204.45949,306.589865 L847.757385,306.589865 Z"
												id="Combined-Shape"
												fill="#FFFFFF"
											/>
										</g>
									</g>
								</g>
							</svg>';
	}

	/**
	 * Sets export reason in the logs table
	 *
	 * @access public
	 * @param string $description A string to save into logs table.
	 * @since 1.4.4
	 * @return void
	 */
	public static function set_export_reason( $description ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'fv_logs',
			[
				'user_id'         => get_current_user_id(),
				'event'           => 'export',
				'description'     => sanitize_text_field( $description ),
				'export_time'     => current_time( 'mysql', 0 ),
				'export_time_gmt' => current_time( 'mysql', 1 ),
			]
		);
	}

	/**
	 * Convert a string to camel case
	 *
	 * @access public
	 * @param string $string A string to covert into camel case.
	 * @param bool $capitalise_first_char If first char should be capital.
	 * @since 1.4.4
	 * @return void
	 */
	public static function dashes_to_camel_case( $string, $capitalize_first_character = true ) {
		$str = str_replace( '-', '', ucwords( $string, '-' ) );
		if ( ! $capitalize_first_character ) {
			$str = lcfirst( $str );
		}
		return $str;
	}

	/**
	 * Gets the plugin forms
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function prepare_forms_data() {
		global $wpdb;
		$forms                = [];
		$data                 = [];
		$data['forms_plugin'] = apply_filters( 'fv_forms', $forms );

		$settings   = get_option( 'fvSettings' );
		$debug_mode = false;

		if ( $settings && Utils::key_exists( 'debug_mode', $settings ) ) {
			$debug_mode = $settings['debug_mode'];
		}

		$form_res = $wpdb->get_results( "select DISTINCT form_id,form_plugin from {$wpdb->prefix}fv_enteries e", OBJECT_K );

		$inserted_forms = get_option( 'fv_forms' );

		$plugin_forms = [];

		foreach ( $data['forms_plugin'] as $key => $value ) {
			$res = [];

			if ( 'caldera' === $key ) {
				$class = '\FormVibes\Integrations\\' . ucfirst( $key );

				$res = $class::get_forms( $key );
			} else {
				foreach ( $form_res as $form_key => $form_value ) {
					if ( Utils::key_exists( $key, $inserted_forms ) && Utils::key_exists( $form_key, $inserted_forms[ $key ] ) ) {
						$name = $inserted_forms[ $key ][ $form_key ]['name'];
					} else {
						$name = $form_key;
					}
					if ( $form_res[ $form_key ]->form_plugin === $key ) {
						$res[ $form_key ] = [
							'id'   => $form_key,
							'name' => $name,
						];
					}
				}
			}

			if ( null !== $res ) {
				$plugin_forms[ $key ] = $res;
			}
		}

		// sort the forms as per their names
		foreach ( $plugin_forms as $f_key => $form ) {
			$forms_name = [];

			foreach ( $form as $key => $row ) {
				$forms_name[ $key ] = $row['name'];
			}

			array_multisort( $forms_name, SORT_ASC, $form );

			$plugin_forms[ $f_key ] = (object) $form;
		}

		return apply_filters( 'formvibes/all_forms', $plugin_forms );
	}

	/**
	 * Gets the saved columns from the database
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_fv_keys() {
		$temp = get_option( 'fv-keys' );

		if ( '' === $temp || false === $temp ) {
			return [];
		}
		$fv_keys = [];
		foreach ( $temp as $key => $value ) {
			foreach ( $value as $val_key => $val_val ) {
				$val_val                             = (object) $val_val;
				$fv_keys[ $key ][ $val_val->colKey ] = $val_val;
			}
		}
		return $fv_keys;
	}

	/**
	 * Gets the plugin key by plugin name
	 *
	 * @access public
	 * @since 1.4.4
	 * @return string
	 */
	public static function get_plugin_key_by_name( $name ) {
		if ( 'Contact Form 7' === $name ) {
			return 'cf7';
		} elseif ( 'Elementor Forms' === $name ) {
			return 'elementor';
		} elseif ( 'Beaver Builder' === $name ) {
			return 'beaverBuilder';
		} elseif ( 'WP Forms' === $name ) {
			return 'wp-forms';
		} elseif ( 'Caldera' === $name ) {
			return 'caldera';
		} elseif ( 'Ninja Forms' === $name ) {
			return 'Ninja-Forms';
		} elseif ( 'Gravity Forms' === $name ) {
			return 'gravity-forms';
		} elseif ( 'WS Form' === $name ) {
			return 'ws-form';
		}

		return $name;
	}

	/**
	 * Gets the saved columns from the database
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_plugin_name_by_key( $key ) {
		if ( 'cf7' === $key ) {
			return 'Contact Form 7';
		} elseif ( 'elementor' === $key ) {
			return 'Elementor Forms';
		} elseif ( 'beaverBuilder' === $key ) {
			return 'Beaver Builder';
		} elseif ( 'wp-forms' === $key ) {
			return 'WP Forms';
		} elseif ( 'caldera' === $key ) {
			return 'Caldera';
		} elseif ( 'Ninja-Forms' === $key ) {
			return 'Ninja Forms';
		} elseif ( 'gravity-forms' === $key ) {
			return 'Gravity Forms';
		} elseif ( 'ws-form' === $key ) {
			return 'WS Form';
		}

		return $key;
	}

	/**
	 * Gets the dates by date range
	 *
	 * @access public
	 * @param string $query_type The current query type `Custom | Preset`
	 * @param array $param The date range
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_query_dates( $query_type, $param ) {

		$gmt_offset = get_option( 'gmt_offset' );
		$hours      = (int) $gmt_offset;
		$minutes    = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

		if ( $hours >= 0 ) {
			$time_zone = '+' . $hours . ':' . $minutes;
		} else {
			$time_zone = $hours . ':' . $minutes;
		}

		if ( 'Custom' !== $query_type ) {
			$dates     = self::get_date_interval( $query_type, $time_zone );
			$from_date = $dates['fromDate'];
			$to_date   = $dates['endDate'];

			if ( $query_type === 'All_Time' ) {
				$from_date = new Carbon( '2019-05-29', $time_zone );
				$to_date   = Carbon::now( $time_zone );
			}
		} else {
			$tz        = new \DateTimeZone( $time_zone );
			$from_date = new \DateTime( $param['fromDate'] );
			$from_date->setTimezone( $tz );
			$to_date = new \DateTime( $param['toDate'] );
			$to_date->setTimezone( $tz );
		}

		return [ $from_date, $to_date ];
	}

	/**
	 * Check if the array is associative array
	 *
	 * @access public
	 * @param array $array The array to be checked
	 * @since 1.4.4
	 * @return bool
	 */
	public static function is_array_associative( array $array ) {
		reset( $array );
		return ! is_int( key( $array ) );
	}

	/**
	 * Gets the dates by preset and time zone
	 *
	 * @access public
	 * @param string $query_type The presets name
	 * @param mixed $time_zone The current timezone
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_date_interval( $query_type, $time_zone ) {
		$dates = [];
		switch ( $query_type ) {
			case 'Today':
				$dates['fromDate'] = Carbon::now( $time_zone );
				$dates['endDate']  = Carbon::now( $time_zone );

				return $dates;

			case 'Yesterday':
				$dates['fromDate'] = Carbon::now( $time_zone )->subDay();
				$dates['endDate']  = Carbon::now( $time_zone )->subDay();

				return $dates;

			case 'Last_7_Days':
				$dates['fromDate'] = Carbon::now( $time_zone )->subDays( 6 );
				$dates['endDate']  = Carbon::now( $time_zone );

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
				$dates['fromDate'] = $staticstart;
				$dates['endDate']  = $staticfinish;
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

				$dates['fromDate'] = $staticstart;
				$dates['endDate']  = $staticfinish;

				return $dates;

			case 'Last_30_Days':
				$dates['fromDate'] = Carbon::now( $time_zone )->subDays( 29 );
				$dates['endDate']  = Carbon::now( $time_zone );

				return $dates;

			case 'This_Month':
				$dates['fromDate'] = Carbon::now( $time_zone )->startOfMonth();
				$dates['endDate']  = Carbon::now( $time_zone )->endOfMonth();

				return $dates;

			case 'Last_Month':
				$dates['fromDate'] = Carbon::now( $time_zone )->subMonth()->startOfMonth();
				$dates['endDate']  = Carbon::now( $time_zone )->subMonth()->endOfMonth();

				return $dates;

			case 'This_Quarter':
				$dates['fromDate'] = Carbon::now( $time_zone )->startOfQuarter();
				$dates['endDate']  = Carbon::now( $time_zone )->endOfQuarter();

				return $dates;

			case 'Last_Quarter':
				$dates['fromDate'] = Carbon::now( $time_zone )->subMonths( 3 )->startOfQuarter();
				$dates['endDate']  = Carbon::now( $time_zone )->subMonths( 3 )->endOfQuarter();

				return $dates;

			case 'This_Year':
				$dates['fromDate'] = Carbon::now( $time_zone )->startOfYear();
				$dates['endDate']  = Carbon::now( $time_zone )->endOfYear();

				return $dates;

			case 'Last_Year':
				$dates['fromDate'] = Carbon::now( $time_zone )->subMonths( 12 )->startOfYear();
				$dates['endDate']  = Carbon::now( $time_zone )->subMonths( 12 )->endOfYear();

				return $dates;
		}
	}

	/**
	 * Gets the dates by preset
	 *
	 * @access public
	 * @param string $query_type The presets name
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_dates( $query_type ) {
		$dates = [];
		switch ( $query_type ) {
			case 'Today':
				$dates['fromDate'] = date( 'Y-m-d H:i:s' );
				$dates['endDate']  = date( 'Y-m-d H:i:s' );

				return $dates;

			case 'Yesterday':
				$dates['fromDate'] = date( 'Y-m-d H:i:s', strtotime( '-1 days' ) );
				$dates['endDate']  = date( 'Y-m-d H:i:s', strtotime( '-1 days' ) );

				return $dates;

			case 'Last_7_Days':
				$dates['fromDate'] = date( 'Y-m-d H:i:s', strtotime( '-6 days' ) );
				$dates['endDate']  = date( 'Y-m-d H:i:s' );

				return $dates;

			case 'This_Week':
				$start_week = get_option( 'start_of_week' );
				if ( 0 !== $start_week ) {
					if ( 'Mon' !== date( 'D' ) ) {
						$staticstart = date( 'Y-m-d', strtotime( 'last Monday' ) );
					} else {
						$staticstart = date( 'Y-m-d' );
					}

					if ( 'Sat' !== date( 'D' ) ) {
						$staticfinish = date( 'Y-m-d', strtotime( 'next Sunday' ) );
					} else {

						$staticfinish = date( 'Y-m-d' );
					}
				} else {
					if ( 'Sun' !== date( 'D' ) ) {
						$staticstart = date( 'Y-m-d', strtotime( 'last Sunday' ) );
					} else {
						$staticstart = date( 'Y-m-d' );
					}

					if ( 'Sat' !== date( 'D' ) ) {
						$staticfinish = date( 'Y-m-d', strtotime( 'next Saturday' ) );
					} else {

						$staticfinish = date( 'Y-m-d' );
					}
				}
				$dates['fromDate'] = $staticstart;
				$dates['endDate']  = $staticfinish;
				return $dates;

			case 'Last_Week':
				$start_week = get_option( 'start_of_week' );
				if ( 0 !== $start_week ) {
					$previous_week = strtotime( '-1 week +1 day' );
					$start_week    = strtotime( 'last monday midnight', $previous_week );
					$end_week      = strtotime( 'next sunday', $start_week );
				} else {
					$previous_week = strtotime( '-1 week +1 day' );
					$start_week    = strtotime( 'last sunday midnight', $previous_week );
					$end_week      = strtotime( 'next saturday', $start_week );
				}
				$start_week = date( 'Y-m-d', $start_week );
				$end_week   = date( 'Y-m-d', $end_week );

				$dates['fromDate'] = $start_week;
				$dates['endDate']  = $end_week;

				return $dates;

			case 'Last_30_Days':
				$dates['fromDate'] = date( 'Y-m-d h:m:s', strtotime( '-29 days' ) );
				$dates['endDate']  = date( 'Y-m-d h:m:s' );

				return $dates;

			case 'This_Month':
				$dates['fromDate'] = date( 'Y-m-01' );
				$dates['endDate']  = date( 'Y-m-t' );

				return $dates;

			case 'Last_Month':
				$dates['fromDate'] = date( 'Y-m-01', strtotime( 'first day of last month' ) );
				$dates['endDate']  = date( 'Y-m-t', strtotime( 'last day of last month' ) );

				return $dates;

			case 'This_Quarter':
				$current_month = date( 'm' );
				$current_year  = date( 'Y' );
				if ( $current_month >= 1 && $current_month <= 3 ) {
					$start_date = strtotime( '1-January-' . $current_year );  // timestamp or 1-Januray 12:00:00 AM
					$end_date   = strtotime( '31-March-' . $current_year );  // timestamp or 1-April 12:00:00 AM means end of 31 March
				} elseif ( $current_month >= 4 && $current_month <= 6 ) {
					$start_date = strtotime( '1-April-' . $current_year );  // timestamp or 1-April 12:00:00 AM
					$end_date   = strtotime( '30-June-' . $current_year );  // timestamp or 1-July 12:00:00 AM means end of 30 June
				} elseif ( $current_month >= 7 && $current_month <= 9 ) {
					$start_date = strtotime( '1-July-' . $current_year );  // timestamp or 1-July 12:00:00 AM
					$end_date   = strtotime( '30-September-' . $current_year );  // timestamp or 1-October 12:00:00 AM means end of 30 September
				} elseif ( $current_month >= 10 && $current_month <= 12 ) {
					$start_date = strtotime( '1-October-' . $current_year );  // timestamp or 1-October 12:00:00 AM
					$end_date   = strtotime( '31-December-' . ( $current_year ) );  // timestamp or 1-January Next year 12:00:00 AM means end of 31 December this year
				}

				$dates['fromDate'] = date( 'Y-m-d', $start_date );
				$dates['endDate']  = date( 'Y-m-d', $end_date );
				return $dates;

			case 'Last_Quarter':
				$current_month = date( 'm' );
				$current_year  = date( 'Y' );

				if ( $current_month >= 1 && $current_month <= 3 ) {
					$start_date = strtotime( '1-October-' . ( $current_year - 1 ) );  // timestamp or 1-October Last Year 12:00:00 AM
					$end_date   = strtotime( '31-December-' . ( $current_year - 1 ) );  // // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
				} elseif ( $current_month >= 4 && $current_month <= 6 ) {
					$start_date = strtotime( '1-January-' . $current_year );  // timestamp or 1-Januray 12:00:00 AM
					$end_date   = strtotime( '31-March-' . $current_year );  // timestamp or 1-April 12:00:00 AM means end of 31 March
				} elseif ( $current_month >= 7 && $current_month <= 9 ) {
					$start_date = strtotime( '1-April-' . $current_year );  // timestamp or 1-April 12:00:00 AM
					$end_date   = strtotime( '30-June-' . $current_year );  // timestamp or 1-July 12:00:00 AM means end of 30 June
				} elseif ( $current_month >= 10 && $current_month <= 12 ) {
					$start_date = strtotime( '1-July-' . $current_year );  // timestamp or 1-July 12:00:00 AM
					$end_date   = strtotime( '30-September-' . $current_year );  // timestamp or 1-October 12:00:00 AM means end of 30 September
				}
				$dates['fromDate'] = date( 'Y-m-d', $start_date );
				$dates['endDate']  = date( 'Y-m-d', $end_date );
				return $dates;

			case 'This_Year':
				$dates['fromDate'] = date( 'Y-01-01' );
				$dates['endDate']  = date( 'Y-12-t' );

				return $dates;

			case 'Last_Year':
				$dates['fromDate'] = date( 'Y-01-01', strtotime( '-1 year' ) );
				$dates['endDate']  = date( 'Y-12-t', strtotime( '-1 year' ) );

				return $dates;
		}
	}

	/**
	 * Gets the first plugin form
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_first_plugin_form() {
		$forms   = [];
		$plugins = apply_filters( 'fv_forms', $forms );

		$class = '\FormVibes\Integrations\\' . ucfirst( array_keys( $plugins )[0] );

		$plugin_forms = $class::get_forms( array_keys( $plugins )[0] );
		$plugin       = array_keys( $plugins )[0];

		$data = [
			'formName'       => $plugin_forms,
			'selectedPlugin' => $plugin,
			'selectedForm'   => array_keys( $plugin_forms )[0],
		];

		return $data;
	}

	/**
	 * Gets form name by form id
	 *
	 * @access public
	 * @param string $id The form id
	 * @since 1.4.4
	 * @return string
	 */
	public static function get_form_name_by_id( $id ) {
		$all_forms = self::prepare_forms_data()['allForms'];
		$form_name = '';
		foreach ( $all_forms as $key => $value ) {
			$options = $value['options'];
			foreach ( $options as $op_key => $op_value ) {
				// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( $op_value['value'] == $id ) {
					$form_name = $op_value['formName'];
				}
			}
		}
		return $form_name;
	}

	/**
	 * Get the columns when all forms are selected
	 *
	 * @access private
	 * @param array $original_columns The original columns
	 * @param array $saved_columns_option The saved columns from database
	 * @since 1.4.4
	 * @return array
	 */
	private static function prepare_columns_for_all_forms(
		$original_columns,
		$saved_columns_option
	) {

		$original_columns = array_unique( $original_columns );

		$columns     = [];
		$column_keys = [];

		// taking all saved column and saving into a variable.
		if ( $saved_columns_option && Utils::key_exists( 'fv__all_forms_fv__all_forms', $saved_columns_option ) ) {
			foreach ( $saved_columns_option as $values ) {
				foreach ( $values as $value ) {
					$col_key = $value->colKey;
					if ( in_array( $col_key, $original_columns, true ) && ! in_array( $col_key, $column_keys, true ) ) {
						$columns[ $col_key ] = $value;
					}
				}
			}

			$all_forms_cols = $saved_columns_option['fv__all_forms_fv__all_forms'];
			foreach ( $all_forms_cols as $values ) {
				$columns[ $values['colKey'] ] = $values;
			}
		}

		$cols = [];

		foreach ( $original_columns as $value ) {
			if ( Utils::key_exists( $value, $columns ) ) {
				$cols[ $value ] = $columns[ $value ];
			} else {
				$alias          = self::change_alias( $value );
				$cols[ $value ] = [
					'colKey'  => $value,
					'alias'   => $alias,
					'visible' => 1,
				];
			}
		}

		$columns = [];

		foreach ( $cols as $col ) {
			$columns[] = $col;
		}

		return $columns;
	}

	/**
	 * Get the columns if saved in database
	 *
	 * @access private
	 * @param array $original_columns The original columns
	 * @param string $form_id The form id
	 * @param string $plugin_name The plugin name
	 * @param bool $is_all_forms If all forms columns needed
	 * @since 1.4.4
	 * @return array|bool
	 */
	private static function form_columns_already_saved( $original_columns, $form_id, $plugin_name, $is_all_forms ) {
		$saved_columns_option = get_option( 'fv-keys' );

		if ( $is_all_forms ) {
			return self::prepare_columns_for_all_forms(
				$original_columns,
				$saved_columns_option
			);
		}

		$saved_columns_key = $plugin_name . '_' . $form_id;

		$settings = Settings::instance();
		$save_ip  = $settings->get_setting_value_by_key( 'save_ip_address' );
		$save_ua  = $settings->get_setting_value_by_key( 'save_user_agent' );

		if ( $saved_columns_option && Utils::key_exists( $saved_columns_key, $saved_columns_option ) && $saved_columns_option[ $saved_columns_key ] ) {

			$saved_columns = $saved_columns_option[ $saved_columns_key ];

			foreach ( $original_columns as $column ) {
				$key   = array_search( $column, array_column( $saved_columns, 'colKey' ), true );
				$alias = $column;

				// if newly added column is not in the saved columns then we push it.
				if ( false === $key ) {
					$saved_columns[] = [
						'alias'   => $alias,
						'colKey'  => $column,
						'visible' => true,
					];
				}
			}

			$cols = [];

			foreach ( $saved_columns as $values ) {
				$values = (array) $values;
				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $values['colKey'], array_values( $original_columns ) ) ) {
					$cols[ $values['colKey'] ] = $values;
				}
			}

			// check this if we want to hide them if save user agent is set false from settings
			if ( ! $save_ip && false ) {
				unset( $cols['IP'] );
			}
			if ( ! $save_ua && false ) {
				unset( $cols['user_agent'] );
			}

			$cols = apply_filters( "formvibes/submissions/{$plugin_name}/columns", $cols, $original_columns, $form_id );

			return array_values( $cols );
		}
		return false;
	}

	/**
	 * Get the columns that will be used on frontend
	 *
	 * @access public
	 * @param array $columns The columns from database
	 * @param string $form_id The form id
	 * @param string $plugin_name The plugin name
	 * @param bool $is_all_forms If all forms columns needed
	 * @since 1.4.4
	 * @return array
	 */
	public static function prepare_table_columns( $columns, $plugin_name, $form_id, $is_all_forms ) {

		// check if column value contains null,false or empty.
		$columns = array_filter(
			$columns,
			function ( $column ) {
				return ( $column !== null && $column !== false && $column !== '' );
			}
		);

		// remove the fv-notes if exist from the columns because we don't want to show it in the table.
		$key = array_search( 'fv-notes', $columns, true );
		if ( ( $key ) !== false ) {
			unset( $columns[ $key ] );
		}

		$already_saved_columns_data = self::form_columns_already_saved( $columns, $form_id, $plugin_name, $is_all_forms );

		// if columns are saved in db.
		if ( $already_saved_columns_data ) {
			return $already_saved_columns_data;
		}

		$cols = [];

		// default columns
		foreach ( $columns as $column ) {

			$alias = self::change_alias( $column );

			$cols[ $column ] = [
				'colKey'  => $column,
				'alias'   => $alias,
				'visible' => true,
			];
		}

		$cols = apply_filters( "formvibes/submissions/{$plugin_name}/columns", $cols, $columns, $form_id );

		return array_values( $cols );
	}

	/**
	 * Change the alias for `captured,user_agent,form_name,form_plugin,id`
	 *
	 * @access private
	 * @param array $key That alias is be changed
	 * @since 1.4.4
	 * @return string
	 */
	private static function change_alias( $key ) {
		$alias = $key;
		if ( $key === 'captured' || $key === 'datestamp' ) {
			$alias = 'Submission Date';
		}

		if ( $key === 'user_agent' ) {
			$alias = 'User Agent';
		}

		if ( $key === 'form_name' ) {
			$alias = 'Form Name';
		}
		if ( $key === 'form_plugin' ) {
			$alias = 'Plugin Name';
		}
		if ( $key === 'id' ) {
			$alias = 'ID';
		}

		return $alias;
	}



	// FILTERS STARTS

	/**
	 * Get the status
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_fv_status() {
		$status = [
			[
				'key'       => 'read',
				'value'     => 'Read',
				'textColor' => '#065F46',
				'bgColor'   => '#D1FAE5',
			],
			[
				'key'       => 'unread',
				'value'     => 'Unread',
				'textColor' => '#1E40AF',
				'bgColor'   => '#DBEAFE',
			],
			[
				'key'       => 'spam',
				'value'     => 'Spam',
				'textColor' => '#991B1B',
				'bgColor'   => '#FEE2E2',
			],
		];

		return apply_filters( 'formvibes/submission/status', $status );
	}

	/**
	 * Get the submission filter operators
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_operators() {
		$operators = [
			[
				'key'      => 'equal',
				'value'    => 'Equal',
				'operator' => '=',
			],
			[
				'key'      => 'not_equal',
				'value'    => 'Not Equal',
				'operator' => '!=',
			],
			[
				'key'      => 'contain',
				'value'    => 'Contain',
				'operator' => 'LIKE',
			],
			[
				'key'      => 'not_contain',
				'value'    => 'Not Contain',
				'operator' => 'NOT LIKE',
			],
		];
		return apply_filters( 'formvibes/submission/filter/operators', $operators );
	}


	/**
	 * Check if operator is for older version
	 *
	 * @access public
	 * @param string $operator The operator
	 * @since 1.4.4
	 * @return string
	 */
	public static function check_operator_for_backward_compatibility( $operator ) {
		switch ( $operator ) {
			case 'equal':
				return '=';
			case 'not_equal':
				return '!=';
			case 'contain':
				return 'LIKE';
			case 'not_contain':
				return 'NOT LIKE';
			default:
				return $operator;
		}
	}

	/**
	 * Get entry table fields
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_entry_table_fields() {
		$entry_table_fields = [
			'url',
			'user_agent',
			'fv_status',
			'captured',
			'form_id',
			'form_name',
			'form_plugin',
			'id',
		];

		return apply_filters( 'formvibes/entry_table_fields', $entry_table_fields );
	}

	/**
	 * Get frontend table limit
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_table_size_limits() {
		$limits = [
			[
				'key'   => '5',
				'value' => 5,
			],
			[
				'key'   => '10',
				'value' => 10,
			],
			[
				'key'   => '15',
				'value' => 15,
			],
			[
				'key'   => '20',
				'value' => 20,
			],
			[
				'key'   => '30',
				'value' => 30,
			],
			[
				'key'   => '40',
				'value' => 40,
			],
			[
				'key'   => '50',
				'value' => 50,
			],
			[
				'key'   => '100',
				'value' => 100,
			],
		];
		return apply_filters( 'formvibes/submission/table/limits', $limits );
	}

	/**
	 * Get default settings and parameters
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_global_settings() {
		$domain          = wp_parse_url( get_site_url() );
		$host            = $domain['host'];
		$global_settings = [
			'ajax_url'                     => admin_url( 'admin-ajax.php' ),
			'rest_url'                     => get_rest_url(),
			'nonce'                        => wp_create_nonce( 'wp_rest' ),
			'ajax_nonce'                   => wp_create_nonce( 'fv_ajax_nonce' ),
			'forms'                        => Utils::prepare_forms_data(),
			'fv_dashboard_widget_settings' => get_option( 'fv_dashboard_widget_settings' ),
			'entry_table_fields'           => Utils::get_entry_table_fields(),
			'saved_columns'                => Utils::get_fv_keys(),
			'plugins'                      => apply_filters( 'fv_forms', [] ),
			'title'                        => 'Form Vibes',
			'version'                      => WPV_FV__VERSION,
			'logo'                         => Utils::get_fv_logo_svg(),
			'quick_export_limit'           => 1000,
			'domain'                       => $domain,
			'tld'                          => strtolower( str_replace( 'www.', '', $domain['host'] ) ),
			'i18n'                         => self::get_i18n(),
		];
		return apply_filters( 'formvibes/global/settings', $global_settings );
	}

	/**
	 * Check if plugin is pro
	 *
	 * @access public
	 * @since 1.4.4
	 * @return bool
	 */
	public static function is_pro() {
		$global_settings = Utils::get_global_settings();
		$is_pro          = false;
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( file_exists( WPV_FV__PATH . 'inc/pro/bootstrap.php' ) && wpv_fv()->can_use_premium_code__premium_only() && Utils::key_exists( 'is_pro', $global_settings ) && $global_settings['is_pro'] == 1 ) {
			$is_pro = true;
		}

		return $is_pro;
	}

	// FILTERS ENDS

	/**
	 * Check if array or object has key
	 *
	 * @access public
	 * @param string $key The key
	 * @param array|object $value The array
	 * @since 1.4.4
	 * @return bool
	 */
	public static function key_exists( $key, $value ) {
		if ( is_object( $value ) ) {
			return property_exists( $value, $key );
		}
		if ( is_array( $value ) ) {
			return array_key_exists( $key, $value );
		}
		return false;
	}

	/**
	 * Add to logs table
	 *
	 * @access public
	 * @param string $logs_data The logs data
	 * @param string $event The event of log
	 * @since 1.4.4
	 * @return void
	 */
	public static function add_to_log_table( $logs_data, $event ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'fv_logs',
			[
				'user_id'         => get_current_user_id(),
				'event'           => $event,
				'description'     => sanitize_text_field( wp_json_encode( $logs_data ) ),
				'export_time'     => current_time( 'mysql', 0 ),
				'export_time_gmt' => current_time( 'mysql', 1 ),
			]
		);
	}

	/**
	 * Split a string by colon
	 *
	 * @access public
	 * @param string $str The with colon string `'cf7:123'`
	 * @since 1.4.4
	 * @return array|bool `['cf7', 123]`|`false`
	 */
	public static function split_string_by_colon( $str ) {
		if ( $str === '' ) {
			return false;
		}

		$data = explode( ':', $str );
		$data = array_map( 'trim', $data );
		return $data;
	}

	/**
	 * Get the table columns columns
	 *
	 * @access public
	 * @param string $plugin the plugin name/slug
	 * @param string $form_id the form id
	 * @since 1.4.4
	 * @return array `[
	 *  'columns' => [],
	 *  'original_columns' => []
	 * ]`
	 */
	public static function get_table_columns( $plugin, $form_id ) {
		$fv_columns_obj = new FV_Columns(
			[
				'plugin'  => $plugin,
				'form_id' => $form_id,
			]
		);

		$all_cols = $fv_columns_obj->get_columns();
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $plugin != 'caldera' ) {
			$all_cols['columns'][]          = [
				'colKey'  => 'fv_status',
				'alias'   => 'Status',
				'visible' => true,
			];
			$all_cols['original_columns'][] = 'fv_status';
		}

		foreach ( $all_cols['columns'] as $key => $values ) {
			$all_cols['columns'][ $key ]['visible'] = true;
		}

		return $all_cols;
	}

	/**
	 * Convert array to sheets columns
	 *
	 * @access public
	 * @param array $headers The headers array - `['id', 'name']`
	 * @since 1.4.4
	 * @return array `[
	 *  'A' => 'id',
	 *  'B' => 'name',
	 *  ...
	 * ]`
	 */
	public static function headers_to_sheets_headers( $headers ) {
		if ( empty( $headers ) ) {
			return [];
		}

		$key_array = [];
		for ( $x = 'A';; $x++ ) {
			array_push( $key_array, $x );
			if ( $x === 'ZZ' ) {
				break;
			}
		}
		$count = count( $headers );
		return array_combine( array_slice( $key_array, 0, $count ), $headers );
	}
}
