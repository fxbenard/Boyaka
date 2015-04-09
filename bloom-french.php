<?php
/**
 * Plugin Name: Bloom French
 * Plugin URI: http://fxbenard.com/
 * Description: French Language Translations for Bloom.
 * Version: 1.0.0
 * Author: fxbenard
 * Author URI: http://fxbenard.com/
 * Text Domain: bloom-french
 * Domain Path: languages
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright Copyright (c) 2015, fxbenard
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FXB_BLOOM_Plugin' ) ) {

	/**
	 * Main FXB_BLOOM_Plugin class
	 *
	 * @since       1.0.0
	 */
	class FXB_BLOOM_Plugin {

		/**
		 *
		 *
		 * @var         FXB_BLOOM_Plugin $instance The one true FXB_BLOOM_Plugin
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true FXB_BLOOM_Plugin
		 */
		public static function instance() {
			if ( !self::$instance ) {
				self::$instance = new FXB_BLOOM_Plugin();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();

				// retrieve our license key from the DB
				$license_key = trim( get_option( 'fxb_bloom_license_key' ) );

				// setup the updater
				$edd_updater = new FXB_BLOOM_Plugin_Updater( FXB_BLOOM_STORE_URL, __FILE__, array(
						'version' 	=> FXB_BLOOM_FRENCH_VER,
						'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
						'item_name' => FXB_BLOOM_ITEM_NAME,
						'author' 	=> 'fxbenard',
					)
				);
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'FXB_BLOOM_FRENCH_VER', '1.0.0' );

			// Plugin path
			define( 'FXB_BLOOM_FRENCH_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'FXB_BLOOM_FRENCH_URL', plugin_dir_url( __FILE__ ) );

			define( 'FXB_BLOOM_STORE_URL', 'https://fxbenard.com' );

			define( 'FXB_BLOOM_ITEM_NAME', 'Boyaka' );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			// Include scripts
			//require_once FXB_BLOOM_FRENCH_DIR . 'includes/scripts.php';
			require_once FXB_BLOOM_FRENCH_DIR . 'includes/functions.php';

			/**
			 *
			 *
			 * @todo        The following files are not included in the boilerplate, but
			 *              the referenced locations are listed for the purpose of ensuring
			 *              path standardization in EDD extensions. Uncomment any that are
			 *              relevant to your extension, and remove the rest.
			 */
			// require_once FXB_BLOOM_FRENCH_DIR . 'includes/shortcodes.php';
			// require_once FXB_BLOOM_FRENCH_DIR . 'includes/widgets.php';
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 *
		 * @todo        The hooks listed in this section are a guideline, and
		 *              may or may not be relevant to your particular extension.
		 *              Please remove any unnecessary lines, and refer to the
		 *              WordPress codex and EDD documentation for additional
		 *              information on the included hooks.
		 *
		 *              This method should be used to add any filters or actions
		 *              that are necessary to the core of your extension only.
		 *              Hooks that are relevant to meta boxes, widgets and
		 *              the like can be placed in their respective files.
		 *
		 *              IMPORTANT! If you are releasing your extension as a
		 *              commercial extension in the EDD store, DO NOT remove
		 *              the license check!
		 */
		private function hooks() {
			// Handle licensing
			if ( ! class_exists( 'FXB_BLOOM_Plugin_Updater' ) ) {
				// load our custom updater
				include dirname( __FILE__ ) . '/includes/FXB_BLOOM_Plugin_Updater.php';
			}
		}

		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {

			// Set filter for language directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'fxb_bloom_french_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'bloom-french' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'bloom-french', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/bloom-french/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/bloom-french/ folder
				load_textdomain( 'bloom-french', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/bloom-french/languages/ folder
				load_textdomain( 'bloom-french', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'bloom-french', false, $lang_dir );
			}
		}

		public function settings( $settings ) {
			$new_settings = array(
				array(
					'id'    => 'fxb_bloom_plugin_settings',
					'name'  => '<strong>' . __( 'FXB BLOOM Settings', 'bloom-french' ) . '</strong>',
					'desc'  => __( 'Configure FXB BLOOM Settings', 'bloom-french' ),
					'type'  => 'header',
				),
			);

			return array_merge( $settings, $new_settings );
		}
	}
} // End if class_exists check


/**
 * The main function responsible for returning the one true FXB_BLOOM_Plugin
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \FXB_BLOOM_Plugin The one true FXB_BLOOM_Plugin
 *
 * @todo        Inclusion of the activation code below isn't mandatory, but
 *              can prevent any number of errors, including fatal errors, in
 *              situations where your extension is activated but EDD is not
 *              present.
 */
function FXB_BLOOM_Plugin_load() {
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
			require_once 'includes/class.extension-activation.php';
		}

		$activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();
		return FXB_BLOOM_Plugin::instance();
	} else {
		return FXB_BLOOM_Plugin::instance();
	}
}
add_action( 'plugins_loaded', 'FXB_BLOOM_Plugin_load' );

/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class, since we are preferring the plugins_loaded
 * hook for compatibility, we also can't reference a function inside the plugin class
 * for the activation function. If you need an activation function, put it here.
 *
 * @since       1.0.0
 * @return      void
 */
function FXB_BLOOM_Plugin_activation() {
	/* Activation functions here */

}
register_activation_hook( __FILE__, 'FXB_BLOOM_Plugin_activation' );
