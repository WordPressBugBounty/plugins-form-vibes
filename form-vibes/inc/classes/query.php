<?php

namespace FormVibes\Classes;

/**
 * A utility class for managing the plugin queries
 */

class FV_Query {

	private $plugin = '';
	/**
	 * Entry table name
	 * @var $entry_table_name
	 */
	private $entry_table_name = '';
	/**
	 * Entry meta table name
	 * @var $entry_meta_table_name
	 */
	private $entry_meta_table_name = '';
	/**
	 * Entry table alias
	 * @var $entry_table_alias
	 */
	private $entry_table_alias = 'entry';
	/**
	 * Submission date key
	 * @var $submission_date_key
	 */
	private $submission_date_key = 'captured';
	/**
	 * Submission sql join key
	 * @var $join_key
	 */
	private $join_key = 'data_id';
	/**
	 * The final result
	 * @var $result
	 */
	private $result = [
		'error'                  => false,
		'message'                => '',
		'data'                   => [],
		'total_submission_count' => '',
		'status'                 => '',
	];

	/**
	 * The constructor of the class.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return void
	 */
	public function __construct( $query = '' ) {
		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Get the result
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array @var $this->result
	 */
	public function get_result() {
		return $this->result;
	}

	/**
	 * prepare the @var $this->result
	 *
	 * @access private
	 * @param array $query The query parameters.
	 * @since 1.4.4
	 * @return void
	 */
	private function query( $query ) {
		global $wpdb;
		$default_query_arr           = $this->init();
		$query_arr                   = wp_parse_args( $query, $default_query_arr );
		$this->entry_table_name      = "{$wpdb->prefix}fv_enteries";
		$this->entry_meta_table_name = "{$wpdb->prefix}fv_entry_meta";
		$this->plugin                = $query_arr['plugin'];
		$meta_key_key                = 'meta_key';
		$meta_value_key              = 'meta_value';

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $this->plugin == 'caldera' ) {
			$this->entry_table_name      = "{$wpdb->prefix}cf_form_entries";
			$this->entry_meta_table_name = "{$wpdb->prefix}cf_form_entry_values";
			$this->join_key              = 'entry_id';
			$meta_key_key                = 'slug';
			$meta_value_key              = 'value';
		}

		if ( ! Utils::key_exists( 'form_id', $query_arr ) || ! Utils::key_exists( 'plugin', $query_arr ) || $this->plugin === '' || $query_arr['form_id'] === '' ) {
			$this->result['error']   = true;
			$this->result['message'] = 'Either form id or plugin missing!';
		} else {
			$entry_query_str             = $this->prepare_query( $query_arr );
			$total_entry_count_query_str = $this->prepare_total_entry_count_query( $query_arr );
			$entry_data                  = $wpdb->get_results( $entry_query_str, ARRAY_A );
			$entry_count                 = $wpdb->get_var( $total_entry_count_query_str );			
			if ( count( $entry_data ) > 0 ) {
				$data_ids = [];
				$entries  = [];
				foreach ( $entry_data as $entry ) {
					array_push( $data_ids, $entry['id'] );
					$entries[ $entry['id'] ] = $entry;
				}

				$entry_meta_query = $this->prepare_entry_meta_query( $data_ids );
				$entry_meta_data  = $wpdb->get_results( $entry_meta_query, ARRAY_A );
				foreach ( $entry_meta_data as $meta ) {
					
					$entries[ $meta[ $this->join_key ] ][ $meta[ $meta_key_key ] ] = $meta[ $meta_value_key ];

					if ( $meta[ $meta_key_key ] === 'fv-notes' ) {
						$new_notes_data = $this->update_notes_meta_data( $meta[ $meta_value_key ] );
						// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
						$entries[ $meta[ $this->join_key ] ]['fv-notes'] = json_encode( $new_notes_data );
					}
				}

				$this->result['error']                  = false;
				$this->result['message']                = '';
				$this->result['data']                   = array_values( $entries );
				$this->result['total_submission_count'] = (int) $entry_count;
				$this->result['status']                 = 200;

				if ( Utils::key_exists( 'data_return_type', $query_arr ) && $query_arr['data_return_type'] === 'with-column-keys' ) {
					$this->result['data'] = $entries;
				}
			}
		}
	}

	/**
	 * Prepare notes for frontend display
	 *
	 * @access private
	 * @param string $notes The notes json data.
	 * @since 1.4.4
	 * @return array
	 */
	private function update_notes_meta_data( $notes ) {
		$notes = json_decode( $notes );

		foreach ( $notes as $key => $note ) {
			if ( ! get_userdata( $note->author_id ) ) {
				return $notes;
			}
			$user_data       = get_userdata( $note->author_id )->data;
			$username        = $user_data->user_login;
			$current_user_id = get_current_user_id();
			$is_me           = true;
			if ( $current_user_id !== (int) $note->author_id ) {
				$is_me = false;
			}
			$notes[ $key ]->author_name = $username;
			$notes[ $key ]->is_me       = $is_me;
		}

		return $notes;
	}

	/**
	 * Prepare total entry count query
	 *
	 * @access private
	 * @param array $query_arr The query.
	 * @since 1.4.4
	 * @return string
	 */
	private function prepare_total_entry_count_query( $query_arr ) {
		$sql = "SELECT COUNT(distinct(e1.{$this->join_key})) FROM {$this->entry_table_name} as entry ";
		$sql .= $this->prepare_joins( $query_arr );
		$sql .= $this->prepare_where( $query_arr );	
		return $sql;
	}

	/**
	 * Prepare entry meta query
	 *
	 * @access private
	 * @param array $data_ids The data ids.
	 * @since 1.4.42
	 * @return string
	 */
	private function prepare_entry_meta_query( $data_ids ) {
		global $wpdb;
		$fields = [
			'meta_key',
			'meta_value',
			'data_id',
		];
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $this->plugin == 'caldera' ) {
			$fields = [
				'slug',
				'value',
				$this->join_key,
			];
		}

		$fields = implode( ',', $fields );
		$q    =  "SELECT {$fields} FROM {$this->entry_meta_table_name} where {$this->join_key} IN (" . implode( ',', $data_ids ) . ')' ;
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $this->plugin != 'caldera' ) {
			$q .= $wpdb->prepare( " AND meta_key NOT IN ('%s', '%s') ", 'fv_form_id', 'fv_plugin' );
		}

		return $q;
	}


	private function fv_get_limit_string($query_arr) {
		global $wpdb;
	
		$limit = 20; // Default limit
		$offset = 0; // Default offset
	
		// Check if 'limit' exists and is not empty, then sanitize and validate it
		if (Utils::key_exists('limit', $query_arr) && $query_arr['limit'] !== '') {
			$limit = absint($query_arr['limit']); // Ensure limit is an absolute integer
			$current_page = isset($query_arr['current_page']) ? absint($query_arr['current_page']) : 1; // Ensure current_page is an absolute integer
	
			if ($current_page > 1) {
				// Calculate the offset
				$offset = ($current_page - 1) * $limit;
			}
		}
	
		// Use prepared statements to safely insert the limit and offset values
		if ($offset > 0) {
			$limit_query = $wpdb->prepare(" LIMIT %d, %d", $offset, $limit);
		} else {
			$limit_query = $wpdb->prepare(" LIMIT %d", $limit);
		}
	
		return $limit_query;
	}
	

	private function fv_get_order_by_string($query_arr) {
		global $wpdb;
	
		$order_by = 'desc';
	
		if (Utils::key_exists('order_by', $query_arr) && !empty($query_arr['order_by'])) {
			// Sanitize and validate order by value
			$order_by_value = sanitize_text_field($query_arr['order_by']);
			if (in_array(strtolower($order_by_value), ['asc', 'desc'], true)) {
				$order_by = $order_by_value;
			}
		}
	
		// Use the validated order_by value
		$entry_query_arr = $wpdb->prepare(" ORDER BY {$this->entry_table_alias}.{$this->submission_date_key} %1s", $order_by);
		// echo $entry_query_arr;
		// die('dfadf');
		return $entry_query_arr;
	}
	/**
	 * Prepare final query
	 *
	 * @access private
	 * @param array $query_arr The query array.
	 * @since 1.4.4
	 * 
	 * @return string
	 */
	// update query for new structure prepare_querys after 1.14.10
	

	private function get_query_cols($cols){
		return implode( ', ' . $this->entry_table_alias . '.', $cols );
	}

	public function prepare_query($query_arr){
		global $wpdb;
		$cols = $this->get_entry_table_query_cols( $query_arr );
		if ( $this->plugin == 'caldera' ) {
			$cols                      = [ 'datestamp', 'id', 'form_id', 'user_id', 'status' ];
			$this->submission_date_key = 'datestamp';
		}
		$cols[0] = $this->entry_table_alias . '.' . $cols[0];
		$prpare_statement_query = $wpdb->prepare("SELECT DISTINCT %1s FROM %2s AS %3s ", 
			$this->get_query_cols($cols),
			$this->entry_table_name,
			$this->entry_table_alias
		);
		$prpare_statement_query .= $this->prepare_joins($query_arr); 
		$prpare_statement_query .= $this->prepare_where($query_arr);
		$prpare_statement_query .= $this->fv_get_order_by_string($query_arr);
		$prpare_statement_query .= $this->fv_get_limit_string($query_arr);
		return $prpare_statement_query;
	}

	/**
	 * Prepare entry table query
	 *
	 * @access private
	 * @param array $query_arr The query array.
	 * @since 1.4.4
	 * @return string
	 */

	private function prepare_entry_query( $query_arr ){
		global $wpdb;

		$entry_query_arr = $query_arr['entry_query'];
		$relation = sanitize_text_field($entry_query_arr['relation']);
		unset($entry_query_arr['relation']);

		$vars = [];
		foreach ($entry_query_arr as $values) {
			$column = sanitize_text_field($values['column']);
			$compare = sanitize_text_field($values['compare']);
			$value = trim($values['value']);
			
			// Prepare each condition and add to the $vars array
			if (in_array($compare, ['=', '!=', 'LIKE', 'NOT LIKE'])) {
				$vars[] = $wpdb->prepare(" {$this->entry_table_alias}.{$column} {$compare} %s", $value);
			}
		}

		return implode(" {$relation} ", $vars);
	}

	/**
	 * Prepare entry meta table query
	 *
	 * @access private
	 * @param array $query_arr The query array.
	 * @since 1.4.4
	 * @return string
	 */
	public function prepare_meta_query( $query_arr){
		global $wpdb;
		$entry_fields = Utils::get_entry_table_fields();
		$meta_query_arr = (array) $query_arr['meta_query'];
		$relation       = sanitize_text_field($meta_query_arr['relation']);
		unset( $meta_query_arr['relation'] );
		$vars = [];
		if ( count( $meta_query_arr ) <= 0 ) {
			return '';
		}
		$meta_key_key   = 'meta_key';
		$meta_value_key = 'meta_value';
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $this->plugin == 'caldera' ) {
			$meta_key_key   = 'slug';
			$meta_value_key = 'value';
		}
		foreach ( $meta_query_arr as $key => $values ) {
			$values      = (array) $values;
			$table_alias = 'e';
			$meta_key    = sanitize_text_field($values['meta_key']);
			$meta_value  = trim( $values['meta_value'] );
			$compare     = sanitize_text_field($values['compare']);
			//echo '<pre>';  print_r($values); echo '</pre>';
			if ( 'OR' == $relation ) {
				$key = 0;
			}
			if ( in_array( $values['meta_key'], $entry_fields, true ) ) {
				$table_alias = 'entry';
			}

			$k = $key + 1;

			if ($compare === 'LIKE' || $compare === 'NOT LIKE') {
				$meta_value = '%' . $wpdb->esc_like($meta_value) . '%';
			}

			if ($table_alias === 'e') {
				if (empty($meta_value)) {
					$vars[] = $wpdb->prepare(
						" ( e{$k}.{$meta_key_key} = %s AND e{$k}.{$meta_value_key} LIKE '%%' OR e{$k}.{$meta_key_key} = '' )",
						$meta_key
					);
				} else {
					$vars[] = $wpdb->prepare(
						" ( e{$k}.{$meta_key_key} = %s AND e{$k}.{$meta_value_key} {$compare} %s )",
						$meta_key, $meta_value
					);
				}
			} else {
				if (empty($meta_value)) {
					$vars[] = " ( entry.{$meta_key} IS NULL OR entry.{$meta_key} = '' )";
				} else {
					$vars[] = $wpdb->prepare(
						" ( entry.{$meta_key} {$compare} %s )",
						$meta_value
					);
				}
			}
		}
		return implode(" {$relation} ", $vars);
	}

	
	

	/**
	 * Add appropriate operator to meta value
	 *
	 * @access private
	 * @param array $operator The operator.
	 * @param array $value The value.
	 * @since 1.4.4
	 * @return string
	 */
	private function check_like_operator( $operator, $value ) {
		if ( $operator === 'LIKE' || $operator === 'NOT LIKE' ) {
			return "'%{$value}%'";
		}
		return "'{$value}'";
	}

	/**
	 * Prepare joins for table query
	 *
	 * @access private
	 * @param array $query_arr The query array.
	 * @since 1.4.4
	 * @return string
	 */

	 private function prepare_joins($query_arr) {
		global $wpdb;
	
		// Initial join condition
		$joins[] = "INNER JOIN {$this->entry_meta_table_name} as e1 ON ({$this->entry_table_alias}.id = e1.{$this->join_key})";
	
		$query_arr['meta_query'] = (array) $query_arr['meta_query'];
	
		if (Utils::key_exists('meta_query', $query_arr) && $query_arr['meta_query'] !== '' && count($query_arr['meta_query']) > 0) {
			$meta_query_arr = (array) $query_arr['meta_query'];
			$condition = 'data_id';
			$relation = sanitize_text_field($meta_query_arr['relation']);
			unset($meta_query_arr['relation']);
	
			if ($relation !== 'OR' && count($meta_query_arr) > 0) {
				$joins = []; // Reset joins if relation is not OR
				foreach ($meta_query_arr as $key => $values) {
					$key = absint($key + 1); // Sanitize key to be used in table alias
					$joins[] = $wpdb->prepare("INNER JOIN {$this->entry_meta_table_name} as e$key ON (entry.id = e$key.%1s)", $condition);
				}
			}
		}
		
		$joins = apply_filters('fv_entry_query_joins', $joins);
		return implode(' ', $joins);
	}
	

	

	/**
	 * Get entry table columns
	 *
	 * @access private
	 * @since 1.4.4
	 * @return array
	 */
	private function get_entry_table_query_cols() {
		$cols = [
			'id',
			'fv_status',
			'url',
			'form_id',
			'form_plugin',
			'captured',
			'user_agent',
		];

		return apply_filters( 'fv_entry_table_query_cols', $cols );
	}

	/**
	 * Prepare the query where
	 *
	 * @access private
	 * @param array $query_arr The query array.
	 * @since 1.4.4
	 * @return array
	 */
	private function prepare_where($query_arr) {		
		global $wpdb;
	
		// Ensure default empty values for sanitized inputs
		$plugin = '';
		$form_id = 0;
	
		// Sanitize inputs
		if (isset($query_arr['plugin'])) {
			if (is_array($query_arr['plugin'])) {
				$plugin = '';
			} else {
				$plugin = sanitize_text_field($query_arr['plugin']);
			}
		}
	
		if (isset($query_arr['form_id'])) {
			if (is_array($query_arr['form_id'])) {
				$form_id = 0;
			} else {
				$form_id = sanitize_text_field($query_arr['form_id']);
			}
		}
	
		$where = [];
		
		if ($this->plugin == 'caldera') {
			$where[] = 'WHERE 1=1';
		} else {
			$where[] = $wpdb->prepare(
				" WHERE {$this->entry_table_alias}.form_plugin = %s AND {$this->entry_table_alias}.form_id = %s ",
				$plugin, $form_id
			);
		}
	
		if (isset($query_arr['fv_export_selected_rows']) && is_array($query_arr['fv_export_selected_rows']) && count($query_arr['fv_export_selected_rows']) > 0) {
			$fv_export_selected_rows = array_map('absint', $query_arr['fv_export_selected_rows']);
			$where[] = "{$this->entry_table_alias}.id IN (" . implode(',', $fv_export_selected_rows) . ')';
		}
	
		// entry query query
		if (isset($query_arr['entry_query']) && is_array($query_arr['entry_query']) && count($query_arr['entry_query']) > 0) {
			
			$where[] = ' ( ' . $this->prepare_entry_query($query_arr) . ' ) ';
		}
	
		$query_arr['meta_query'] = (array)$query_arr['meta_query'];
	
		// meta query
		if (isset($query_arr['meta_query']) && $query_arr['meta_query'] !== '' && count($query_arr['meta_query']) > 0) {
			$meta_query = $this->prepare_meta_query($query_arr);
			if ($meta_query !== '') {
				$where[] = ' ( ' . $meta_query . ' ) ';
			}
		}
	
		$fv_status_arr  = Utils::get_fv_status();
		$allowed_status      = [];
		foreach ( $fv_status_arr as $value ) {
				$allowed_status[] = $value['key'];
		}
		$allowed_status[] = 'undefined';	

		if (isset($query_arr['status']) && is_array($query_arr['status']) && count($query_arr['status']) > 0) {
			
			if (in_array('unread', $query_arr['status'])) {
				$query_arr['status'][] = 'undefined';
			}		
			$status_sanitized = array_intersect($query_arr['status'],$allowed_status);		
			$where[] = " fv_status IN ('" . implode("', '", $status_sanitized) . "') ";
		}
	
		$query_type = 'This_Year';
		$from_date = '';
		$to_date = '';
	
		if (isset($query_arr['query_type'])) {
			$query_type = sanitize_text_field($query_arr['query_type']);
		} else {
			$query_type = '';
		}
	
		if (isset($query_arr['from_date']) && isset($query_arr['to_date']) && $query_arr['from_date'] !== '' && $query_arr['to_date'] !== '') {
			$from_date = sanitize_text_field($query_arr['from_date']);
			$to_date = sanitize_text_field($query_arr['to_date']);
		}
	
		if ($query_type !== '' && $query_type !== 'Custom') {
			$dates = CarbonUtils::get_preset_date_range($query_type);
			$from_date = $dates['from_date']->format('Y-m-d');
			$to_date = $dates['to_date']->format('Y-m-d');
		}
	
		// date
		$where[] = $wpdb->prepare(" DATE_FORMAT({$this->entry_table_alias}.{$this->submission_date_key}, GET_FORMAT(DATE, 'JIS')) >= %s ", $from_date);
		$where[] = $wpdb->prepare(" DATE_FORMAT({$this->entry_table_alias}.{$this->submission_date_key}, GET_FORMAT(DATE, 'JIS')) <= %s ", $to_date);
		
		$where = apply_filters('fv_entry_query_where', $where);
		return implode(' AND ', $where);

	}
	

	/**
	 * Get default query parameters
	 *
	 * @access private
	 * @since 1.4.4
	 * @return array
	 */
	private function init() {
		$default_query_arr = [
			'limit'            => 20,
			'plugin'           => '',
			'form_id'          => '',
			'from_date'        => '',
			'to_date'          => '',
			'order_by'         => 'desc',
			'status'           => [],
			'entry_query'      => [],
			'meta_query'       => [],
			'current_page'     => 1,
			'meta_key_exclude' => [],
			'data_return_type' => 'array',
		];
		return apply_filters( 'fv_default_query_arr', $default_query_arr );
	}
}
