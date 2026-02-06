<?php
// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
namespace FormVibes\Classes;

use FormVibes\Pro\Classes\Helper;
use FormVibes\Classes\Utils;

/**
 * A utility class for managing the export of the form data.
 */

class Export {

	/**
	 * The constructor of the class.
	 *
	 * @access public
	 * @param array $params The parameters for export the data.
	 * @since 1.4.4
	 * @return void
	 *
	 */
	public function __construct( ) {
		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'form-vibes';
		if ( ! file_exists( $uploads_dir ) ) {
			wp_mkdir_p( $uploads_dir );
		}
		add_action( 'init', [ $this, 'fv_export_csv' ] );
	}

	/**
	 * Call when user clicks on export button from quick export
	 *
	 * @access public
	 * @return void
	 */
	public function fv_export_csv() {
		if (isset($_POST['btnExport'])) {
			// Check if the current user has the capability to export
			if ( !Permissions::check_permission(Permissions::$CAP_EXPORT) ) {
				die('Sorry, you are not allowed to do this action!!!!!!');
			}
			
			// Verify the nonce
			if (!wp_verify_nonce($_POST['fv_nonce'], 'fv_ajax_nonce')) {
				die('Sorry, your nonce did not verify!');
			}

			$params = (array) json_decode(stripslashes($_REQUEST['fv_export_data']));
			// echo '<pre>';  print_r($params); echo '</pre>';
			// die('dfadf');
			// new Export($params);
			$this->export_file($params);
		}
	}

	/**
	 * Instantiates the export and prepare the data for export.
	 *
	 * @access private
	 * @param array $params The parameters for export the data.
	 * @since 1.4.4
	 * @return void
	 */
	private function export_file( $params ) {
		if (!Permissions::check_permission(Permissions::$CAP_EXPORT)) {
			die('Sorry, you are not allowed to do this action!!!!!!');
		}

		$fv_settings = get_option( 'fvSettings' );

		// echo '<pre>';  print_r($params); echo '</pre>';
		// die('dfadf');
		if ( $fv_settings && Utils::key_exists( 'csv_export_reason', $fv_settings ) && $fv_settings['csv_export_reason'] ) {
			Utils::set_export_reason( $params['description'] );
		}

		$plugin                  = lcfirst( $params['plugin'] );
		$form_id                 = $params['form_id'];
		$name                    = $plugin . '-' . $form_id . '-' . date( 'Y/m/d' );
		$name                    = apply_filters( 'formvibes/quickexport/filename', $name, $params );
		$download_type           = $params['download_type'];
		//$fv_export_selected_rows = $params['fv_export_selected_rows'];

		$params['data_return_type'] = [
			'with-column-keys',
		];

		$fv_query      = new FV_Query( $params );
		$res           = $fv_query->get_result();
		$data          = $res['data'];
		$columns_obj   = new FV_Columns( $params );
		$cols          = $columns_obj->get_columns()['columns'];
		$fv_status_arr = Utils::get_fv_status();
		$fv_status     = [];
		foreach ( $fv_status_arr as $value ) {
			$fv_status[ $value['key'] ] = $value['value'];
		}

		$columns = [];
		if ( Utils::is_pro() ) {
			foreach ( $cols as $col ) {
				if ( $col['visible'] === true ) {
					$columns[] = $col['alias'];
				}
			}
		} else {
			foreach ( $cols as $key => $col ) {
				$columns[]               = $col['alias'];
				$cols[ $key ]['visible'] = true;
			}
		}

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $params['is_pro'] == 1 && $plugin != 'caldera' ) {
			$columns[] = 'Status';
		}

		if ( $download_type === 'csv' ) {
			$this->create_csv( $name, $columns, $cols, $params, $fv_status, $data );
		}
	}



	/**
	 * Creates the csv file
	 *
	 * @param string $name The name of the file
	 * @param array $columns The columns to be included in the header
	 * @param array $cols The columns to be included in the file
	 * @param array $params The parameters
	 * @param array $fv_status The status of the entry
	 * @param array $data The data to be included in the file
	 * @return void
	 */
	private function create_csv( $name, $columns, $cols, $params, $fv_status, $data ) {
		/* Settings file headers */
		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false );
		header( 'Content-Type: text/csv;charset=utf-8' );
		header( 'Content-Disposition: attachment;filename=' . $name . '.csv' );

		$fp = fopen( 'php://output', 'w' );

		$csv_params = [
			'delimiter' => ',',
			'enclosure' => '"',
			'escape'    => '\\',
			'eol'       => PHP_EOL,
		];

		$csv_params = apply_filters( 'formvibes/export/csv_params', $csv_params );

		if ( isset( $data ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
			fwrite( $fp, "\xEF\xBB\xBF" );
			fputcsv(
				$fp,
				array_values( $columns ),
				$csv_params['delimiter'],
				$csv_params['enclosure'],
				$csv_params['escape']
			);
			foreach ( $data as $values ) {
				$temp = [];
				foreach ( $cols as $col ) {
					if ( $col['visible'] ) {
						if ( Utils::key_exists( $col['colKey'], $values ) ) {
							$temp[ $col['colKey'] ] = stripslashes( $values[ $col['colKey'] ] );
						} else {
							$temp[ $col['colKey'] ] = '';
						}
					}
				}
				// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				if ( Utils::key_exists( 'fv_status', $values ) && $params['is_pro'] == 1 ) {
					$status_key = $values['fv_status'];
					if ( Utils::key_exists( $status_key, $fv_status ) ) {
						$temp['fv_status'] = $fv_status[ $status_key ];
					} else {
						$temp['fv_status'] = 'Unread';
					}
				}

				fputcsv(
					$fp,
					$temp,
					$csv_params['delimiter'],
					$csv_params['enclosure'],
					$csv_params['escape']
				);
			}
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $fp );

		$exported_data = ob_get_contents();
		die();
	}
}
