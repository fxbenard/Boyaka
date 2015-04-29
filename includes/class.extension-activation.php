<?php
/**
 * Activation handler
 *
 * @package     EDD\ActivationHandler
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDD Extension Activation Handler Class
 *
 * @since       1.0.0
 */
class EDD_Extension_Activation {

	public $plugin_name, $plugin_path, $plugin_file, $fxb_base, $has_fxb_sample_plugin;

	/**
	 * Setup the activation class
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function __construct( $plugin_path, $plugin_file ) {
		// We need plugin.php!
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugins = get_plugins();

		// Set plugin directory
		$plugin_path = array_filter( explode( '/', $plugin_path ) );
		$this->plugin_path = end( $plugin_path );

		// Set plugin file
		$this->plugin_file = $plugin_file;

		// Set plugin name
		if ( isset( $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] ) ) {
			$this->plugin_name = str_replace( 'FxB Sample Translations -', '', $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] );
		} else {
			$this->plugin_name = __( 'This plugin', 'fxb-sample-translations' );
		}

		// Is FxB Sample installed?
		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( $plugin['Name'] == 'FxB Sample' ) {
				$this->has_fxb_sample_plugin = true;
				$this->fxb_base = $plugin_path;
				break;
			}
		}
	}


	/**
	 * Process plugin deactivation
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function run() {
		// Display notice
		add_action( 'admin_notices', array( $this, 'missing_edd_notice' ) );
	}


	/**
	 * Display notice if EDD isn't installed
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      string The notice to display
	 */
	public function missing_edd_notice() {
		if ( $this->has_fxb_sample_plugin ) {
			$url  = esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $this->fxb_base ), 'activate-plugin_' . $this->fxb_base ) );
			$link = '<a href="' . $url . '">' . __( 'activate it', 'fxb-sample-translations' ) . '</a>';
		} else {
			$url  = esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=fxb-sample' ), 'install-plugin_easy-digital-downloads' ) );
			$link = '<a href="' . $url . '">' . __( 'install it', 'fxb-sample-translations' ) . '</a>';
		}

		 '<div class="error"><p>' . $this->plugin_name . sprintf( __( ' requires FxB Sample! Please %s to continue!', 'fxb-sample-translations' ), $link ) . '</p></div>';
	}
}
