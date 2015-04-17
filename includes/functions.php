<?php
/**
 * Helper Functions
 *
 * @package     FxB\Sample Translations\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'fxb_license_menu' ) ) {
	function fxb_license_menu() {
		add_plugins_page( __( 'FxB Translations', 'fxb-sample-translations' ), __( 'FxB Translations', 'fxb-sample-translations' ), 'manage_options', 'fxb-trads-license', 'fxb_license_page' );
	}
	add_action( 'admin_menu', 'fxb_license_menu' );
}

if ( ! function_exists( 'fxb_license_page' ) ) {
	function fxb_license_page() {
		$license  = get_option( 'fxb_sample_license_key' );
		$status   = get_option( 'fxb_sample_license_status' );
		?>
		<div class="wrap">
			<h2><?php _e( 'FxB Translations License Options', 'fxb-sample-translations' ); ?></h2>
			<form method="post" action="options.php">

				<?php settings_fields( 'fxb_sample_license' ); ?>

				<table class="form-table">
					<tbody>
					<?php echo do_action('fxb_key_setting', $license, $status, $content_key = ''); ?>
					</tbody>
				</table>
				<?php submit_button(); ?>

			</form>
		<?php
	}
}

function fxb_sample_key($license, $status, $content_key) {
	$plugin = 'FxB Sample Translations';

	$content_key .= '<tr valign="top">
		<th scope="row" valign="top">'
			. __( 'License Key', 'fxb-sample-translations' ) . ' ' . $plugin .'
		</th>
		<td>
			<input id="fxb_sample_license_key" name="fxb_sample_license_key" type="text" class="regular-text" value="' . esc_attr__( $license ) . '" />
			<label class="description" for="fxb_sample_license_key">' . __( 'Enter your license key', 'fxb-sample-translations' ) . '</label>
		</td>
	</tr>';
	if ( false !== $license ) {
		$content_key .= '<tr valign="top">
		<th scope="row" valign="top">
			' . __( 'Activate License', 'fxb-sample-translations' ) . '
		</th>
		<td>';
		if ( false !== $status && "valid" == $status ) {
			$content_key .= '<span style="color:green;">' . __( 'active', 'fxb-sample-translations' ) . '</span>
				' . wp_nonce_field( "fxb_sample_nonce", "fxb_sample_nonce" ) . '
				<input type="submit" class="button-secondary" name="edd_license_deactivate" value="' . __( 'Deactivate License', 'fxb-sample-translations' ) .'"/>';
		} else {
			$content_key .= wp_nonce_field( "fxb_sample_nonce", "fxb_sample_nonce" ) .'
				<input type="submit" class="button-secondary" name="edd_license_activate" value="' . __( 'Activate License', 'fxb-sample-translations' ) .'"/>';
		}
		$content_key .= '</td>
		</tr>';
	}

	echo $content_key;
}
add_action( 'fxb_key_setting', 'fxb_sample_key', 10, 3 );

if ( ! function_exists( 'fxb_sample_register_option' ) ) {
	function fxb_sample_register_option() {
		// creates our settings in the options table
		register_setting( 'fxb_sample_license', 'fxb_sample_license_key', 'edd_sanitize_license' );
	}
	add_action( 'admin_init', 'fxb_sample_register_option' );
}

if ( ! function_exists( 'edd_sanitize_license' ) ) {
	function edd_sanitize_license( $new ) {
		$old = get_option( 'fxb_sample_license_key' );
		if ( $old && $old != $new ) {
			delete_option( 'fxb_sample_license_status' ); // new license has been entered, so must reactivate
		}
		return $new;
	}
}

if ( ! function_exists( 'fxb_sample_activate_license' ) ) {
	function fxb_sample_activate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['edd_license_activate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'fxb_sample_nonce', 'fxb_sample_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}

			// retrieve the license from the database
			$license = trim( get_option( 'fxb_sample_license_key' ) );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'   => $license,
				'item_name' => urlencode( FXB_SAMPLE_ITEM_NAME ), // the name of our product
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, FXB_SAMPLE_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'fxb_sample_license_status', $license_data->license );

		}
	}
	add_action( 'admin_init', 'fxb_sample_activate_license' );
}

if ( ! function_exists( 'fxb_sample_deactivate_license' ) ) {
	function fxb_sample_deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['edd_license_deactivate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'fxb_sample_nonce', 'fxb_sample_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}

			// retrieve the license from the database
			$license = trim( get_option( 'fxb_sample_license_key' ) );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'   => $license,
				'item_name' => urlencode( FXB_SAMPLE_ITEM_NAME ), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, FXB_SAMPLE_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( 'deactivated' == $license_data->license ) {
				delete_option( 'fxb_sample_license_status' );
			}
		}
	}
	add_action( 'admin_init', 'fxb_sample_deactivate_license' );
}

function fxb_sample_translations_admin_notices() {
	$license = get_option( 'fxb_sample_license_key' );
	$plugin = 'FxB Sample Translations';

	if ( $license ) {
		return false;
	}

	echo '<div class="error"><p>';
	 echo sprintf( __( 'Please enter your license key for %s. An active license key is needed for automatic plugin updates.', 'fxb-sample-translations' ), $plugin ) ;
	 echo '&nbsp;<a href="' . admin_url( 'plugins.php?page=fxb-trads-license' ) . '" class="button-secondary">' . __( 'Activate License', 'fxb-sample-translations' ) . '</a>';
	 echo '</p></div>';
}
add_action( 'admin_notices', 'fxb_sample_translations_admin_notices' );