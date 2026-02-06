<?php

namespace FormVibes\Classes;

/**
 * A utility class for Managing the table columns.
 */
class FV_Columns {

	/**
	 *
	 * @access private
	 * @var array
	 */
	private $columns = [
		'columns'          => [],
		'original_columns' => [],
	];

	/**
	 * The constructor of the class.
	 *
	 * @access public
	 * @param array $params The parameters for getting the columns.
	 * @since 1.4.4
	 * @return void
	 */
	public function __construct( $params = '' ) {
		if ( ! empty( $params ) ) {
			$this->columns( $params );
		}
	}

	/**
	 * Sets the @var $columns.
	 *
	 * Takes the required args and fetch the columns from the database.
	 *
	 * @access private
	 * @param array $params {
	 *      @type string $plugin The plugin name.
	 *      @type string $form_id The form id.
	 * }
	 * @since 1.4.4
	 * @return array [
	 *                  'columns'          => [],
	 *                  'original_columns' => []
	 * ]
	 */
	private function columns( $params ) {
		if ( is_array( $params['plugin'] ) ) {
			$params['plugin'] = '';
		}

		if ( is_array( $params['form_id'] ) ) {
			$params['form_id'] = '';
		}

		global $wpdb;
		$distinct_cols_query = "select distinct BINARY(meta_key) from {$wpdb->prefix}fv_entry_meta em join {$wpdb->prefix}fv_enteries e on em.data_id=e.id where form_id='" . $params['form_id'] . "' AND meta_key != 'fv_form_id' AND meta_key != 'fv_plugin'";
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $params['plugin'] == 'caldera' ) {
			$distinct_cols_query = "select distinct BINARY(slug) from {$wpdb->prefix}cf_form_entry_values em join {$wpdb->prefix}cf_form_entries e on em.entry_id=e.id AND e.form_id ='" . $params['form_id'] . "'";
		}
		$columns = $wpdb->get_col( $distinct_cols_query );
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $params['plugin'] != 'caldera' ) {
			array_push( $columns, 'captured' );
			array_push( $columns, 'url' );
			array_push( $columns, 'user_agent' );
		} else {
			array_push( $columns, 'datestamp' );
		}
		array_unshift( $columns, 'id' );
		$original_columns = $columns;
		$columns          = Utils::prepare_table_columns( $columns, $params['plugin'], $params['form_id'], false );

		$this->columns['columns']          = $columns;
		$this->columns['original_columns'] = $original_columns;
	}

	/**
	 * Gets the @var $this->columns.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array @var $this->columns
	 */
	public function get_columns() {
		return $this->columns;
	}
}
