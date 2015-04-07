<?php


/* Language: text domain
------------------------------------------*/

load_plugin_textdomain( 'bloom-french', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/* Hook to init
------------------------------------------*/
add_action( 'init', 'fxb_bloom_init' );

/**
 * Add the text domain to init.
 *
 * @since 1.0
 */
function fxb_bloom_init() {

	/* Language */
	// Set filter for language directory
	$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$lang_dir = apply_filters( 'fxb_bloom_languages_directory', $lang_dir );

	// Traditional WordPress plugin locale filter
	$locale = apply_filters( 'plugin_locale', get_locale(), 'bloom' );
	$mofile = sprintf( '%1$s-%2$s.mo', 'bloom', $locale );

	// Setup paths to current locale file
	$mofile_local   = $lang_dir . $mofile;
	$mofile_global  = WP_LANG_DIR . '/bloom-french/' . $mofile;

	if ( file_exists( $mofile_global ) ) {
		// Look in global /wp-content/languages/bloom-french/ folder
		load_textdomain( 'bloom', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) {
		// Look in local /wp-content/plugins/bloom-french/languages/ folder
		load_textdomain( 'bloom', $mofile_local );
	} else {
		// Load the default language files
		load_plugin_textdomain( 'bloom', false, $lang_dir );

	}
}
define( 'FXB_BLOOM_FRENCH_VER', '1.0.0' );

define( 'FXB_BLOOM_STORE_URL', 'http://fxbenard.com' );

define( 'FXB_BLOOM_ITEM_NAME', 'Divi2 French' );

if ( ! class_exists( 'FXB_BLOOM_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/includes/FXB_BLOOM_Plugin_Updater.php' );
}

function fxb_bloom_french_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'fxb_bloom_license_key' ) );

	// setup the updater
	$edd_updater = new FXB_BLOOM_Plugin_Updater( FXB_BLOOM_STORE_URL, __FILE__, array(
			'version' 	=> FXB_BLOOM_FRENCH_VER,
			'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
			'item_name' => FXB_BLOOM_ITEM_NAME,
			'author' 	=> 'FX Bénard',
		)
	);

}
add_action( 'admin_init', 'fxb_bloom_french_plugin_updater', 0 );

function fxb_bloom_license_menu() {
	add_plugins_page( __( 'Bloom French License', 'bloom-french' ), __( 'Bloom French License', 'bloom-french' ), 'manage_options', 'bloomfrench-license', 'fxb_bloom_license_page' );
}
add_action( 'admin_menu', 'fxb_bloom_license_menu' );

function fxb_bloom_license_page() {
	$license 	= get_option( 'fxb_bloom_license_key' );
	$status 	= get_option( 'fxb_bloom_license_status' );
	?>
	<div class="wrap">
		<h2><?php _e( 'Bloom French License Options', 'bloom-french' ); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields( 'fxb_bloom_license' ); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'License Key', 'bloom-french' ); ?>
						</th>
						<td>
							<input id="fxb_bloom_license_key" name="fxb_bloom_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="fxb_bloom_license_key"><?php _e( 'Enter your license key', 'bloom-french' ); ?></label>
						</td>
					</tr>
					<?php if ( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'Activate License', 'bloom-french' ); ?>
							</th>
							<td>
								<?php if ( false !== $status && 'valid' == $status ) { ?>
									<span style="color:green;"><?php _e( 'active', 'bloom-french' ); ?></span>
									<?php wp_nonce_field( 'fxb_bloom_nonce', 'fxb_bloom_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_license_deactivate" value="<?php _e( 'Deactivate License', 'bloom-french' ); ?>"/>
								<?php } else {
									wp_nonce_field( 'fxb_bloom_nonce', 'fxb_bloom_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_license_activate" value="<?php _e( 'Activate License', 'bloom-french' ); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
	<?php
}

function fxb_bloom_register_option() {
	// creates our settings in the options table
	register_setting( 'fxb_bloom_license', 'fxb_bloom_license_key', 'edd_sanitize_license' );
}
add_action( 'admin_init', 'fxb_bloom_register_option' );

function edd_sanitize_license( $new ) {
	$old = get_option( 'fxb_bloom_license_key' );
	if ( $old && $old != $new ) {
		delete_option( 'fxb_bloom_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

function fxb_bloom_activate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['edd_license_activate'] ) ) {

		// run a quick security check
	 	if ( ! check_admin_referer( 'fxb_bloom_nonce', 'fxb_bloom_nonce' ) ) {
			return; // get out if we didn't click the Activate button
	 	}

		// retrieve the license from the database
		$license = trim( get_option( 'fxb_bloom_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( FXB_BLOOM_ITEM_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, FXB_BLOOM_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'fxb_bloom_license_status', $license_data->license );

	}
}
add_action( 'admin_init', 'fxb_bloom_activate_license' );

function fxb_bloom_deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['edd_license_deactivate'] ) ) {

		// run a quick security check
	 	if ( ! check_admin_referer( 'fxb_bloom_nonce', 'fxb_bloom_nonce' ) ) {
			return; // get out if we didn't click the Activate button
	 	}

		// retrieve the license from the database
		$license = trim( get_option( 'fxb_bloom_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( FXB_BLOOM_ITEM_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, FXB_BLOOM_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( 'deactivated' == $license_data->license ) {
			delete_option( 'fxb_bloom_license_status' );
		}
	}
}
add_action( 'admin_init', 'fxb_bloom_deactivate_license' );